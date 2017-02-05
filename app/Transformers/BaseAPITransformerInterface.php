<?php
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 05.02.2017
 * Time: 22:02
 */

namespace App\Transformers;

use GuzzleHttp\Psr7\Response;

interface BaseAPITransformerInterface
{

    public function getStatusCode(): int;

    public function setStatusCode(int $statusCode) : void;

    public function setSuccess(bool $success) : void;

    public function isSuccess(): bool;

    public function transform(Response $response);

}