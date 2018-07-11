<?php declare(strict_types = 1);
/**
 * Created by IntelliJ IDEA.
 * User: Sebastian
 * Date: 04.02.2017
 * Time: 21:29
 */

namespace App\Transformers\StarCitizen\Stats;

use App\Models\StarCitizen\Stat;
use App\Transformers\AbstractBaseTransformer;

/**
 * Class FundsTransformer
 */
class FundsTransformer extends AbstractBaseTransformer
{
    /**
     * Transforms Stats to only return the funds
     *
     * @param \App\Models\StarCitizen\Stat $stat Data
     *
     * @return array
     */
    public function transform(Stat $stat)
    {

        $deDE = number_format(
            (float) $stat->funds,
            0,
            ',',
            '.'
        );

        $enUS = number_format(
            (float) $stat->funds,
            0,
            '.',
            ','
        );

        $frFR = number_format(
            (float) $stat->funds,
            0,
            ',',
            ' '
        );

        return [
            'funds' => (string) $stat->funds,
            'formatted' => [
                'en_US' => $enUS,
                'de_DE' => $deDE,
                'fr_FR' => $frFR,
            ],
        ];
    }
}
