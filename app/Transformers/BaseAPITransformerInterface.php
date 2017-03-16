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

    /**
     * @param $data
     * @return mixed
     */
    public function transform($data);

}