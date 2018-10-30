<?php declare(strict_types = 1);
/**
 * User: Keonie
 * Date: 02.09.2018 20:25
 */

namespace App\Jobs\Api\StarCitizen\Starmap\Parser;

use App\Models\Api\StarCitizen\Starmap\Affiliation;

/**
 * Class ParseAffiliation
 */
class ParseAffiliation
{
    /**
     * @param array $affiliationData
     *
     * @return int
     */
    public static function getAffiliation(array $affiliationData): int
    {
        $affiliation = Affiliation::updateOrCreate(
            [
                'cig_id' => $affiliationData['id'],
            ],
            [
                'name' => $affiliationData['name'],
                'code' => $affiliationData['code'],
                'color' => $affiliationData['color'],
                'membership_id' => $affiliationData['membership.id'] ?? null,
            ]
        );

        return $affiliation->id;
    }
}
