<?php

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Manufacturer extends Model
{
    protected $table = 'game_manufacturers';

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
                $query->where('type', 'NOT LIKE', '%Seat%')
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
            ->whereRelation('item.manufacturer', 'name', $this->name)
            ->count();
    }

    /**
     * @return int Ships
     */
    public function groundVehiclesCount(): int
    {
        return Vehicle::query()
            ->where('is_ship', 0)
            ->whereRelation('item.manufacturer', 'name', $this->name)
            ->count();
    }

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
