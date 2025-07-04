<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TrackerMileageReport extends Model
{
    protected $table = 'tracker_mileage_report';

    protected $fillable = [
    	'vehicle_no',
        'day',
        'date',
        'akpl',
        'ibc_center',
        'before_peak_one_hour',
        'before_peak_two_hour',
        'kms_driven_peak',
        'kms_driven_off_peak',
        'total_kms_in_a_day',
        'after_peak_one_hour',
        'after_peak_two_hour',
        'difference',
        'odo_meter',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function days()
    {
        return $this->belongsTo(Days::class, 'day');
    }

    public function ibcCenter()
    {
        return $this->belongsTo(IbcCenter::class, 'ibc_center');
    }

    public static function GetSerialNumber()
    {
        $serial_no = DB::table('tracker_mileage_report');
        $serial_no = $serial_no->select(DB::RAW("LPAD(IFNULL( MAX( id ) +1, 1 ),9,'0') AS id"));
        $serial_no = $serial_no->first()->id;

        return $serial_no;
    }
}
