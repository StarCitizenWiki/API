<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hannes
 * Date: 01.02.2017
 * Time: 23:11
 */

namespace App\Repositories;

use Carbon\Carbon;
use Spatie\Fractal\Fractal;

/**
 * Class BaseAPITrait
 */
abstract class AbstractBaseRepository
{
    /**
     * @var \Spatie\Fractal\Fractal
     */
    protected $manager;

    /**
     * AbstractBaseRepository constructor.
     */
    public function __construct()
    {
        $this->manager = Fractal::create();
        $this->manager->addMeta(
            [
                'processed_at' => Carbon::now(),
            ]
        );
    }
}
