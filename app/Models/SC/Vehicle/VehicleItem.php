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
                    'Arm',
                    'Battery',
                    'BombLauncher',
                    'Cooler',
                    'EMP',
                    'ExternalFuelTank',
                    'FlightController',
                    'FuelIntake',
                    'FuelTank',
                    'MainThruster',
                    'ManneuverThruster',
                    'MiningArm',
                    'Missile',
                    'MissileLauncher',
                    'Mount',
                    'Paints',
                    'PowerPlant',
                    'QuantumDrive',
                    'QuantumFuelTank',
                    'QuantumInterdictionGenerator',
                    'Radar',
                    'SalvageModifier',
                    'SelfDestruct',
                    'Shield',
                    'ToolArm',
                    'TowingBeam',
                    'TractorBeam',
                    'Turret',
                    'Turret',
                    'TurretBase',
                    'UtilityTurret',
                    'WeaponDefensive',
                    'WeaponGun',
                    'WeaponMount',
                    'WheeledController',
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
