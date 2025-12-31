<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InventoryWarehouse extends Model
{
    protected $fillable = [
        'warehouse_name',
        'location',
        'country_id',
        'city_id',
        'contact',
        'warehouse_manager',
        'warehouse_type',
        'operating_hour',
        'handling_equipment',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function warehouseManager()
    {
        return $this->belongsTo(User::class, 'warehouse_manager');
    }

    public function warehouseType()
    {
        return $this->belongsTo(WarehouseType::class, 'warehouse_type');
    }

    public function operatingHours()
    {
        return $this->belongsTo(OperatingHours::class, 'operating_hour');
    }

    public function handlingEquipment()
    {
        return $this->belongsTo(HandlingEquipment::class, 'handling_equipment');
    }

    public static function GetSerialNumber()
    {
        $serial_no = DB::table('inventory_warehouses');
        $serial_no = $serial_no->select(DB::RAW("LPAD(IFNULL( MAX( id ) +1, 1 ),9,'0') AS id"));
        $serial_no = $serial_no->first()->id;

        return $serial_no;
    }
}
