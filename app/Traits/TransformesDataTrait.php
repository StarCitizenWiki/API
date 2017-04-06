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
use Illuminate\Support\Facades\Log;
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
        Log::debug('Setting Transformation Type to '.TRANSFORM_ITEM, [
            'method' => __METHOD__,
        ]);
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
        Log::debug('Setting Transformation Type to '.TRANSFORM_COLLECTION, [
            'method' => __METHOD__,
        ]);
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
        Log::debug('Setting Transformation Type to '.TRANSFORM_NULL, [
            'method' => __METHOD__,
        ]);
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
        Log::debug('Setting Transformer', [
            'method' => __METHOD__,
            'transformer' => $transformer,
        ]);
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
        Log::debug('Starting to Transform Data', [
            'method' => __METHOD__,
        ]);
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

        Log::debug('Finished Transforming Data', [
            'method' => __METHOD__,
        ]);

        return $this;
    }

    /**
     * Returns the transformed Resource as JSON
     *
     * @return String
     */
    public function asJSON() : String
    {
        Log::debug('Returning Transformation as JSON', [
            'method' => __METHOD__,
        ]);
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
        Log::debug('Returning Transformation as Array', [
            'method' => __METHOD__,
        ]);
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
        Log::debug('Creating Fractal Manager Instance', [
            'method' => __METHOD__,
        ]);
        if (is_null($this->fractalManager)) {
            Log::debug('Fractal Manager is null, creating new one', [
                'method' => __METHOD__,
            ]);
            $this->fractalManager = Fractal::create();
        } else {
            Log::debug('Fractal Manager already Set', [
                'method' => __METHOD__,
            ]);
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
        Log::debug('Checking if Transformer is valid', [
            'method' => __METHOD__,
        ]);
        if (is_null($this->transformer)) {
            Log::warning('Transformer not set, aborting', [
                'method' => __METHOD__,
            ]);
            throw new MissingTransformerException();
        } else {
            Log::debug('Transformer is valid', [
                'method' => __METHOD__,
            ]);
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
        Log::debug('Adding Metadata to Transformation', [
            'method' => __METHOD__,
        ]);
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
        Log::debug('Checking if Data to transform is valid', [
            'method' => __METHOD__,
        ]);
        if (is_null($this->dataToTransform) && $this->transformationType !== TRANSFORM_NULL) {
            Log::debug('dataToTransform is null, aborting', [
                'method' => __METHOD__,
                'transformation_type' => $this->transformationType,
            ]);
            throw new InvalidDataException('Data to transform is empty');
        } else {
            Log::debug('Data to transform is valid', [
                'method' => __METHOD__,
            ]);
        }
    }

    /**
     * Sets the transformation type to NullResource if data is empty
     *
     * @return void
     */
    protected function checkNullTransformation() : void
    {
        Log::debug('Checking if data should be transformed as '.TRANSFORM_NULL, [
            'method' => __METHOD__,
        ]);
        if (is_null($this->dataToTransform) || empty($this->dataToTransform)) {
            Log::debug('Data is either empty or null, setting transformationType to '.TRANSFORM_NULL, [
                'method' => __METHOD__,
            ]);
            $this->transformationType = TRANSFORM_NULL;
        } else {
            Log::debug('Data is not empty', [
                'method' => __METHOD__,
            ]);
        }
    }
}
