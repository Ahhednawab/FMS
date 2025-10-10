<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DriversAttendance extends Model
{
    protected $fillable = [
        'driver_id',
        'date',
        'status',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function driverAttendanceStatus()
    {
        return $this->belongsTo(DriverAttendanceStatus::class, 'status');
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
