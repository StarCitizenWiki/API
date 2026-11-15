<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\System\Language;
use App\Services\Translation\TranslationService;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait TranslatesModelBatch
{
    use HandlesTranslationExceptions;

    protected function translateChunk(Builder $query, Closure $translate, Closure $context): void
    {
        [, $translationLocale] = TranslationService::resolveLocale();

        $query->chunk(25, function (Collection $models) use ($translate, $context, $translationLocale) {
            foreach ($models as $model) {
                if (! $this->translateModel($model, $translationLocale, $translate, $context)) {
                    return false;
                }
            }

            return true;
        });
    }

    protected function translateModel(Model $model, string $translationLocale, Closure $translate, Closure $context, ?Closure $skip = null): bool
    {
        $english = $model->getTranslation('translation', Language::ENGLISH, false);
        $existingTranslation = $model->getTranslation('translation', $translationLocale, false);

        if ($english === null || $english === '') {
            return true;
        }

        if ($skip !== null ? $skip($english, $existingTranslation) : ($existingTranslation !== null && $existingTranslation !== '')) {
            return true;
        }

        $translation = $this->translateOrAbort(
            fn (): string => $translate($model, $english),
            $context($model),
        );

        if ($translation === false) {
            return false;
        }

        if ($translation !== null) {
            $model->setTranslation('translation', $translationLocale, $translation);
            $model->save();
        }

        return true;
    }
}
