<?php

declare(strict_types=1);

namespace App\Models\SC;

use App\Models\SC\Item\Item;
use App\Models\SC\Vehicle\Vehicle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Manufacturer Model
 */
class Manufacturer extends Model
{
    protected $table = 'sc_manufacturers';

    protected $fillable = [
        'uuid',
        'name',
        'code',
    ];

    /**
     * {@inheritdoc}
     */
    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function itemsCount()
    {
        return Item::query()
            ->whereRelation('manufacturer', 'name', $this->name)
            ->where(function (Builder $query) {
                $query->where('name', 'NOT LIKE', '%PLACEHOLDER%')
                    ->where('type', 'NOT LIKE', '%Seat%')
                    ->where('type', 'NOT LIKE', '%Access%')
                    ->where('type', 'NOT LIKE', 'NOITEM%')
                    ->where('type', '!=', 'Armor');
            })
            ->count();
    }

    /**
     * @return int Ships
     */
    public function shipsCount(): int
    {
        return Vehicle::query()
            ->where('is_ship', 1)
            ->whereRelation('item.manufacturer', 'name', $this->name)->count();
    }

    /**
     * @return int Ships
     */
    public function groundVehiclesCount(): int
    {
        return Vehicle::query()
            ->where('is_ship', 0)
            ->whereRelation('item.manufacturer', 'name', $this->name)->count();
    }

    /**
     * @return Collection
     */
    public function items(): Collection
    {
        return Item::query()
            ->whereRelation('manufacturer', 'name', $this->name)
            ->where(function (Builder $query) {
                $query->where('name', 'NOT LIKE', '%PLACEHOLDER%')
                    ->where('type', 'NOT LIKE', '%Seat%')
                    ->where('type', 'NOT LIKE', '%Access%')
                    ->where('type', 'NOT LIKE', 'NOITEM%')
                    ->where('type', '!=', 'Armor');
//                    ->where(function(Builder $query) {
//                        $query->where('type', 'LIKE', 'Char%')
//                            ->orWhere('type', 'LIKE', 'Weapon%');
//                    });
            })
            ->get();
    }

    /**
     * @return Collection Ships
     */
    public function ships(): Collection
    {
        return Vehicle::query()
            ->where('is_ship', 1)
            ->whereRelation('item.manufacturer', 'name', $this->name)->get();
    }

    /**
     * @return Collection Ships
     */
    public function groundVehicles(): Collection
    {
        return Vehicle::query()
            ->where('is_ship', 0)
            ->whereRelation('item.manufacturer', 'name', $this->name)->get();
    }
}
