<?php

namespace App\Http\Controllers;

use App\Traits\ExecutionTimeTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * Class Controller
 *
 * @package App\Http\Controllers
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, ExecutionTimeTrait;

    /**
     * Controller constructor.
     */
    public function __construct()
    {
    }
}
