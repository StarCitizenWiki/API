<?php

declare(strict_types=1);

namespace App\Services\Gdoc\Parser;

use App\Services\Mapper\SmwSubObjectMapper;
use Illuminate\Support\Collection;
use JsonException;

/**
 * Parses 'Preise' lines of category 'Raumschiff' or 'Fahrzeug'
 */
final class VehiclePriceParser extends PriceTableCSVParser
{
    /**
     * All buyable ships
     *
     * @var Collection
     */
    private Collection $buyables;

    /**
     * All rentable ships
     *
     * @var Collection
     */
    private Collection $rentables;

    /**
     * Map: Index -> rentable Days
     *
     * @var array|int[]
     */
    private array $idxDayMap = [
        1 => 1,
        2 => 3,
        3 => 7,
        4 => 30,
    ];

    /**
     * @inheritdoc
     */
    protected array $handleable = [
        'Raumschiff',
        'Fahrzeug',
    ];

    /**
     * @param string $file Path to csv file
     */
    public function __construct(string $file)
    {
        $this->fileHandle = fopen($file, 'rb');
        $this->buyables = collect();
        $this->rentables = collect();
    }

    /**
     * @return Collection
     */
    public function getBuyables(): Collection
    {
        return $this->buyables;
    }

    /**
     * @return Collection
     */
    public function getRentables(): Collection
    {
        return $this->rentables;
    }

    /**
     * @inheritdoc
     */
    public function parse(): void
    {
        if ($this->parsed) {
            return;
        }

        while (($data = fgetcsv($this->fileHandle, 1000)) !== false) {
            if (count($data) !== 28 || !in_array($data[self::IDX_CATEGORY], $this->handleable, true)) {
                continue;
            }

            if ($data[self::IDX_PRICE_MIN] !== "") {
                $this->addBuyable($data);
            } elseif ($data[self::IDX_RENTAL_PRICE_START] !== "") {
                $this->addRentable($data);
            } elseif ($data[self::IDX_REC_PRICE] !== "") {
                $this->addRecRentable($data);
            }
        }

        $this->buyables = $this->buyables->sortBy('Name');
        $this->rentables = $this->rentables->sortBy('Name');

        $this->parsed = true;
    }

    /**
     * Add a buyble vehicle to the collections
     *
     * @param array $data
     */
    private function addBuyable(array $data): void
    {
        $this->buyables->push(
            $this->getBase($data) + [
                'Händler' => $data[self::IDX_MERCHANT],
                'Landezone' => $data[self::IDX_PLACE],
                'Kaufpreis' => sprintf('%s aUEC', $data[self::IDX_PRICE_MIN]),
            ]
        );
    }

    /**
     * Add a rentable vehicle to the collection
     *
     * @param array $data
     */
    private function addRentable(array $data): void
    {
        $prices = [];
        for ($i = 0; $i < 4; $i++) {
            $fmt = ($i === 0) ? '%d Tag' : '%d Tage';
            $days = $this->idxDayMap[($i + 1)];
            $prices[sprintf($fmt, $days)] = sprintf('%s aUEC', $data[self::IDX_RENTAL_PRICE_START + $i]);
        }

        $this->rentables->push(
            array_merge(
                $this->getBase($data),
                [
                    'Händler' => $data[self::IDX_RENTAL_MERCHANT],
                    'Landezone' => $data[self::IDX_RENTAL_PLACE],
                ],
                $prices
            )
        );
    }

    /**
     * Adds a arena commander rentable ship to the collection
     * Requires that REC rentals come _after_ normal rentables
     *
     * @param array $data
     */
    private function addRecRentable(array $data): void
    {
        $name = $data[self::IDX_NAME];

        $this->rentables
            ->transform(function ($item) use ($data, $name) {
                if ($item['Name'] === $name) {
                    $item['REC'] = sprintf('%s REC', $data[self::IDX_REC_PRICE]);
                }

                return $item;
            });
    }

    /**
     * Base data for each object
     *
     * @param array $data
     * @return array
     */
    private function getBase(array $data): array
    {
        return [
            'Spielversion' => $data[self::IDX_VERSION],
            'Name' => $data[self::IDX_NAME],
            'Typ' => $data[self::IDX_CATEGORY],
        ];
    }

    /**
     * Returns an array containing the length of the longest entry for each parsed row
     *
     * @param $data
     * @return array
     */
    private static function mapLengths($data): array
    {
        $lengthMap = [];

        collect($data)->each(function ($item) use (&$lengthMap) {
            foreach ($item as $key => $v) {
                $len = strlen(trim($v));

                if (!isset($lengthMap[$key]) || $lengthMap[$key] < $len) {
                    $lengthMap[$key] = $len;
                }
            }
        });


        return $lengthMap;
    }

    /**
     * Wikitext SubObject representation
     *
     * @return string
     */
    public function toSubObjects(): string
    {
        if (!$this->parsed) {
            $this->parse();
        }

        $mapBuyables = self::mapLengths($this->getBuyables());
        $mapRentables = self::mapLengths($this->getRentables());

        $objectsBuyables = $this->getBuyables()
            ->map(function ($ship) use ($mapBuyables) {
                return SmwSubObjectMapper::mapInline($ship, $mapBuyables);
            })
            ->implode("\n\n");

        $objectsRentables = $this->getRentables()
            ->map(function ($ship) use ($mapRentables) {
                return SmwSubObjectMapper::mapInline($ship, $mapRentables);
            })
            ->implode("\n\n");

        return sprintf("%s\n\n%s", $objectsBuyables, $objectsRentables);
    }

    /**
     * @return string
     * @throws JsonException
     */
    public function __toString(): string
    {
        if (!$this->parsed) {
            $this->parse();
        }

        return json_encode([
            'buyable' => $this->getBuyables()->toArray(),
            'rentable' => $this->getRentables()->toArray(),
        ], JSON_THROW_ON_ERROR);
    }
}
