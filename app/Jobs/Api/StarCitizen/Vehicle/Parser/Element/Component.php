<?php declare(strict_types=1);

namespace App\Jobs\Api\StarCitizen\Vehicle\Parser\Element;

use App\Jobs\Api\StarCitizen\Vehicle\Parser\Element\AbstractBaseElement as BaseElement;
use App\Models\Api\StarCitizen\Vehicle\Component\Component as ComponentModel;
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
    public function getComponentIDs(): array
    {
        app('Log')::debug('Getting Component IDs');

        if (!$this->rawData->has(self::COMPONENTS)) {
            return [];
        }

        $componentData = collect($this->rawData->get(self::COMPONENTS));

        $ids = $componentData->flatten()
            ->flatMap(
                function ($bla) {
                    return collect($bla)->flatten();
                }
            )->map(
                function ($component) {
                    $component = $this->getComponent(new Collection($component));

                    if ($component !== null) {
                        return $component->id;
                    }

                    return null;
                }
            )->filter(
                function ($component) {
                    return $component !== null;
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

        if ($data->get(self::NAME) === null) {
            return null;
        }

        /** @var ComponentModel $component */
        return ComponentModel::query()->updateOrCreate(
            [
                'type' => $this->normalizeString(self::TYPE),
                'name' => $this->normalizeString(self::NAME),
                'component_class' => $this->normalizeString(self::COMPONENT_CLASS),
            ],
            [
                'mounts' => (int) $data->get(self::MOUNTS),
                'component_size' => $this->normalizeString(self::COMPONENT_SIZE),
                'category' => $this->normalizeString(self::CATEGORY),
                'size' => $data->get(self::SIZE),
                'details' => $this->normalizeString($data->get(self::DETAILS)),
                'quantity' => (int) $data->get(self::QUANTITY),
                'manufacturer' => $this->normalizeString($data->get(self::MANUFACTURER)),
            ]
        );
    }
}
