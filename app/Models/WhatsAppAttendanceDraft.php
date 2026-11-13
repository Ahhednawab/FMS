<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One attendance line waiting to be reviewed.
 *
 * A draft is not attendance. It becomes attendance only when somebody confirms
 * it, and `drivers_attendance_id` then records the row that was written.
 */
class WhatsAppAttendanceDraft extends Model
{
    public const STATE_PENDING = 'pending';
    public const STATE_CONFIRMED = 'confirmed';
    public const STATE_REJECTED = 'rejected';

    /** The two registers, which are confirmed into two different tables. */
    public const KIND_DRIVER = 'driver';
    public const KIND_VEHICLE = 'vehicle';

    /**
     * `attendance_status` id 4, Replace. A replace entry needs the pool driver
     * who stood in as well, which the real registers do carry - "Nizam 🅰️
     * Imran akhtr 🅿️" names both - so these can be confirmed as long as the
     * stand-in was recognised.
     */
    public const REPLACE_STATUS = 4;

    /** Statuses that mean somebody else may have driven that day. */
    public const AWAY_STATUSES = [2, 3, 4];

    protected $table = 'whatsapp_attendance_drafts';

    protected $fillable = [
        'whatsapp_message_id',
        'kind',
        'line_no',
        'raw_line',
        'attendance_date',
        'vehicle_token',
        'driver_name_token',
        'status_token',
        'vehicle_id',
        'driver_id',
        'matched_by',
        'match_score',
        'fms_driver_id',
        'replacement_name_token',
        'replacement_driver_id',
        'replacement_vehicle_id',
        'shift',
        'status',
        'status_assumed',
        'state',
        'error',
        'drivers_attendance_id',
        'confirmed_by',
        'confirmed_at',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'confirmed_at' => 'datetime',
        'status_assumed' => 'boolean',
    ];

    public function message()
    {
        return $this->belongsTo(WhatsAppMessage::class, 'whatsapp_message_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function attendanceStatus()
    {
        return $this->belongsTo(AttendanceStatus::class, 'status');
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    /** The driver `drivers.vehicle_id` puts on this vehicle, where it differs. */
    public function fmsDriver()
    {
        return $this->belongsTo(Driver::class, 'fms_driver_id');
    }

    /** The man who stood in, when the line named one. */
    public function replacementDriver()
    {
        return $this->belongsTo(Driver::class, 'replacement_driver_id');
    }

    /** The stand-in vehicle, on a vehicle register line. */
    public function replacementVehicle()
    {
        return $this->belongsTo(Vehicle::class, 'replacement_vehicle_id');
    }

    public function isVehicleRegister(): bool
    {
        return $this->kind === self::KIND_VEHICLE;
    }

    /** Did the supervisor and FMS disagree about who drives this vehicle? */
    public function disagreesWithFms(): bool
    {
        return $this->matched_by === 'name'
            && $this->fms_driver_id !== null
            && (int) $this->fms_driver_id !== (int) $this->driver_id;
    }

    /**
     * Whether this draft holds everything a `drivers_attendances` row needs.
     * A draft can be missing the driver (unknown vehicle number, or a vehicle
     * with two drivers on it) or the status, and then it cannot be confirmed
     * until the reviewer fills the gap in.
     */
    public function isReady(): bool
    {
        return $this->confirmBlockedReason() === null;
    }

    /**
     * Why this draft cannot be confirmed yet, in words a reviewer can act on,
     * or null when it is ready to write.
     */
    public function confirmBlockedReason(): ?string
    {
        if ($this->state !== self::STATE_PENDING) {
            return 'Already ' . $this->state;
        }

        if ($this->attendance_date === null) {
            return 'Set the date first';
        }

        if ($this->status === null) {
            return 'Choose the status first';
        }

        // The vehicle register needs nothing but the vehicle and the status.
        if ($this->isVehicleRegister()) {
            return $this->vehicle_id === null ? 'Choose the vehicle first' : null;
        }

        if ($this->driver_id === null) {
            return 'Choose the driver first';
        }

        // A Replace entry is only half a record without the man who stood in.
        if ((int) $this->status === self::REPLACE_STATUS && $this->replacement_driver_id === null) {
            return 'Replace needs the stand-in driver';
        }

        return null;
    }
}
