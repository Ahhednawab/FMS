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

    /**
     * `attendance_status` id 4, Replace. A replace entry also needs the pool
     * driver who stood in, which a WhatsApp line does not carry, so those are
     * sent to the normal Driver Attendance screen instead of being half-saved.
     */
    public const REPLACE_STATUS = 4;

    protected $table = 'whatsapp_attendance_drafts';

    protected $fillable = [
        'whatsapp_message_id',
        'line_no',
        'raw_line',
        'attendance_date',
        'vehicle_token',
        'status_token',
        'vehicle_id',
        'driver_id',
        'status',
        'state',
        'error',
        'drivers_attendance_id',
        'confirmed_by',
        'confirmed_at',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'confirmed_at' => 'datetime',
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

    /**
     * Whether this draft holds everything a `drivers_attendances` row needs.
     * A draft can be missing the driver (unknown vehicle number, or a vehicle
     * with two drivers on it) or the status, and then it cannot be confirmed
     * until the reviewer fills the gap in.
     */
    public function isReady(): bool
    {
        return $this->state === self::STATE_PENDING
            && $this->driver_id !== null
            && $this->status !== null
            && $this->attendance_date !== null;
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

        if ($this->driver_id === null) {
            return 'Choose the driver first';
        }

        if ($this->status === null) {
            return 'Choose the status first';
        }

        if ((int) $this->status === self::REPLACE_STATUS) {
            return 'Replace needs a pool driver - mark this one on the Driver Attendance screen';
        }

        return null;
    }
}
