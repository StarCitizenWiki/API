<?php
/**
 * Created by IntelliJ IDEA.
 * User: Sebastian
 * Date: 04.02.2017
 * Time: 21:29
 */

namespace App\Transformers\StarCitizen\Stats;

use App\Transformers\BaseAPITransformer;

class FundsTransformer extends BaseAPITransformer
{
	public function transform($stats)
    {
		return ['funds' => $stats['data']['funds']];
	}
}
