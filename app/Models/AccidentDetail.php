<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AccidentDetail extends Model
{
    protected $fillable = [
        'accident_type',
        'location',
        'accident_date',
        'accident_time',
        'accident_description',
        'person_involved',
        'injury_type',
        'damage_type',
    ];

    public static function GetAccidentId()
    {
        $accident_id = DB::table('accident_details');
        $accident_id = $accident_id->select(DB::RAW("LPAD(IFNULL( MAX( id ) +1, 1 ),9,'0') AS id"));
        $accident_id = $accident_id->first()->id;

        return $accident_id;
    }
}
