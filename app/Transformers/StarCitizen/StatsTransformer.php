<?php
/**
 * Created by IntelliJ IDEA.
 * User: Sebastian
 * Date: 04.02.2017
 * Time: 21:29
 */

namespace App\Transformers\StarCitizen;

use App\Transformers\BaseAPITransformer;
use GuzzleHttp\Psr7\Response;

class StatsTransformer extends BaseAPITransformer
{

	public function transform($stats)
    {
		return $stats;
	}

}
