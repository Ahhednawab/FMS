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
 * decided that a line says "JZ-9036 is present", and this works out which
 * driver that actually is - or records why it could not tell.
 *
 * Nothing here writes attendance. Every row it creates is a draft waiting for
 * somebody to confirm it, which is the whole point of the design: the live
 * data has vehicles with two drivers on them and a vehicle number that exists
 * twice, so there are lines no amount of parsing can resolve on its own.
 */
class AttendanceDraftBuilder
{
    public function __construct(private AttendanceMessageParser $parser)
    {
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
                'Not an attendance message (it does not start with ATT).'
            );

            return 0;
        }

        // Drafts from an earlier delivery of this same message are replaced,
        // so a re-run cannot leave two copies of every line behind. Anything
        // already confirmed is left alone - that is real attendance now.
        $message->drafts()->where('state', WhatsAppAttendanceDraft::STATE_PENDING)->delete();

        $vehiclesByNumber = $this->vehiclesByNumber();
        $driversBySerial = $this->driversBySerial();
        $driversByVehicle = $this->driversByVehicle();
        $senderStationId = $message->sender?->station_id;

        $rows = [];

        foreach ($parsed['lines'] as $line) {
            $problems = [];

            if ($line['error'] !== null) {
                $problems[] = $line['error'];
            }

            if ($parsed['date_error'] !== null) {
                $problems[] = $parsed['date_error'];
            }

            $resolved = $this->resolveDriver(
                $line['vehicle_token'],
                $vehiclesByNumber,
                $driversBySerial,
                $driversByVehicle,
                $senderStationId
            );

            foreach ($resolved['problems'] as $problem) {
                $problems[] = $problem;
            }

            $rows[] = [
                'whatsapp_message_id' => $message->id,
                'line_no' => $line['line_no'],
                'raw_line' => mb_substr($line['raw'], 0, 255),
                'attendance_date' => $parsed['date'],
                'vehicle_token' => $line['vehicle_token'] === null
                    ? null
                    : mb_substr($line['vehicle_token'], 0, 60),
                'status_token' => $line['status_token'] === null
                    ? null
                    : mb_substr($line['status_token'], 0, 30),
                'vehicle_id' => $resolved['vehicle_id'],
                'driver_id' => $resolved['driver_id'],
                'status' => $line['status'],
                'state' => WhatsAppAttendanceDraft::STATE_PENDING,
                'error' => $problems === [] ? null : mb_substr(implode('; ', $problems), 0, 255),
                'created_at' => now(),
                'updated_at' => now(),
            ];
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
     * Which driver does this vehicle number belong to?
     *
     * @param  array<string, array<int, int>>  $vehiclesByNumber
     * @param  array<string, array<int, int>>  $driversBySerial
     * @param  array<int, array<int, \App\Models\Driver>>  $driversByVehicle
     * @return array{vehicle_id: ?int, driver_id: ?int, problems: array<int, string>}
     */
    private function resolveDriver(
        ?string $vehicleToken,
        array $vehiclesByNumber,
        array $driversBySerial,
        array $driversByVehicle,
        ?int $senderStationId
    ): array {
        $key = AttendanceMessageParser::normalizeVehicleToken($vehicleToken);

        if ($key === '') {
            return ['vehicle_id' => null, 'driver_id' => null, 'problems' => ['No vehicle number on this line']];
        }

        // A supervisor may send the driver code instead of the vehicle number.
        if (!isset($vehiclesByNumber[$key]) && isset($driversBySerial[$key])) {
            $driverIds = $driversBySerial[$key];

            if (count($driverIds) > 1) {
                return [
                    'vehicle_id' => null,
                    'driver_id' => null,
                    'problems' => ['Driver code ' . $vehicleToken . ' matches more than one driver'],
                ];
            }

            $driver = Driver::find($driverIds[0]);

            return [
                'vehicle_id' => $driver?->vehicle_id ?: null,
                'driver_id' => $driver?->id,
                'problems' => $this->stationProblems($driver, $senderStationId),
            ];
        }

        if (!isset($vehiclesByNumber[$key])) {
            return [
                'vehicle_id' => null,
                'driver_id' => null,
                'problems' => ['No vehicle matches "' . $vehicleToken . '"'],
            ];
        }

        $vehicleIds = $vehiclesByNumber[$key];

        // The live data has one vehicle number on two rows, so this really happens.
        if (count($vehicleIds) > 1) {
            return [
                'vehicle_id' => null,
                'driver_id' => null,
                'problems' => [count($vehicleIds) . ' vehicles share the number ' . $vehicleToken],
            ];
        }

        $vehicleId = $vehicleIds[0];
        $drivers = $driversByVehicle[$vehicleId] ?? [];

        if ($drivers === []) {
            return [
                'vehicle_id' => $vehicleId,
                'driver_id' => null,
                'problems' => ['No active driver is assigned to ' . $vehicleToken],
            ];
        }

        // Nine vehicles currently carry two active drivers. Picking one would
        // be a guess, so the reviewer is asked instead.
        if (count($drivers) > 1) {
            return [
                'vehicle_id' => $vehicleId,
                'driver_id' => null,
                'problems' => [count($drivers) . ' drivers are on ' . $vehicleToken . ' - choose one'],
            ];
        }

        return [
            'vehicle_id' => $vehicleId,
            'driver_id' => $drivers[0]->id,
            'problems' => $this->stationProblems($drivers[0], $senderStationId),
        ];
    }

    /**
     * A supervisor marks their own station. Anything else is flagged rather
     * than blocked, since a driver can genuinely be moved between stations.
     *
     * @return array<int, string>
     */
    private function stationProblems(?Driver $driver, ?int $senderStationId): array
    {
        if ($driver === null || $senderStationId === null || $driver->station_id === null) {
            return [];
        }

        return (int) $driver->station_id === (int) $senderStationId
            ? []
            : ['Driver belongs to another station'];
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

    /** @return array<string, array<int, int>> */
    private function driversBySerial(): array
    {
        $map = [];

        $drivers = Driver::select('id', 'serial_no')
            ->where('is_active', 1)
            ->whereNotNull('serial_no')
            ->get();

        foreach ($drivers as $driver) {
            $key = AttendanceMessageParser::normalizeVehicleToken($driver->serial_no);

            if ($key !== '') {
                $map[$key][] = (int) $driver->id;
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
