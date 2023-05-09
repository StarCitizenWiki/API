<?php

declare(strict_types=1);

namespace App\Models\SC;

use App\Events\ModelUpdating;
use App\Models\SC\Item\Item;
use App\Models\SC\Vehicle\Vehicle;
use App\Models\StarCitizen\Manufacturer\ManufacturerTranslation;
use App\Models\System\Translation\AbstractHasTranslations as HasTranslations;
use App\Traits\HasModelChangelogTrait as ModelChangelog;
use App\Traits\HasVehicleRelationsTrait as VehicleRelations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
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

    /**
     * @return Collection
     */
    public function items()
    {
        return Item::query()
            ->whereRelation('manufacturer', 'name', $this->name)
            ->where(function(Builder $query) {
                $query->where('name', 'NOT LIKE', '%PLACEHOLDER%')
                    ->where('type', 'NOT LIKE', '%Seat%')
                    ->where('type', 'NOT LIKE', '%Access%')
                    ->where('type', 'NOT LIKE', 'NOITEM%')
                    ->where('type', '!=', 'Armor')
                ;
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
