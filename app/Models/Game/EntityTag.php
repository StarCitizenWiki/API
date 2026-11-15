<?php

declare(strict_types=1);

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntityTag extends Model
{
    use HasFactory;

    protected $table = 'game_entity_tags';

    protected $fillable = [
        'uuid',
        'name',
        'parent_uuid',
    ];
}
