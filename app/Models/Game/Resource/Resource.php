<?php

declare(strict_types=1);

namespace App\Models\Game\Resource;

use App\Models\Game\HasVersionedData;
use Database\Factories\Game\Resource\ResourceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Resource extends Model
{
    /** @use HasFactory<ResourceFactory> */
    use HasFactory;

    use HasVersionedData;

    protected $table = 'game_resources';

    protected $fillable = [
        'uuid',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function data(): HasMany
    {
        return $this->hasMany(ResourceData::class);
    }
}
