<?php declare(strict_types = 1);
/**
 * Created by IntelliJ IDEA.
 * User: Sebastian
 * Date: 04.02.2017
 * Time: 21:29
 */

namespace App\Transformers\StarCitizen\Stats;

use App\Transformers\BaseAPITransformerInterface;
use League\Fractal\TransformerAbstract;

/**
 * Class FundsTransformer
 *
 * @package App\Transformers\StarCitizen\Stats
 */
class FundsTransformer extends TransformerAbstract implements BaseAPITransformerInterface
{
    /**
     * Transformes Stats to only return the funds
     *
     * @param mixed $stats Data
     *
     * @return array
     */
    public function transform($stats)
    {
        return ['funds' => $stats['data']['funds']];
    }
}
