<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'serial_no',
        'full_name',
        'father_name',
        'mother_name',
        'phone',
        'salary',
        'account_no',
        'driver_status_id',
        'marital_status_id',
        'dob',
        'vehicle_id',
        'shift_timing_id',
        'cnic_no',
        'cnic_expiry_date',
        'cnic_file',
        'eobi_no',
        'eobi_start_date',
        'eobi_card_file',
        'picture_file',
        'medical_report_file',
        'authority_letter_file',
        'employment_date',
        'employee_card_file',
        'ddc_file',
        'third_party_driver_file',
        'license_no',
        'license_category_id',
        'license_expiry_date',
        'license_file',
        'uniform_issue_date',
        'sandal_issue_date',
        'address',
        'last_date',
        'employee_code',      // new
        'ke_card_serial',     // new
        'location',           // new
        'designation',        // new
    ];


    public function driverStatus()
    {
        return $this->belongsTo(DriverStatus::class, 'driver_status_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function maritalStatus()
    {
        return $this->belongsTo(MaritalStatus::class, 'marital_status_id');
    }

    public function licenseCategory()
    {
        return $this->belongsTo(LicenseCategory::class, 'license_category_id');
    }

    public function shiftTiming()
    {
        return $this->belongsTo(ShiftTimings::class, 'shift_timing_id');
    }


    public function salaries()
    {
        return $this->hasMany(Salary::class);
    }

    public function advance()
    {
        return $this->hasOne(EmployeeAdvance::class)
            ->where('is_closed', false)
            ->latest('advance_date'); // get the latest open advance
    }


    public static function GetSerialNumber()
    {
        $serial_no = DB::table('drivers');
        $serial_no = $serial_no->select(DB::RAW("LPAD(IFNULL( MAX( id ) +1, 1 ),9,'0') AS id"));
        $serial_no = $serial_no->first()->id;

        return $serial_no;
    }

    public function getAdvanceBalanceAttribute()
    {
        return $this->advance()
            ->where('is_closed', false)
            ->sum('remaining_amount');
    }
}
