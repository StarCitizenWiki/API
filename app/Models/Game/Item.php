<?php

declare(strict_types=1);

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Item extends Model
{
    use HasFactory;
    use HasTranslations;
    use HasVersionedData;

    public array $translatable = ['translation'];

    protected $table = 'game_items';

    protected $fillable = [
        'uuid',
        'slug',
        'translation',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function data(): HasMany
    {
        return $this->hasMany(ItemData::class);
    }
}
