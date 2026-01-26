<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'title',
        'message',
        'type',
        'ref_id',
        'is_read',
    ];

    public const TYPE_MASTER_DATA = 'master_data';
    public const TYPE_MAINTENANCE = 'maintenance';
    public const TYPE_DRIVER = 'driver';
}
