<?php

namespace App\Models;

use App\Models\User;
use App\Models\Issue;
use App\Models\Vehicle;
use App\Models\JobCartItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobCart extends Model
{
    use HasFactory;

    protected $fillable = [
        'serial_no',
        'vehicle_id',
        'issue_id',
        'status',
        'type',
        'remarks',
        'created_by',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->serial_no = self::generateSerialNo();
        });
    }

    private static function generateSerialNo()
    {
        $last = self::latest('id')->value('serial_no');

        return $last
            ? 'JC-' . str_pad((int) filter_var($last, FILTER_SANITIZE_NUMBER_INT) + 1, 5, '0', STR_PAD_LEFT)
            : 'JC-00001';
    }

    public function items()
    {
        return $this->hasMany(JobCartItem::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function issue()
    {
        return $this->belongsTo(Issue::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
