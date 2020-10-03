<?php

declare(strict_types=1);

namespace App\Jobs\Api\StarCitizen\Starmap\Parser\Element;

use App\Models\Api\StarCitizen\Starmap\Affiliation as AffiliationModel;
use Illuminate\Support\Collection;

/**
 * Class ParseAffiliation
 */
class Affiliation
{
    private Collection $rawData;

    /**
     * Affiliation constructor.
     *
     * @param array|Collection $rawData
     */
    public function __construct($rawData)
    {
        $this->rawData = new Collection($rawData);
    }

    /**
     * @return AffiliationModel
     */
    public function getAffiliation(): AffiliationModel
    {
        $data = $this->getData();

        return AffiliationModel::updateOrCreate(
            [
                'cig_id' => $data->pull('cig_id'),
            ],
            $data->toArray()
        );
    }

    public function getData(): Collection
    {
        return new Collection(
            [
                'cig_id' => $this->rawData->get('id'),

                'name' => $this->rawData->get('name'),
                'code' => $this->rawData->get('code'),
                'color' => $this->rawData->get('color'),
                'membership_id' => $this->rawData->get('membership.id', null),
            ]
        );
    }
}
