<?php
/**
 * User: Hannes
 * Date: 23.03.2017
 * Time: 14:57
 */

namespace App\Traits;

use App\Exceptions\InvalidDataException;
use App\Exceptions\MissingTransformerException;
use Spatie\Fractal\Fractal;

trait TransformesData
{
    /** @var Fractal */
    protected $_fractalManager;
    protected $_transformer;
    /** @var array */
    protected $_dataToTransform;
    protected $_allowedTransformations = [
        TRANSFORM_COLLECTION,
        TRANSFORM_ITEM,
        TRANSFORM_NULL
    ];

    /** Transformation Type */
    protected $_transformationType = TRANSFORM_ITEM;

    /** @var  Fractal */
    private $_transformedResource;

    /**
     * Sets the transformation type to Item
     */
    public function item()
    {
        $this->_transformationType = TRANSFORM_ITEM;
        return $this;
    }

    /**
     * sets the transformation type to collection
     */
    public function collection()
    {
        $this->_transformationType = TRANSFORM_COLLECTION;
        return $this;
    }

    /**
     * Sets the transformation type to NullResource
     */
    public function null()
    {
        $this->_transformationType = TRANSFORM_NULL;
        return $this;
    }

    /**
     * @param String $transformer
     * @return $this
     */
    public function withTransformer(String $transformer)
    {
        $this->_checkIfTransformerIsValid($transformer);
        $this->_transformer = new $transformer();
        return $this;
    }

    /**
     * Transformes the given data
     * @param null $data
     * @return $this
     */
    public function transform($data = null)
    {
        if (!is_null($data)) {
            $this->_dataToTransform = $data;
        }

        $this->_createFractalInstance();
        $this->_checkIfDataIsValid();
        $this->_checkNullTransformation();
        $this->_checkIfReadyToTransform();

        $this->_transformedResource = $this->_fractalManager->data(
            $this->_transformationType,
            $this->_dataToTransform,
            $this->_transformer
        );

        $this->_addMetadataToTransformation();

        return $this;
    }

    /**
     * @return String
     */
    public function asJSON() : String
    {
        if (is_null($this->_transformedResource)) {
            $this->transform();
        }
        return $this->_transformedResource->toJson();
    }

    /**
     * @return array
     */
    public function asArray() : array
    {
        if (is_null($this->_transformedResource)) {
            $this->transform();
        }
        return $this->_transformedResource->toArray();
    }

    /**
     * Creates a fractal instance if null
     */
    protected function _createFractalInstance() : void
    {
        if (is_null($this->_fractalManager)) {
            $this->_fractalManager = Fractal::create();
        }
    }

    /**
     * @param $transformer
     * @throws MissingTransformerException
     */
    protected function _checkIfTransformerIsValid($transformer)
    {
        if (is_null($this->_transformer)) {
            throw new MissingTransformerException();
        }
    }

    protected function _checkIfReadyToTransform()
    {
    }

    protected function _addMetadataToTransformation()
    {
    }

    /**
     * @throws InvalidDataException
     */
    protected function _checkIfDataIsValid()
    {
        if (is_null($this->_dataToTransform)) {
            throw new InvalidDataException('Data to transform is empty');
        }
    }

    /**
     * Sets the transformation type to NullResource if data is empty
     */
    protected function _checkNullTransformation()
    {
        if (is_null($this->_dataToTransform) || empty($this->_dataToTransform)) {
            $this->_transformationType = TRANSFORM_NULL;
        }
    }
}