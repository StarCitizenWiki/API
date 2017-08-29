<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 29.08.2017
 * Time: 14:26
 */

namespace App\Interfaces;

use App\Transformers\AbstractBaseTransformer;

/**
 * Interface TransformableInterface
 * @package App\Interfaces
 */
interface TransformableInterface
{
    /**
     * Sets the transformation type to Item
     *
     * @return $this
     */
    public function item();

    /**
     * Sets the transformation type to collection
     *
     * @return $this
     */
    public function collection();

    /**
     * Sets the transformation type to NullResource
     *
     * @return $this
     */
    public function null();

    /**
     * Resolves a Transformer Class Name
     *
     * @param string $transformer Transformer to use
     *
     * @return $this
     */
    public function withTransformer(string $transformer);

    /**
     * @return \App\Transformers\AbstractBaseTransformer
     */
    public function getTransformer(): AbstractBaseTransformer;

    /**
     * Returns the transformed Resource as JSON
     *
     * @return string
     */
    public function toJson(): string;

    /**
     * Transforms the given data
     *
     * @param null $data used to transform if not null
     *
     * @return $this
     */
    public function transform($data = null);

    /**
     * Returns the transformed Resource as Array
     *
     * @return array
     */
    public function toArray(): array;
}
