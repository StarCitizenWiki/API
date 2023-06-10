<?php

declare(strict_types=1);

namespace App\Models\SC\Vehicle;

use App\Models\SC\Item\Item;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class VehicleItem extends Item
{
    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope(
            'type',
            static function (Builder $builder) {
                $builder->whereIn('type', [
                    'Battery',
                    'Cooler',
                    'EMP',
                    'ExternalFuelTank',
                    'FuelIntake',
                    'FuelTank',
                    'MainThruster',
                    'ManneuverThruster',
                    'Missile',
                    'Paints',
                    'PowerPlant',
                    'QuantumDrive',
                    'QuantumFuelTank',
                    'QuantumInterdictionGenerator',
                    'Radar',
                    'SelfDestruct',
                    'Shield',
                    'WeaponDefensive',
                    'WeaponGun',
                    'FlightController',
                    'Turret',
                    'Mount',
                    'Arm',
                    'WheeledController',
                    'BombLauncher',
                    'MiningArm',
                    'MissileLauncher',
                    'ToolArm',
                    'Turret',
                    'TurretBase',
                    'UtilityTurret',
                    'WeaponMount',
                ]);
            }
        );
    }

    public function ports(): HasMany
    {
        return parent::ports()
            ->where('name', 'NOT LIKE', '%access%')
            ->where('name', 'NOT LIKE', '%hud%');
    }

    public function getGradeAttribute()
    {
        return $this->getDescriptionDatum('Grade');
    }

    public function getClassAttribute()
    {
        return $this->getDescriptionDatum('Class');
    }

    public function getItemTypeAttribute()
    {
        return implode(' ', Str::ucsplit($this->type));
    }
}
