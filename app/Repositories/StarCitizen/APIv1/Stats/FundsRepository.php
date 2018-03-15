<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 14.03.2018
 * Time: 22:44
 */

namespace App\Repositories\StarCitizen\APIv1\Stats;

use App\Repositories\StarCitizen\AbstractStarCitizenRepository as StarCitizenRepository;
use App\Repositories\StarCitizen\Interfaces\FundsRepositoryInterface;
use App\Transformers\StarCitizen\Stats\FundsTransformer;

/**
 * Funds Repository
 */
class FundsRepository extends StarCitizenRepository implements FundsRepositoryInterface
{


    public function __construct(FundsTransformer $transformer)
    {
        $this->manager->transformWith($transformer);
    }

    public function getFunds()
    {
        // TODO: Implement getFunds() method.
    }
}
