<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Vendor extends Model
{
    protected $table = 'vendor';

    protected $fillable = [
    	'name',
        'phone',
        'vendor_type_id',
        'city_id',
        'description',
    ];

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function vendorType()
    {
        return $this->belongsTo(VendorType::class, 'vendor_type_id');
    }

    public static function GetSerialNumber()
    {
        $serial_no = DB::table('vendor');
        $serial_no = $serial_no->select(DB::RAW("LPAD(IFNULL( MAX( id ) +1, 1 ),9,'0') AS id"));
        $serial_no = $serial_no->first()->id;

        return $serial_no;
    }
}
