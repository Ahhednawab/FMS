<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackingReport extends Model
{
    protected $fillable = [
        'report_date',
        'vehicle_no',
        'display_vehicle_no',
        'akpl',
        'shift',
        'peak_kms',
        'api_off_peak_kms',
        'api_ams_kms',
        'off_peak',
        'mis_peak_hrs',
        'ams',
        'parking',
        'total_kms',
        'odo_kms',
        'diff',
    ];

    protected $casts = [
        'report_date' => 'date',
        'peak_kms' => 'decimal:2',
        'api_off_peak_kms' => 'decimal:2',
        'api_ams_kms' => 'decimal:2',
        'off_peak' => 'decimal:2',
        'mis_peak_hrs' => 'decimal:2',
        'ams' => 'decimal:2',
        'parking' => 'decimal:2',
        'total_kms' => 'decimal:2',
        'odo_kms' => 'decimal:2',
        'diff' => 'decimal:2',
    ];
}
