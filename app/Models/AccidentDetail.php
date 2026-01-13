<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AccidentDetail extends Model
{
    protected $fillable = [
        'accident_id',
        'vehicle_no',
        'insurance',
        'ownership',
        'driver_name',
        'licence_no',
        'policy_no',
        'workshop',
        'third_party',
        'claim_amount',
        'depreciation_amount',
        'remarks',
        'bill_to_ke',
        'payment_status',
        'created_by',
    ];

    public function files()
    {
        return $this->hasMany(AccidentDetailFile::class, 'accident_detail_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function GetAccidentId()
    {
        $accident_id = DB::table('accident_details');
        $accident_id = $accident_id->select(DB::RAW("LPAD(IFNULL( MAX( id ) +1, 1 ),9,'0') AS id"));
        $accident_id = $accident_id->first()->id;

        return $accident_id;
    }
}
