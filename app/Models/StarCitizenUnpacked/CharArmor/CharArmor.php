<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\CharArmor;

use App\Models\StarCitizenUnpacked\CommodityItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CharArmor extends CommodityItem
{
    use HasFactory;

    /**
     * Keywords used as splits for Armor Names
     * Essentially removes the color from the item name
     *
     * @var string[]
     */
    public static $splits = [
        'Arms',
        'Helmet',
        'Legs',
        'Core',
        'Undersuit',
    ];

    protected $table = 'star_citizen_unpacked_char_armor';

    protected $fillable = [
        'uuid',
        'armor_type',
        'carrying_capacity',
        'damage_reduction',
        'temp_resistance_min',
        'temp_resistance_max',
        'version',
    ];

    protected $casts = [
        'temp_resistance_min' => 'double',
        'temp_resistance_max' => 'double',
    ];

    public function getRouteKey()
    {
        return $this->uuid;
    }

    public function attachments(): BelongsToMany
    {
        return $this->belongsToMany(
            CharArmorAttachment::class,
            'star_citizen_unpacked_char_armor_attachment',
        );
    }

    public function resistances(): HasMany
    {
        return $this->hasMany(
            CharArmorResistance::class,
            'char_armor_id',
        );
    }

    /**
     * Tries to find the base model of this item
     * Removes the color string from the name and searches all armors
     *
     * @return CharArmor|null
     */
    public function getBaseModelAttribute(): ?CharArmor
    {
        foreach (self::$splits as $split) {
            if (!Str::contains($this->item->name, $split)) {
                continue;
            }

            $splitted = array_filter(explode($split, $this->item->name));

            // This is the base version
            if (count($splitted) !== 2) {
                return null;
            }

            array_pop($splitted);
            $splitted[] = $split;

            $baseName = implode(' ', $splitted);
            $baseName = trim(preg_replace('/\s+/', ' ', $baseName));

            return CharArmor::query()
                ->whereHas('item', function (Builder $query) use ($splitted, $baseName) {
                    $query->whereIn('name', [
                        trim($splitted[0]),
                        $baseName,
                        sprintf('%s Base', $baseName),
                    ]);
                })
                ->first();
        }

        return null;
    }
}
