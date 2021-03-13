<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenItems;

use App\Services\Mapper\ShipCSVToSMWSubobjectMapper;
use Illuminate\Support\Collection;

class ShipCSVParser extends AbstractCSVParser
{
    private $handle;
    private array $handleable = [
        'Raumschiff',
        'Fahrzeug',
    ];
    private Collection $buyables;

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

    private Collection $rentables;

    private $idxDayMap = [
        1 => 1,
        2 => 3,
        3 => 7,
        4 => 30,
    ];

    public function __construct(string $file)
    {
        $this->handle = fopen($file, 'r');
        $this->buyables = collect();
        $this->rentables = collect();
    }

    public function __toString()
    {
        $parser = new ShipCSVParser();
        $parser->parse();

        $wikitext = $parser->getBuyables()->map(function ($ship) {
            return ShipCSVToSMWSubobjectMapper::mapShip($ship);
        })->implode("\n\n");

        $wikitext = $wikitext . $parser->getRentables()->map(function ($ship) {
                return ShipCSVToSMWSubobjectMapper::mapShip($ship);
            })->implode("\n\n");

        return $wikitext;
    }

    public function parse(): void
    {
        while (($data = fgetcsv($this->handle, 1000, ",")) !== false) {
            if (count($data) !== 28 || !in_array($data[self::IDX_CATEGORY], $this->handleable)) {
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
    }

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

    private function addRentable(array $data): void
    {
        $prices = [];
        for ($i = 0; $i < 4; $i++) {
            $fmt = ($i === 0) ? '%d Tag' : '%d Tage';
            $prices[sprintf($fmt, $this->idxDayMap[($i + 1)])] = sprintf('%s aUEC', $data[self::IDX_RENTAL_PRICE_START + $i]);
        }

        $this->rentables->push(
            $this->getBase($data) + [
                'Händler' => $data[self::IDX_RENTAL_MERCHANT],
                'Landezone' => $data[self::IDX_RENTAL_PLACE],
            ] + $prices
        );
    }

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

    private function getBase(array $data): array
    {
        return [
            'Spielversion' => $data[self::IDX_VERSION],
            'Name' => $data[self::IDX_NAME],
            'Typ' => $data[self::IDX_CATEGORY],
        ];
    }
}
