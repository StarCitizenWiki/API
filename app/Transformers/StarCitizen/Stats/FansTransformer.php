<?php
/**
 * Created by IntelliJ IDEA.
 * User: Sebastian
 * Date: 04.02.2017
 * Time: 21:29
 */

namespace App\Transformers\StarCitizen\Stats;

use App\Transformers\BaseAPITransformerInterface;
use League\Fractal\TransformerAbstract;

class FansTransformer extends TransformerAbstract implements BaseAPITransformerInterface
{
	public function transform($stats)
    {
		return ['fans' => $stats['data']['fans']];
	}
}
