<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DriversAttendance extends Model
{
    protected $fillable = [
        'driver_id',
        'vehicle_id',
        'original_driver_id',
        'replacement_driver_id',
        'is_replacement',
        'date',
        'status',
    ];

    protected $casts = [
        'is_replacement' => 'boolean',
        'date' => 'date',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function originalDriver()
    {
        return $this->belongsTo(Driver::class, 'original_driver_id');
    }

    public function replacementDriver()
    {
        return $this->belongsTo(Driver::class, 'replacement_driver_id');
    }

    public function attendanceStatus()
    {
        return $this->belongsTo(AttendanceStatus::class, 'status');
    }


    public static function GetSerialNumber()
    {
        $serial_no = DB::table('drivers_attendances');
        $serial_no = $serial_no->select(DB::RAW("LPAD(IFNULL( MAX( id ) +1, 1 ),9,'0') AS id"));
        $serial_no = $serial_no->first()->id;

        return $serial_no;
    }

    // Relationships (already declared above)
}
