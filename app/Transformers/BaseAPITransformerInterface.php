<?php
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 05.02.2017
 * Time: 22:02
 */

namespace App\Transformers;

interface BaseAPITransformerInterface
{

    public function setSuccess(bool $success) : void;

    public function isSuccess(): bool;

    public function transform($data);

}