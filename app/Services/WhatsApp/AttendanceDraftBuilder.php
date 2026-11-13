<?php

namespace App\Services\WhatsApp;

use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\WhatsAppAttendanceDraft;
use App\Models\WhatsAppMessage;
use Illuminate\Support\Facades\DB;

/**
 * Turns a received message into review-screen rows.
 *
 * This is the half of the job that needs the database: the parser has already
 * decided that a line says "JZ 9787, Adnan khan, present", and this works out
 * which driver that is.
 *
 * The name decides, not the vehicle. Checked against the real registers, the
 * man the supervisor names is frequently not the man `drivers.vehicle_id` has
 * on that vehicle - the assignments in FMS have fallen behind what happens on
 * the ground. Resolving by vehicle would quietly mark the wrong driver present
 * on a good number of lines every day, so the name leads and the vehicle is
 * the fallback. Wherever the two disagree, both are put on the row and a
 * person decides.
 *
 * Nothing here writes attendance.
 */
class AttendanceDraftBuilder
{
    public function __construct(
        private AttendanceMessageParser $parser,
        private DriverNameMatcher $matcher
    ) {
    }

    /**
     * Read one message and queue its lines for review.
     *
     * @return int how many drafts were created
     */
    public function build(WhatsAppMessage $message): int
    {
        if ($message->whatsapp_sender_id === null && !config('whatsapp.accept_unknown_senders')) {
            $message->markState(
                WhatsAppMessage::STATE_IGNORED,
                'Number ' . $message->from_number . ' is not a registered supervisor.'
            );

            return 0;
        }

        $parsed = $this->parser->parse((string) $message->body, $message->received_at);

        if (!$parsed['is_attendance']) {
            $message->markState(
                WhatsAppMessage::STATE_IGNORED,
                'Not an attendance register - too few vehicle numbers to be one.'
            );

            return 0;
        }

        // Drafts from an earlier delivery of this same message are replaced,
        // so a re-run cannot leave two copies of every line behind. Anything
        // already confirmed is left alone - that is real attendance now.
        $message->drafts()->where('state', WhatsAppAttendanceDraft::STATE_PENDING)->delete();

        $vehiclesByNumber = $this->vehiclesByNumber();
        $driversByVehicle = $this->driversByVehicle();
        $activeDrivers = Driver::select('id', 'full_name', 'vehicle_id', 'station_id')
            ->where('is_active', 1)
            ->get();

        $rows = [];

        foreach ($parsed['lines'] as $line) {
            $rows[] = $this->buildRow(
                $message,
                $parsed,
                $line,
                $vehiclesByNumber,
                $driversByVehicle,
                $activeDrivers
            );
        }

        if ($rows !== []) {
            DB::table('whatsapp_attendance_drafts')->insert($rows);
        }

        $message->markState(
            WhatsAppMessage::STATE_PARSED,
            $parsed['notes'] === [] ? null : 'Ignored text: ' . implode(' | ', $parsed['notes'])
        );

        return count($rows);
    }

