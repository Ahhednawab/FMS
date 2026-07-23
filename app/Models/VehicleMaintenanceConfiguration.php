<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleMaintenanceConfiguration extends Model
{
    protected $table = 'vehicle_maintenance_configurations';

    /**
     * Predefined predictive maintenance items.
     *
     * These names are fixed by the system and are NOT editable by users.
     * The value is the database column that stores the interval (KM) for
     * that item. This map is the single source of truth for the predictive
     * maintenance feature — used by the configuration module, the maintenance
     * form (Work Done options when type = Predictive) and the alert engine.
     *
     * @var array<string, string>
     */
    public const ITEMS = [
        'Engine Oil'             => 'engine_oil',
        'Oil Filter'             => 'oil_filter',
        'Air Filter'             => 'air_filter',
        'Transmission Oil'       => 'transmission_oil',
        'Differential Oil'       => 'differential_oil',
        'Wheel Bearing Greasing' => 'wheel_bearing_greasing',
        'Fuel Filter (Small)'    => 'fuel_filter_small',
        'Fuel Filter (Large)'    => 'fuel_filter_large',
        'Power Steering Oil'     => 'power_steering_oil',
        'Brake Oil'              => 'brake_oil',
        'King Pin Greasing'      => 'king_pin_greasing',
    ];

    /**
     * Number of kilometers before the due mileage that an upcoming alert fires.
     */
    public const UPCOMING_ALERT_KM = 200;

    protected $fillable = [
        'make',
        'model',
        'engine_oil',
        'oil_filter',
        'air_filter',
        'transmission_oil',
        'differential_oil',
        'wheel_bearing_greasing',
        'fuel_filter_small',
        'fuel_filter_large',
        'power_steering_oil',
        'brake_oil',
        'king_pin_greasing',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * The predefined item display names.
     *
     * @return array<int, string>
     */
    public static function itemNames(): array
    {
        return array_keys(self::ITEMS);
    }

    /**
     * Whether the given Work Done name is a predefined predictive item.
     */
    public static function isPredictiveItem(?string $name): bool
    {
        return $name !== null && array_key_exists(trim($name), self::ITEMS);
    }

    /**
     * The DB column that stores the interval for a predefined item name.
     */
    public static function columnForItem(string $name): ?string
    {
        return self::ITEMS[trim($name)] ?? null;
    }

    /**
     * Configured interval (KM) for a predefined item on this configuration row.
     */
    public function intervalFor(string $item): ?int
    {
        $column = self::columnForItem($item);

        if ($column === null) {
            return null;
        }

        $value = $this->{$column};

        return $value !== null ? (int) $value : null;
    }

    /**
     * Find the active configuration row for a vehicle's make + model.
     */
    public static function forVehicle(?string $make, ?string $model): ?self
    {
        if ($make === null || $model === null || $make === '' || $model === '') {
            return null;
        }

        return self::where('is_active', 1)
            ->where('make', $make)
            ->where('model', $model)
            ->first();
    }
}
