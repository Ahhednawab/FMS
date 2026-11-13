<?php

namespace App\Http\Controllers;

use App\Models\AttendanceStatus;
use App\Models\Driver;
use App\Models\DriversAttendance;
use App\Models\VehiclesAttendance;
use App\Models\WhatsAppAttendanceDraft;
use App\Models\WhatsAppSender;
use App\Services\DriverAvailabilitySync;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * The review screen for attendance that arrived over WhatsApp.
 *
 * Everything a supervisor sends waits here. A row shows what was typed, what
 * FMS thinks it means, and what is wrong with it if anything is - then a person
 * corrects it and confirms. Confirming is the only thing in this module that
 * writes to `drivers_attendances`.
 */
class WhatsAppAttendanceController extends Controller
{
    private const STATES = [
        WhatsAppAttendanceDraft::STATE_PENDING => 'Pending review',
        WhatsAppAttendanceDraft::STATE_CONFIRMED => 'Confirmed',
        WhatsAppAttendanceDraft::STATE_REJECTED => 'Rejected',
    ];

    public function __construct(private DriverAvailabilitySync $availability)
    {
    }

    public function index(Request $request)
    {
        $state = $request->query('state', WhatsAppAttendanceDraft::STATE_PENDING);
        $state = array_key_exists($state, self::STATES) ? $state : WhatsAppAttendanceDraft::STATE_PENDING;

        $date = (string) $request->query('date', '');
        $senderId = (string) $request->query('sender_id', '');

        $perPage = (int) $request->query('per_page', 50);
        $perPage = in_array($perPage, [25, 50, 100], true) ? $perPage : 50;

        $drafts = WhatsAppAttendanceDraft::query()
            ->with([
                'message.sender',
                'vehicle:id,vehicle_no',
                'replacementVehicle:id,vehicle_no',
                'driver:id,full_name,serial_no,vehicle_id',
                'fmsDriver:id,full_name',
                'replacementDriver:id,full_name',
                'attendanceStatus',
                'confirmedBy:id,name',
            ])
            ->where('state', $state)
            ->when($date !== '', fn ($query) => $query->whereDate('attendance_date', $date))
            ->when($senderId !== '', fn ($query) => $query->whereHas(
                'message',
                fn ($message) => $message->where('whatsapp_sender_id', $senderId)
            ))
            ->orderByDesc('attendance_date')
            ->orderBy('whatsapp_message_id')
            ->orderBy('line_no')
            ->paginate($perPage)
            ->withQueryString();

        return view('whatsapp.attendance', [
            'drafts' => $drafts,
            'state' => $state,
            'states' => self::STATES,
            'date' => $date,
            'senderId' => $senderId,
            'perPage' => $perPage,
            'statusOptions' => AttendanceStatus::where('is_active', 1)->orderBy('id')->pluck('name', 'id'),
            'senderOptions' => WhatsAppSender::orderBy('name')->pluck('name', 'id'),
            'driverChoices' => $this->driverChoices($drafts->getCollection()),
            'allDrivers' => Driver::where('is_active', 1)->orderBy('full_name')->pluck('full_name', 'id'),
            'existingAttendance' => $this->existingAttendance($drafts->getCollection()),
            'counts' => $this->counts($date, $senderId),
        ]);
    }

    /**
     * Write the ticked drafts into `drivers_attendances`.
     *
     * Corrections made on screen (driver, status, date) are applied to the
     * draft first, so the draft always records what was actually confirmed,
     * not just what WhatsApp originally said.
     */
    public function confirm(Request $request)
    {
        $validated = $request->validate([
            'draft_ids' => ['required', 'array', 'min:1'],
            'draft_ids.*' => ['integer'],
            'driver_id' => ['array'],
            'status' => ['array'],
            'attendance_date' => ['array'],
        ]);

        if ($truncated = $this->truncatedPostMessage($request)) {
            return back()->withErrors(['draft_ids' => $truncated]);
        }

        $drafts = WhatsAppAttendanceDraft::whereIn('id', $validated['draft_ids'])
            ->where('state', WhatsAppAttendanceDraft::STATE_PENDING)
            ->get();

        $written = 0;
        $overwritten = 0;
        $skipped = [];

        DB::transaction(function () use ($request, $drafts, &$written, &$overwritten, &$skipped) {
            foreach ($drafts as $draft) {
                $this->applyCorrections($draft, $request);

                if ($reason = $draft->confirmBlockedReason()) {
                    $skipped[] = $draft->raw_line . ' (' . $reason . ')';

                    continue;
                }

                $result = $draft->isVehicleRegister()
                    ? $this->confirmVehicle($draft)
                    : $this->confirmDriver($draft);

                if ($result['replaced']) {
                    $overwritten++;
                }

                $draft->update([
                    'state' => WhatsAppAttendanceDraft::STATE_CONFIRMED,
                    'drivers_attendance_id' => $result['id'],
                    'confirmed_by' => Auth::id(),
                    'confirmed_at' => now(),
                ]);

                $written++;
            }
        });

        $message = $written === 1
            ? '1 attendance entry saved.'
            : $written . ' attendance entries saved.';

        if ($overwritten > 0) {
            $message .= ' ' . $overwritten . ' replaced an entry that already existed.';
        }

        if ($skipped !== []) {
            return back()
                ->with('success', $message)
                ->withErrors(['draft_ids' => 'Not saved: ' . implode(', ', array_slice($skipped, 0, 10))]);
        }

        return back()->with('success', $message);
    }

