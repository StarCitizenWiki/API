<?php

declare(strict_types=1);

namespace App\Services\Parser\ShipMatrix;

use App\Models\StarCitizen\Vehicle\Component\Component as ComponentModel;
use App\Services\Parser\ShipMatrix\AbstractBaseElement as BaseElement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Manufacturer Parser
 */
class Component extends BaseElement
{
    private const COMPONENTS = 'compiled';

    private const TYPE = 'type';
    private const NAME = 'name';
    private const MOUNTS = 'mounts';
    private const COMPONENT_SIZE = 'component_size';
    private const CATEGORY = 'category';
    private const SIZE = 'size';
    private const DETAILS = 'details';
    private const QUANTITY = 'quantity';
    private const MANUFACTURER = 'manufacturer';
    private const COMPONENT_CLASS = 'component_class';

    /**
     * @return int[]
     */
    public function getComponents(): array
    {
        app('Log')::debug('Getting Component IDs');

        if (!$this->rawData->has(self::COMPONENTS)) {
            return [];
        }

        $componentData = collect($this->rawData->get(self::COMPONENTS));

        $ids = $componentData
            ->flatMap(
                function ($componentGroup) {
                    return collect($componentGroup);
                }
            )
            ->flatMap(
                function ($componentGroup) {
                    return collect($componentGroup);
                }
            )->map(
                function ($component) {
                    $component = $this->getComponent(new Collection($component));

                    return $component ?? null;
                }
            )->filter(
                function ($component) {
                    return $component !== null;
                }
            )->map(
                function ($component) {
                    $data = [
                        'component' => $component,
                        'data' => $component->pivotData,
                    ];

                    unset($component->pivotData);

                    return $data;
                }
            );

        return $ids->toArray();
    }

    /**
     * @param Collection $data
     *
     * @return Model|null
     */
    public function getComponent(Collection $data): ?Model
    {
        app('Log')::debug('Getting Component');

        /** @var ComponentModel $component */
        $component = ComponentModel::query()->updateOrCreate(
            [
                'type' => $this->normalizeString($data->get(self::TYPE)),
                'name' => $this->normalizeString($data->get(self::NAME, '')),
                'component_class' => $this->normalizeString($data->get(self::COMPONENT_CLASS)),
                'component_size' => $this->normalizeString($data->get(self::COMPONENT_SIZE)),
            ],
            [
                'manufacturer' => $this->normalizeString($data->get(self::MANUFACTURER)),
                'category' => $this->normalizeString($data->get(self::CATEGORY)),
            ]
        );

        $component->pivotData = [
            'mounts' => (int)$data->get(self::MOUNTS),
            'size' => $data->get(self::SIZE),
            'details' => $this->normalizeString($data->get(self::DETAILS)),
            'quantity' => (int)$data->get(self::QUANTITY),
        ];

        return $component;
    }
}
