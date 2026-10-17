<?php

declare(strict_types=1);

namespace App\Services\Parser\ShipMatrix;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Class TranslatableLookup
 */
class TranslatableLookup extends AbstractBaseElement
{
    /**
     * @param  class-string<Model>  $modelClass
     */
    public function __construct(
        Collection $rawData,
        private readonly string $modelClass,
        private readonly string $arrayKey,
    ) {
        parent::__construct($rawData);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function get(): Model
    {
        $value = $this->rawData->get($this->arrayKey);

        if ($value === null) {
            app('Log')::debug($this->arrayKey.' not set in Matrix, returning default (undefined)');

            return $this->modelClass::query()->findOrFail(1);
        }

        try {
            return $this->modelClass::query()
                ->where('translation->'.config('language.english'), $value)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            app('Log')::debug($this->arrayKey.' not found in DB');

            return $this->create();
        }
    }

    private function create(): Model
    {
        $translation = $this->rawData->get($this->arrayKey);

        /** @var Model $model */
        $model = $this->modelClass::query()->firstOrCreate([
            'slug' => Str::slug($translation),
        ]);

        if ($translation !== '') {
            $model->setTranslation('translation', config('language.english'), $translation);
            $model->save();
        }

        app('Log')::debug(class_basename($this->modelClass).' created', ['id' => $model->id]);

        return $model;
    }
}
