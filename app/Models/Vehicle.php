<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Vehicle extends Model
{
    use HasFactory;

    protected $table = 'vehicles';

    protected $fillable = [
        'vehicle_no',
        'make',
        'model',
        'chasis_no',
        'engine_no',
        'ownership',
        'pool_vehicle',
        'vehicle_type_id',
        'cone',
        'station_id',
        'ibc_center_id',
        'ladder_maker_id',
        'medical_box',
        'on_duty_status',
        'seat_cover',
        'fire_extenguisher',
        'tracker_installation_date',
        'inspection_date',
        'next_inspection_date',
        'pso_card',
        'akpl',
        'registration_file',
        'fitness_date',
        'next_fitness_date',
        'fitness_file',
        'insurance_policy_no',
        'insurance_company_id',
        'insurance_date',
        'insurance_expiry_date',
        'insurance_file',
        'route_permit_date',
        'route_permit_expiry_date',
        'route_permit_file',
        'tax_date',
        'next_tax_date',
        'tax_file',
        'shift_timing_id',
        'primary_driver_id',
        'current_driver_id',
        'is_new_vehicle',
    ];

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id');
    }

    public function station()
    {
        return $this->belongsTo(Station::class, 'station_id');
    }

    public function ibcCenter()
    {
        return $this->belongsTo(IbcCenter::class, 'ibc_center_id');
    }

    public function drivers()
    {
        return $this->hasMany(Driver::class);
    }

    public function primaryDriver()
    {
        return $this->belongsTo(Driver::class, 'primary_driver_id');
    }

    public function currentDriver()
    {
        return $this->belongsTo(Driver::class, 'current_driver_id');
    }

    public function poolDrivers()
    {
        return $this->belongsToMany(Driver::class, 'vehicle_pool_drivers')
            ->withTimestamps();
    }

    public function fabricationVendor()
    {
        return $this->belongsTo(Vendor::class, 'fabrication_vendor_id');
    }

    public function shiftHours()
    {
        return $this->belongsTo(ShiftHours::class, 'shift_hour_id');
    }





    public static function GetSerialNumber()
    {
        $serial_no = DB::table('vehicles');
        $serial_no = $serial_no->select(DB::RAW("LPAD(IFNULL( MAX( id ) +1, 1 ),9,'0') AS id"));
        $serial_no = $serial_no->first()->id;

        return $serial_no;
    }

    public function shiftTiming()
    {
        return $this->belongsTo(ShiftTimings::class, 'shift_timing_id');
    }

    public function maintenanceSchedules()
    {
        return $this->hasMany(VehicleMaintenanceSchedule::class);
    }

}
