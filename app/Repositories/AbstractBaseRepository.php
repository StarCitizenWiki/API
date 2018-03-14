<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hannes
 * Date: 01.02.2017
 * Time: 23:11
 */

namespace App\Repositories;

use GuzzleHttp\Psr7\Response;
use League\Fractal\Manager;

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
        $this->manager = new Manager();
    }

    /**
     * Checks if the Response Data is valid, must be overridden
     *
     * @param \GuzzleHttp\Psr7\Response $response
     *
     * @return bool
     */
    abstract protected function checkIfResponseDataIsValid(Response $response): bool;
}