    /** Close the ticked drafts without writing attendance. */
    public function reject(Request $request)
    {
        $validated = $request->validate([
            'draft_ids' => ['required', 'array', 'min:1'],
            'draft_ids.*' => ['integer'],
        ]);

        $rejected = WhatsAppAttendanceDraft::whereIn('id', $validated['draft_ids'])
            ->where('state', WhatsAppAttendanceDraft::STATE_PENDING)
            ->update([
                'state' => WhatsAppAttendanceDraft::STATE_REJECTED,
                'confirmed_by' => Auth::id(),
                'confirmed_at' => now(),
            ]);

        return back()->with('success', $rejected . ' line(s) rejected. No attendance was written.');
    }

    /**
     * Write one driver register line.
     *
     * A line that names a stand-in - "Nizam 🅰️ Imran akhtr 🅿️" - is saved the
     * way the Driver Attendance screen saves one: a single Replace row whose
     * `driver_id` is the man who actually drove, with the man he covered kept
     * in `original_driver_id`. Keeping the two screens identical matters more
     * than recording the absence separately, which this schema has no room for.
     *
     * @return array{id: int, replaced: bool}
     */
    private function confirmDriver(WhatsAppAttendanceDraft $draft): array
    {
        $isReplacement = $draft->replacement_driver_id !== null
            && in_array((int) $draft->status, WhatsAppAttendanceDraft::AWAY_STATUSES, true);

        $driverId = $isReplacement ? (int) $draft->replacement_driver_id : (int) $draft->driver_id;
        $status = $isReplacement ? WhatsAppAttendanceDraft::REPLACE_STATUS : (int) $draft->status;

        $replaced = DriversAttendance::where('driver_id', $driverId)
            ->whereDate('date', $draft->attendance_date)
            ->exists();

        $attendance = DriversAttendance::updateOrCreate(
            [
                'driver_id' => $driverId,
                'date' => $draft->attendance_date->toDateString(),
            ],
            [
                'vehicle_id' => $draft->vehicle_id ?: $draft->driver?->vehicle_id,
                'original_driver_id' => $isReplacement ? (int) $draft->driver_id : null,
                'replacement_driver_id' => $isReplacement ? (int) $draft->replacement_driver_id : null,
                'is_replacement' => $isReplacement,
                'status' => $status,
                'is_active' => 1,
            ]
        );

        // The same thing the manual screen does once a row is saved.
        $this->availability->forDriver($driverId, $status);

        if ($isReplacement) {
            $this->availability->forDriver((int) $draft->driver_id, (int) $draft->status);
        }

        return ['id' => $attendance->id, 'replaced' => $replaced];
    }

    /**
     * Write one vehicle register line. The stand-in vehicle on a line like
     * "Jz 9853 inspection replecment LE-1940" goes in `pool_id`, which is what
     * that column is for.
     *
     * @return array{id: int, replaced: bool}
     */
    private function confirmVehicle(WhatsAppAttendanceDraft $draft): array
    {
        $replaced = VehiclesAttendance::where('vehicle_id', $draft->vehicle_id)
            ->whereDate('date', $draft->attendance_date)
            ->exists();

        $attendance = VehiclesAttendance::updateOrCreate(
            [
                'vehicle_id' => $draft->vehicle_id,
                'date' => $draft->attendance_date->toDateString(),
            ],
            [
                'pool_id' => $draft->replacement_vehicle_id,
                'status' => $draft->status,
                'is_active' => 1,
            ]
        );

        return ['id' => $attendance->id, 'replaced' => $replaced];
    }

