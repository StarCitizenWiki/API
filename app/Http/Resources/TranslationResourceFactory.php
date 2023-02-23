<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\System\Translation\AbstractHasTranslations;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\MissingValue;
use Illuminate\Support\Collection;

class TranslationResourceFactory
{
    /**
     * @param Request $request
     * @param AbstractBaseResource|AbstractHasTranslations|MissingValue $model
     * @return TranslationCollection|mixed
     */
    public static function getTranslationResource(Request $request, $model): mixed
    {
        if ($model instanceof MissingValue) {
            return $model;
        }

        if ($model instanceof Collection) {
            $collection = new TranslationCollection($model);
        } elseif (is_callable([$model, 'translations'])) {
            $collection = new TranslationCollection($model->translations);
        } else {
            return new MissingValue();
        }

        $transformed = $collection->toArray($request);

        if ($request->has('locale')) {
            return $transformed[$request->get('locale')] ?? $transformed['en_EN'];
        }

        return $collection;
    }
}