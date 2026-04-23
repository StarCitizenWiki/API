<?php

declare(strict_types=1);

namespace App\Models\Game\Mission;

use App\Models\Game\HasVersionedData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mission extends Model
{
    use HasFactory;
    use HasVersionedData;

    protected $table = 'game_missions';

    protected $fillable = [
        'uuid',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function data(): HasMany
    {
        return $this->hasMany(MissionData::class);
    }
}
