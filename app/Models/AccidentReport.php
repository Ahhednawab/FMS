<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AccidentReport extends Model
{
    protected $fillable = [
        'accident_report_id',
        'accident_type',
        'location',
        'accident_date',
        'accident_time',
        'accident_description',
        'person_involved',
        'injury_type',
        'damage_type',
        'witness_involved',
        'vehicle_no',
        'primary_cause',
        'medical_provided',
        'police_report_filed',
        'investigation_status',
        'insurance_claimed',
        'insurance_doc',
        'police_report_file',
    ];

    public static function GetAccidentReportId()
    {
        $accident_report_id = DB::table('accident_reports');
        $accident_report_id = $accident_report_id->select(DB::RAW("LPAD(IFNULL( MAX( id ) +1, 1 ),9,'0') AS id"));
        $accident_report_id = $accident_report_id->first()->id;

        return $accident_report_id;
    }
}
