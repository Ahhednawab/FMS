<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Warehouse extends Model
{
    protected $fillable = [
        'name',
        'country_id',
        'city_id',
        'location',
    ];

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function station()
    {
        return $this->belongsTo(Station::class, 'station_id');
    }

    public static function GetSerialNumber()
    {
        $serial_no = DB::table('warehouses');
        $serial_no = $serial_no->select(DB::RAW("LPAD(IFNULL( MAX( id ) +1, 1 ),9,'0') AS id"));
        $serial_no = $serial_no->first()->id;

        return $serial_no;
    }
}

