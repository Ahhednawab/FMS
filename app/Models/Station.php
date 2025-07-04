<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Station extends Model
{
    protected $table = 'stations';

    protected $fillable = [
    	'area',
    ];

    public function ibcCenter()
    {
        return $this->hasMany(IbcCenter::class, 'station_id');
    }

    public static function GetSerialNumber()
    {
        $serial_no = DB::table('stations');
        $serial_no = $serial_no->select(DB::RAW("LPAD(IFNULL( MAX( id ) +1, 1 ),9,'0') AS id"));
        $serial_no = $serial_no->first()->id;

        return $serial_no;
    }
}
