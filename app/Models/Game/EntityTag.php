<?php

declare(strict_types=1);

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EntityTag extends Model
{
    use HasFactory;

    protected $table = 'game_entity_tags';

    protected $fillable = [
        'uuid',
        'name',
        'parent_uuid',
    ];

    public function itemData(): BelongsToMany
    {
        return $this->belongsToMany(
            ItemData::class,
            'game_item_data_entity_tag',
            'entity_tag_id',
            'item_data_id'
        );
    }
}
