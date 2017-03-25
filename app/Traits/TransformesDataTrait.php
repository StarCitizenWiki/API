<?php
/**
 * User: Hannes
 * Date: 23.03.2017
 * Time: 14:57
 */

namespace App\Traits;

use App\Exceptions\InvalidDataException;
use App\Exceptions\MissingTransformerException;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use Spatie\Fractal\Fractal;

/**
 * Class TransformesDataTrait
 *
 * @package App\Traits
 */
trait TransformesDataTrait
{
    /**
     * Transformer
     *
     * @var TransformerAbstract
     */
    public $transformer;

    /**
     * Fractal Manager Instance
     *
     * @var Fractal
     */
    protected $fractalManager;

    /**
     * Data to transform
     *
     * @var array
     */
    protected $dataToTransform;

    /**
     * Allowed transformation types
     *
     * @var array
     */
    protected $allowedTransformations = [
        TRANSFORM_COLLECTION,
        TRANSFORM_ITEM,
        TRANSFORM_NULL,
    ];

    /**
     * Transformation Type
     */
    protected $transformationType = TRANSFORM_ITEM;

    /**
     * Transformed Fractal Object
     *
     * @var Fractal
     */
    private $transformedResource;

    /**
     * Sets the transformaion type to Item
     *
     * @return $this
     */
    public function item()
    {
        $this->transformationType = TRANSFORM_ITEM;

        return $this;
    }

    /**
     * Sets the transformation type to collection
     *
     * @return $this
     */
    public function collection()
    {
        $this->transformationType = TRANSFORM_COLLECTION;

        return $this;
    }

    /**
     * Sets the transformation type to NullResource
     *
     * @return $this
     */
    public function null()
    {
        $this->transformationType = TRANSFORM_NULL;

        return $this;
    }

    /**
     * Resolves a Transformer Class Name
     *
     * @param String $transformer Transformer to use
     *
     * @return $this
     */
    public function withTransformer(String $transformer)
    {
        $this->transformer = resolve($transformer);

        return $this;
    }

    /**
     * Transformes the given data
     *
     * @param null $data used to transform if not null
     *
     * @return $this
     */
    public function transform($data = null)
    {
        if (!is_null($data)) {
            $this->dataToTransform = $data;
        }

        $this->createFractalInstance();
        $this->checkIfDataIsValid();
        $this->checkIfTransformerIsValid();
        $this->checkNullTransformation();
        $this->checkIfReadyToTransform();

        $this->transformedResource = $this->fractalManager->data(
            $this->transformationType,
            $this->dataToTransform,
            $this->transformer
        );

        $this->addMetadataToTransformation();

        return $this;
    }

    /**
     * Returns the transformed Resource as JSON
     *
     * @return String
     */
    public function asJSON() : String
    {
        if (is_null($this->transformedResource)) {
            $this->transform();
        }

        return $this->transformedResource->toJson();
    }

    /**
     * Returns the transformed Resource as Array
     *
     * @return array
     */
    public function asArray() : array
    {
        if (is_null($this->transformedResource)) {
            $this->transform();
        }

        return $this->transformedResource->toArray();
    }

    /**
     * Creates a fractal instance if null
     *
     * @return void
     */
    protected function createFractalInstance() : void
    {
        if (is_null($this->fractalManager)) {
            $this->fractalManager = Fractal::create();
        }
    }

    /**
     * Called before Transformation to check if the Transformer is valid
     *
     * @throws MissingTransformerException
     *
     * @return void
     */
    protected function checkIfTransformerIsValid() : void
    {
        if (is_null($this->transformer)) {
            throw new MissingTransformerException();
        }
    }

    /**
     * Called before Transformation to check if Ready to transform
     *
     * @return void
     */
    protected function checkIfReadyToTransform() : void
    {
    }

    /**
     * Adds Metadata to the transformed resource
     *
     * @return void
     */
    protected function addMetadataToTransformation() : void
    {
        $this->transformedResource->addMeta(
            [
                'processed_at' => Carbon::now(),
            ]
        );
    }

    /**
     * Checks if the data to transform is valid
     *
     * @throws InvalidDataException
     *
     * @return void
     */
    protected function checkIfDataIsValid() : void
    {
        if (is_null($this->dataToTransform)) {
            throw new InvalidDataException('Data to transform is empty');
        }
    }

    /**
     * Sets the transformation type to NullResource if data is empty
     *
     * @return void
     */
    protected function checkNullTransformation() : void
    {
        if (is_null($this->dataToTransform) || empty($this->dataToTransform)) {
            $this->transformationType = TRANSFORM_NULL;
        }
    }
}
