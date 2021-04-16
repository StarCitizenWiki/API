<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\StarCitizenUnpacked\Weapons;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\StarCitizenUnpacked\WeaponPersonal;
use App\Transformers\Api\V1\StarCitizenUnpacked\WeaponPersonalTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

class WeaponPersonalController extends ApiController
{
    /**
     * ShipController constructor.
     *
     * @param WeaponPersonalTransformer $transformer
     * @param Request $request
     */
    public function __construct(WeaponPersonalTransformer $transformer, Request $request)
    {
        $this->transformer = $transformer;

        parent::__construct($request);
    }

    public function index(): Response
    {
        return $this->getResponse(WeaponPersonal::query()->orderBy('name'));
    }
}
