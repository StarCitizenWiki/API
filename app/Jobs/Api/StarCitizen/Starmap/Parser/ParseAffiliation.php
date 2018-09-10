<?php
/**
 * User: Keonie
 * Date: 02.09.2018 20:25
 */

namespace App\Jobs\Api\StarCitizen\Starmap\Parser;


use App\Models\Api\StarCitizen\Starmap\Affiliation;

class ParseAffiliation
{
    public static function getAffiliation($affiliationData) : int {

        //TODO check input Data

        $affiliation = Affiliation::updateOrCreate(
            [
                'id'            => $affiliationData['id'],
                'name'          => $affiliationData['name'],
                'code'          => $affiliationData['code'],
                'color'         => $affiliationData['color'],
            ]
        );
        return $affiliation->id;
    }
}