<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class IbcCenter extends Model
{
    protected $table = 'ibc_center';
    protected $fillable = ['name','station_id'];

    public function station()
    {
        return $this->belongsTo(Station::class, 'station_id');
    }

    public static function GetSerialNumber()
    {
        $serial_no = DB::table('ibc_center');
        $serial_no = $serial_no->select(DB::RAW("LPAD(IFNULL( MAX( id ) +1, 1 ),9,'0') AS id"));
        $serial_no = $serial_no->first()->id;

        return $serial_no;
    }
}
