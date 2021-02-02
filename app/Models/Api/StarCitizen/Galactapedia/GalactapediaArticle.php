<?php

namespace App\Models\Api\StarCitizen\Galactapedia;

use App\Events\ModelUpdating;
use App\Models\System\Translation\AbstractHasTranslations;
use App\Traits\HasModelChangelogTrait as ModelChangelog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GalactapediaArticle extends AbstractHasTranslations
{
    use HasFactory;
    use ModelChangelog;

    protected $dispatchesEvents = [
        'updating' => ModelUpdating::class,
        'created' => ModelUpdating::class,
        'deleting' => ModelUpdating::class,
    ];

    protected $fillable = [
        'cig_id',
        'title',
        'slug',
    ];

    /**
     * @return HasMany
     */
    public function translations(): HasMany
    {
        return $this->hasMany(GalactapediaArticleTranslation::class);
    }
}
