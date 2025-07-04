<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DailyMileageReport extends Model
{
    protected $table = 'daily_mileage_report';

    protected $fillable = [
    	'vehicle_no',
        'location',
        'remarks',
        'date',
        'mileage',
        'last_third_month_km',
        'last_second_month_km',
        'last_month_km',
        'current_month_km',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function mileageStatus()
    {
        return $this->belongsTo(MileageStatus::class, 'mileage');
    }


    public static function GetSerialNumber()
    {
        $serial_no = DB::table('daily_mileage_report');
        $serial_no = $serial_no->select(DB::RAW("LPAD(IFNULL( MAX( id ) +1, 1 ),9,'0') AS id"));
        $serial_no = $serial_no->first()->id;

        return $serial_no;
    }
}