    /**
     * One row of the review queue.
     *
     * @param  array<string, mixed>  $parsed
     * @param  array<string, mixed>  $line
     * @param  array<string, array<int, int>>  $vehiclesByNumber
     * @param  array<int, array<int, Driver>>  $driversByVehicle
     * @param  \Illuminate\Support\Collection<int, Driver>  $activeDrivers
     * @return array<string, mixed>
     */
    private function buildRow(
        WhatsAppMessage $message,
        array $parsed,
        array $line,
        array $vehiclesByNumber,
        array $driversByVehicle,
        $activeDrivers
    ): array {
        $problems = [];

        if ($parsed['date_error'] !== null) {
            $problems[] = $parsed['date_error'];
        }

        $vehicle = $this->resolveVehicle($line['vehicle_token'], $vehiclesByNumber, $problems);
        $replacementVehicle = $line['second_vehicle_token'] === null
            ? null
            : $this->resolveVehicle($line['second_vehicle_token'], $vehiclesByNumber, $problems);

        $people = $line['people'];
        $primary = $people[0] ?? ['name' => '', 'status' => null, 'status_token' => null];
        $stand_in = $people[1] ?? null;

        $isVehicleRegister = $parsed['kind'] === AttendanceMessageParser::KIND_VEHICLE;

        $driverId = null;
        $matchedBy = null;
        $score = null;
        $fmsDriverId = null;

        if (!$isVehicleRegister) {
            $resolved = $this->resolveDriver(
                $primary['name'],
                $vehicle,
                $driversByVehicle,
                $activeDrivers,
                $problems
            );

            $driverId = $resolved['driver_id'];
            $matchedBy = $resolved['matched_by'];
            $score = $resolved['score'];
            $fmsDriverId = $resolved['fms_driver_id'];
        }

        // No marker at all is the normal case in one of the registers: the
        // whole message is a present list. That is an assumption, not a fact,
        // so it is recorded as one and shown on the row.
        $status = $primary['status'];
        $statusAssumed = false;

        if ($status === null && $line['shift'] === null) {
            $status = 1;
            $statusAssumed = true;
        }

        $replacementDriverId = null;

        if ($stand_in !== null && !$isVehicleRegister) {
            $replacementDriverId = $this->resolveStandIn($stand_in['name'], $activeDrivers, $problems);
        }

        if ($line['keywords'] !== []) {
            $problems[] = 'Line mentions: ' . implode(', ', $line['keywords']);
        }

        return [
            'whatsapp_message_id' => $message->id,
            'kind' => $parsed['kind'],
            'line_no' => $line['line_no'],
            'raw_line' => mb_substr($line['raw'], 0, 255),
            'attendance_date' => $parsed['date'],
            'vehicle_token' => mb_substr((string) $line['vehicle_token'], 0, 60),
            'driver_name_token' => $primary['name'] === '' ? null : mb_substr($primary['name'], 0, 120),
            'status_token' => $primary['status_token'] === null
                ? null
                : mb_substr($primary['status_token'], 0, 30),
            'vehicle_id' => $vehicle?->id,
            'driver_id' => $driverId,
            'matched_by' => $matchedBy,
            'match_score' => $score === null ? null : (int) round($score * 100),
            'fms_driver_id' => $fmsDriverId,
            'replacement_name_token' => $stand_in === null || $stand_in['name'] === ''
                ? null
                : mb_substr($stand_in['name'], 0, 120),
            'replacement_driver_id' => $replacementDriverId,
            'replacement_vehicle_id' => $replacementVehicle?->id,
            'shift' => $line['shift'],
            'status' => $status,
            'status_assumed' => $statusAssumed,
            'state' => WhatsAppAttendanceDraft::STATE_PENDING,
            'error' => $problems === [] ? null : mb_substr(implode('; ', $problems), 0, 255),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Which driver did the supervisor mean?
     *
     * @param  array<int, array<int, Driver>>  $driversByVehicle
     * @param  \Illuminate\Support\Collection<int, Driver>  $activeDrivers
     * @param  array<int, string>  $problems
     * @return array{driver_id: ?int, matched_by: ?string, score: ?float, fms_driver_id: ?int}
     */
    private function resolveDriver(
        string $name,
        ?Vehicle $vehicle,
        array $driversByVehicle,
        $activeDrivers,
        array &$problems
    ): array {
        $onVehicle = $vehicle === null ? [] : ($driversByVehicle[(int) $vehicle->id] ?? []);
        $fmsDriver = count($onVehicle) === 1 ? $onVehicle[0] : null;

        // No name sent - the register is vehicle numbers only, so the system's
        // own assignment is all there is to go on.
        if ($name === '') {
            if ($fmsDriver !== null) {
                return [
                    'driver_id' => (int) $fmsDriver->id,
                    'matched_by' => 'vehicle',
                    'score' => null,
                    'fms_driver_id' => (int) $fmsDriver->id,
                ];
            }

            $problems[] = count($onVehicle) > 1
                ? count($onVehicle) . ' drivers are on this vehicle and no name was sent'
                : 'No name sent and no driver is assigned to this vehicle';

            return ['driver_id' => null, 'matched_by' => 'none', 'score' => null, 'fms_driver_id' => null];
        }

        // "A waseem & Habib" is two men sharing a vehicle. Choosing between
        // them is a coin toss, so it is not a choice made here.
        if (AttendanceMessageParser::namesTwoPeople($name)) {
            $problems[] = 'Line names two drivers: "' . $name . '"';

            return [
                'driver_id' => null,
                'matched_by' => 'none',
                'score' => null,
                'fms_driver_id' => $fmsDriver === null ? null : (int) $fmsDriver->id,
            ];
        }

        $ranked = $this->matcher->rank($name, $activeDrivers);
        $best = $ranked[0] ?? null;

        if ($best === null || $best['score'] < DriverNameMatcher::CONFIDENT) {
            $problems[] = $best === null
                ? 'No driver matches "' . $name . '"'
                : 'Closest driver to "' . $name . '" is ' . $best['name']
                    . ' (' . round($best['score'] * 100) . '%) - not sure enough';

            return [
                'driver_id' => null,
                'matched_by' => 'none',
                'score' => $best === null ? null : $best['score'],
                'fms_driver_id' => $fmsDriver === null ? null : (int) $fmsDriver->id,
            ];
        }

        // A second candidate just as good as the first is not a match, it is
        // a choice, and it is not one to make without being asked.
        if (isset($ranked[1]) && $ranked[1]['score'] >= $best['score'] - 0.02) {
            $problems[] = '"' . $name . '" could be ' . $best['name'] . ' or ' . $ranked[1]['name'];

            return [
                'driver_id' => null,
                'matched_by' => 'none',
                'score' => $best['score'],
                'fms_driver_id' => $fmsDriver === null ? null : (int) $fmsDriver->id,
            ];
        }

        // The name won. Say so plainly when the system disagrees.
        if ($fmsDriver !== null && (int) $fmsDriver->id !== $best['id']) {
            $problems[] = 'Supervisor says ' . $best['name'] . '; FMS has '
                . $fmsDriver->full_name . ' on this vehicle';
        }

        return [
            'driver_id' => $best['id'],
            'matched_by' => 'name',
            'score' => $best['score'],
            'fms_driver_id' => $fmsDriver === null ? null : (int) $fmsDriver->id,
        ];
    }

    /**
     * The man who stood in, named after the one he covered.
     *
     * @param  \Illuminate\Support\Collection<int, Driver>  $activeDrivers
     * @param  array<int, string>  $problems
     */
    private function resolveStandIn(string $name, $activeDrivers, array &$problems): ?int
    {
        if ($name === '') {
            return null;
        }

        $ranked = $this->matcher->rank($name, $activeDrivers);
        $best = $ranked[0] ?? null;

        if ($best === null || $best['score'] < DriverNameMatcher::CONFIDENT) {
            $problems[] = 'Stand-in "' . $name . '" could not be matched to a driver';

            return null;
        }

        return $best['id'];
    }

    /**
     * @param  array<string, array<int, int>>  $vehiclesByNumber
     * @param  array<int, string>  $problems
     */
    private function resolveVehicle(?string $token, array $vehiclesByNumber, array &$problems): ?Vehicle
    {
        $key = AttendanceMessageParser::normalizeVehicleToken($token);

        if ($key === '' || !isset($vehiclesByNumber[$key])) {
            $problems[] = 'No vehicle matches "' . $token . '"';

            return null;
        }

        $ids = $vehiclesByNumber[$key];

        // The live data has one vehicle number on two rows, so this happens.
        if (count($ids) > 1) {
            $problems[] = count($ids) . ' vehicles share the number ' . $token;

            return null;
        }

        return Vehicle::find($ids[0]);
    }

    /**
     * Vehicle numbers, normalised the same way the parser normalises what a
     * supervisor typed. Several ids per key because the number is not unique.
     *
     * @return array<string, array<int, int>>
     */
    private function vehiclesByNumber(): array
    {
        $map = [];

        foreach (Vehicle::select('id', 'vehicle_no')->get() as $vehicle) {
            $key = AttendanceMessageParser::normalizeVehicleToken($vehicle->vehicle_no);

            if ($key !== '') {
                $map[$key][] = (int) $vehicle->id;
            }
        }

        return $map;
    }

    /** @return array<int, array<int, Driver>> */
    private function driversByVehicle(): array
    {
        $map = [];

        $drivers = Driver::select('id', 'full_name', 'vehicle_id', 'station_id')
            ->where('is_active', 1)
            ->whereNotNull('vehicle_id')
            ->where('vehicle_id', '>', 0)
            ->get();

        foreach ($drivers as $driver) {
            $map[(int) $driver->vehicle_id][] = $driver;
        }

        return $map;
    }
}
