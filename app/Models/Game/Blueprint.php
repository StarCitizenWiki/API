<?php

declare(strict_types=1);

namespace App\Models\Game;

use Database\Factories\Game\BlueprintFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Blueprint extends Model
{
    /** @use HasFactory<BlueprintFactory> */
    use HasFactory;

    use HasVersionedData;

    protected $table = 'game_blueprints';

    protected $fillable = [
        'uuid',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function data(): HasMany
    {
        return $this->hasMany(BlueprintData::class);
    }
}
