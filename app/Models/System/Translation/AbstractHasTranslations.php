<?php declare(strict_types=1);

namespace App\Models\System\Translation;

use App\Models\System\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Base Translation Class which holds Language Query Scopes.
 */
abstract class AbstractHasTranslations extends Model
{
    private const LOCALE_CODE = 'locale_code';

    /**
     * @return HasMany
     */
    abstract public function translations();

    /**
     * @return Model|null
     */
    public function english(): ?Model
    {
        return $this->translations->keyBy(self::LOCALE_CODE)->get('en_EN', null);
    }

    /**
     * @return Model|null
     */
    public function german(): ?Model
    {
        return $this->translations->keyBy(self::LOCALE_CODE)->get('de_DE', null);
    }

    public function localeCode(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'locale_code', 'locale_code');
    }

    /**
     * Translations Right Joined with Languages.
     *
     * @return Collection
     */
    public function translationsCollection(): Collection
    {
        /** @var Collection $tanslations */
        $translations = $this->translations;

        $languages = Language::all();

        return $languages->merge($translations)->keyBy(self::LOCALE_CODE);
    }
}