    /**
     * Apply whatever the reviewer changed on screen before deciding whether a
     * draft can be confirmed.
     */
    private function applyCorrections(WhatsAppAttendanceDraft $draft, Request $request): void
    {
        $changes = [];

        $driverId = $request->input('driver_id.' . $draft->id);
        if ($driverId !== null && $driverId !== '' && (int) $driverId !== (int) $draft->driver_id) {
            $changes['driver_id'] = (int) $driverId;
        }

        $status = $request->input('status.' . $draft->id);
        if ($status !== null && $status !== '' && (int) $status !== (int) $draft->status) {
            $changes['status'] = (int) $status;
        }

        $date = $request->input('attendance_date.' . $draft->id);
        if ($date !== null && $date !== '' && $date !== $draft->attendance_date?->toDateString()) {
            $changes['attendance_date'] = $date;
        }

        if ($changes !== []) {
            $draft->fill($changes);
            $draft->save();
            $draft->refresh();
        }
    }

    /**
     * PHP silently drops every field past max_input_vars, which on a long list
     * would look like a success while saving only part of it. The form sends
     * how many rows it rendered so a truncated POST can be refused outright.
     */
    private function truncatedPostMessage(Request $request): ?string
    {
        $expected = (int) $request->input('rendered_row_count', 0);

        if ($expected <= 0) {
            return null;
        }

        $received = count((array) $request->input('status', []));

        if ($received >= $expected) {
            return null;
        }

        return 'Only ' . $received . ' of ' . $expected . ' rows reached the server, so nothing was saved. '
            . 'Show fewer rows per page, or ask your administrator to raise the PHP max_input_vars limit.';
    }

    /**
     * The drivers a reviewer may pick for each draft. For a row whose vehicle
     * was recognised the list is that vehicle's drivers, which is what makes
     * the two-drivers-on-one-vehicle case a one-click decision.
     *
     * @param  \Illuminate\Support\Collection<int, WhatsAppAttendanceDraft>  $drafts
     * @return array<int, array<int, string>>
     */
    private function driverChoices($drafts): array
    {
        $vehicleIds = $drafts->pluck('vehicle_id')->filter()->unique()->values();

        if ($vehicleIds->isEmpty()) {
            return [];
        }

        $choices = [];

        $drivers = Driver::select('id', 'full_name', 'vehicle_id')
            ->where('is_active', 1)
            ->whereIn('vehicle_id', $vehicleIds)
            ->orderBy('full_name')
            ->get();

        foreach ($drivers as $driver) {
            $choices[(int) $driver->vehicle_id][(int) $driver->id] = (string) $driver->full_name;
        }

        // The man the supervisor named belongs in the list even when the
        // system has him on another vehicle - usually he is the right answer.
        foreach ($drafts as $draft) {
            if ($draft->vehicle_id === null || $draft->driver_id === null) {
                continue;
            }

            $choices[(int) $draft->vehicle_id][(int) $draft->driver_id] =
                (string) ($draft->driver->full_name ?? $draft->driver_name_token);
        }

        return $choices;
    }

    /**
     * Attendance that already exists for the drafts on screen, so a row can
     * warn that confirming it will replace something.
     *
     * @param  \Illuminate\Support\Collection<int, WhatsAppAttendanceDraft>  $drafts
     * @return array<string, string>  "driverId|Y-m-d" => status name
     */
    private function existingAttendance($drafts): array
    {
        $pairs = $drafts->filter(fn ($draft) => $draft->driver_id && $draft->attendance_date);

        if ($pairs->isEmpty()) {
            return [];
        }

        $rows = DriversAttendance::with('attendanceStatus')
            ->whereIn('driver_id', $pairs->pluck('driver_id')->unique()->values())
            ->whereIn('date', $pairs->pluck('attendance_date')->map->toDateString()->unique()->values())
            ->get();

        $existing = [];

        foreach ($rows as $row) {
            $key = $row->driver_id . '|' . $row->date->toDateString();
            $existing[$key] = $row->attendanceStatus->name ?? ('status ' . $row->status);
        }

        return $existing;
    }

    /**
     * How many drafts sit in each state for the current filter, for the tabs.
     *
     * @return array<string, int>
     */
    private function counts(string $date, string $senderId): array
    {
        $rows = WhatsAppAttendanceDraft::query()
            ->selectRaw('state, COUNT(*) as total')
            ->when($date !== '', fn ($query) => $query->whereDate('attendance_date', $date))
            ->when($senderId !== '', fn ($query) => $query->whereHas(
                'message',
                fn ($message) => $message->where('whatsapp_sender_id', $senderId)
            ))
            ->groupBy('state')
            ->pluck('total', 'state');

        return collect(self::STATES)
            ->map(fn ($label, $key) => (int) ($rows[$key] ?? 0))
            ->all();
    }
}
