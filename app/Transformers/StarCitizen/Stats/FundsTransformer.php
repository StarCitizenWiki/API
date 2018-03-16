<?php declare(strict_types = 1);
/**
 * Created by IntelliJ IDEA.
 * User: Sebastian
 * Date: 04.02.2017
 * Time: 21:29
 */

namespace App\Transformers\StarCitizen\Stats;

use App\Models\StarCitizen\Stats;
use App\Transformers\AbstractBaseTransformer;

/**
 * Class FundsTransformer
 */
class FundsTransformer extends AbstractBaseTransformer
{
    /**
     * Transforms Stats to only return the funds
     *
     * @param \App\Models\StarCitizen\Stats $stats Data
     *
     * @return array
     */
    public function transform(Stats $stats)
    {

        setlocale(LC_MONETARY, 'de_DE');
        $formattedDE = number_format($stats->funds)

        return [
            'funds' => (string) $stats->funds,
            'formatted' => [
                'USD' => '',
            ]
        ];
    }
}
