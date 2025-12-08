<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Warehouse extends Model
{
    protected $table = 'warehouses';

    protected $fillable = [
        'serial_no',
        'name',
        'type',
        'manager_id',
        'station_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function station()
    {
        return $this->belongsTo(Station::class, 'station_id');
    }

    // Auto-generate serial_no like WH-000000001, WH-000000002, etc.
    public static function generateSerialNo(): string
    {
        $prefix = 'WH-';
        $nextId = DB::table('warehouses')->max('id') + 1;

        return $prefix . str_pad($nextId, 9, '0', STR_PAD_LEFT);
        // → WH-000000001, WH-000000002, ...
    }
}
