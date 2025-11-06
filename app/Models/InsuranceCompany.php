<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InsuranceCompany extends Model
{
    protected $table = 'insurance_companies';
    protected $fillable = [
        'serial_no',
        'name',
        'is_active',
        'deleted_at',
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public static function getSerialNumber()
    {
        $serial_no = DB::table('insurance_companies');
        $serial_no = $serial_no->select(DB::raw("LPAD(IFNULL(MAX(id) + 1, 1), 9, '0') AS id"));
        $serial_no = $serial_no->first()->id;

        return $serial_no;
    }
}
