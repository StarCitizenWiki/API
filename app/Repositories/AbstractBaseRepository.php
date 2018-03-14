<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hannes
 * Date: 01.02.2017
 * Time: 23:11
 */

namespace App\Repositories;

use Carbon\Carbon;
use Psr\Http\Message\ResponseInterface;
use Spatie\Fractal\Fractal;

/**
 * Class BaseAPITrait
 */
abstract class AbstractBaseRepository
{
    /**
     * Guzzle Client
     *
     * @var  \GuzzleHttp\Client
     */
    protected $client;

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
                'Processed at' => Carbon::now(),
            ]
        );
    }

    /**
     * Checks if the Response Data is valid, must be overridden
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return bool
     */
    abstract protected function checkIfResponseDataIsValid(ResponseInterface $response): bool;
}
