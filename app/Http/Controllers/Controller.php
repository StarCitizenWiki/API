<?php

namespace App\Http\Controllers;

use App\Processors\UserInfoProcessor;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Monolog\Processor\WebProcessor;

/**
 * Class Controller
 *
 * @package App\Http\Controllers
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        app('Log')::getMonolog()->pushProcessor(new UserInfoProcessor());
        app('Log')::getMonolog()->pushProcessor(new WebProcessor());
    }
}
