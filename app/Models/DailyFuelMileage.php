<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DailyFuelMileage extends Model
{
    protected $fillable = [
        'vehicle_no',
        'destination',
        'date',
        'current_reading',
        'previous_reading',
        'difference',
        'fuel_taken',
        'consumption',
        'fuel_station',
        'driver_name',
        'location',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function destination()
    {
        return $this->belongsTo(Destination::class, 'destination');
    }

    public function fuelStation()
    {
        return $this->belongsTo(FuelStation::class, 'fuel_station');
    }

    public static function GetSerialNumber()
    {
        $serial_no = DB::table('daily_fuel_mileages');
        $serial_no = $serial_no->select(DB::RAW("LPAD(IFNULL( MAX( id ) +1, 1 ),9,'0') AS id"));
        $serial_no = $serial_no->first()->id;

        return $serial_no;
    }
}
