<?php

declare(strict_types=1);

namespace App\Models\Game\Mission;

use App\Models\Game\HasVersionedData;
use App\Models\Game\ResolvesUuidOrSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Mission extends Model
{
    use HasFactory;
    use HasTranslations;
    use HasVersionedData;
    use ResolvesUuidOrSlug;

    public array $translatable = ['translation'];

    protected $table = 'game_missions';

    protected $fillable = [
        'uuid',
        'slug',
        'translation',
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
