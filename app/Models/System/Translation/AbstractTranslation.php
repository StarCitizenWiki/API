<?php

declare(strict_types=1);

namespace App\Models\System\Translation;

use App\Events\ModelUpdating;
use App\Models\System\Language;
use App\Traits\HasModelChangelogTrait as ModelChangelog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Base Translation Class which holds Language Query Scopes
 */
abstract class AbstractTranslation extends Model
{
    use ModelChangelog;
    use HasFactory;

    private const ATTR_LOCALE_CODE = '.locale_code';

    protected $dispatchesEvents = [
        'updating' => ModelUpdating::class,
        'created' => ModelUpdating::class,
        'deleting' => ModelUpdating::class,
    ];

    /**
     * Language Relation
     *
     * @return BelongsTo
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * English Translations
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeEnglish(Builder $query): Builder
    {
        return $query->where($this->getTable() . self::ATTR_LOCALE_CODE, config('language.english'));
    }

    /**
     * German Translations
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeGerman(Builder $query): Builder
    {
        return $query->where($this->getTable() . self::ATTR_LOCALE_CODE, config('language.german'));
    }
}
