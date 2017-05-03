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
use Illuminate\Support\Facades\App;
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
    protected $transformedResource;

    /**
     * Sets the transformaion type to Item
     *
     * @return $this
     */
    public function item()
    {
        App::make('Log')::debug('Setting Transformation Type to '.TRANSFORM_ITEM);
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
        App::make('Log')::debug('Setting Transformation Type to '.TRANSFORM_COLLECTION);
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
        App::make('Log')::debug('Setting Transformation Type to '.TRANSFORM_NULL);
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
        App::make('Log')::debug('Setting Transformer', [
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
        App::make('Log')::debug('Starting to Transform Data');
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

        App::make('Log')::debug('Finished Transforming Data');

        return $this;
    }

    /**
     * Returns the transformed Resource as JSON
     *
     * @return String
     */
    public function asJSON() : String
    {
        App::make('Log')::debug('Returning Transformation as JSON');
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
        App::make('Log')::debug('Returning Transformation as Array');
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
        App::make('Log')::debug('Creating Fractal Manager Instance');
        if (is_null($this->fractalManager)) {
            App::make('Log')::debug('Fractal Manager is null, creating new one');
            $this->fractalManager = Fractal::create();
        } else {
            App::make('Log')::debug('Fractal Manager already Set');
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
        App::make('Log')::debug('Checking if Transformer is valid');
        if (is_null($this->transformer)) {
            App::make('Log')::warning('Transformer not set, aborting');
            throw new MissingTransformerException();
        } else {
            App::make('Log')::debug('Transformer is valid');
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
        App::make('Log')::debug('Adding Metadata to Transformation');
        $this->transformedResource->addMeta([
            'processed_at' => Carbon::now(),
        ]);
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
        App::make('Log')::debug('Checking if Data to transform is valid');
        if (is_null($this->dataToTransform) && $this->transformationType !== TRANSFORM_NULL) {
            App::make('Log')::debug('dataToTransform is null, aborting', [
                'transformation_type' => $this->transformationType,
            ]);
            throw new InvalidDataException('Data to transform is empty');
        } else {
            App::make('Log')::debug('Data to transform is valid');
        }
    }

    /**
     * Sets the transformation type to NullResource if data is empty
     *
     * @return void
     */
    protected function checkNullTransformation() : void
    {
        App::make('Log')::debug('Checking if data should be transformed as '.TRANSFORM_NULL);
        if (is_null($this->dataToTransform) || empty($this->dataToTransform)) {
            App::make('Log')::debug('Data is either empty or null, setting transformationType to '.TRANSFORM_NULL);
            $this->transformationType = TRANSFORM_NULL;
        } else {
            App::make('Log')::debug('Data is not empty');
        }
    }
}
