<?php

namespace App\Http\Controllers\Tools;

use App\Exceptions\InvalidDataException;
use App\Exceptions\MissingExtensionException;
use App\Repositories\StarCitizen\APIv1\Stats\StatsRepository;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;

class FundImageController extends Controller
{
    const COLORS = ['blue' => [0, 231, 255], 'black' => [51, 51, 51]];
    const FUNDING_ONLY = FUNDIMAGE_FUNDING_ONLY;
    const FUNDING_AND_TEXT = FUNDIMAGE_FUNDING_AND_TEXT;
    const FUNDING_AND_BARS = FUNDIMAGE_FUNDING_AND_BARS;

	const SUPPORTED_FUNDS = [
		FundImageController::FUNDING_ONLY,
		FundImageController::FUNDING_AND_TEXT,
		FundImageController::FUNDING_AND_BARS
	];

    private $_api;
    private $_funds;
    private $_image = [
        'pointer' => null,
        'width' => null,
        'height' => null,
        'data' => null,
        'type' => FundImageController::FUNDING_ONLY,
        'text' => 'Crowdfunding:',
        'name' => null
    ];
    private $_font = [
        'path' => null,
        'color' => null,
    ];

    public function __construct(StatsRepository $api)
    {
        $this->_checkIfImageCanBeCreated();
        $this->_api = $api;
        $this->_font['path'] = resource_path('assets/fonts/orbitron-light-webfont.ttf');
        $this->_font['color'] = FundImageController::COLORS['black'];
    }

    /**
     * Sets the Image Type to FUNDING_AND_TEXT
     *
     * @return mixed
     */
    public function getImageWithText()
    {
        $this->_image['type'] = FundImageController::FUNDING_AND_TEXT;
        return $this->getImage();
    }

    /**
     * Sets the Image Type to FUNDING_AND_BARS
     *
     * @return mixed
     */
    public function getImageWithBars()
    {
        $this->_image['type'] = FundImageController::FUNDING_AND_BARS;
        return $this->getImage();
    }


    /**
     * Generates the Image with the defined Values
     *
     * @return mixed
     */
    public function getImage()
    {
        try {
            $this->_setImageType();
        } catch (\InvalidArgumentException $e) {
            abort(400);
        }

        $this->_assembleFileName();

        if ($this->_checkIfImageCanBeLoadedFromCache()) {
            return $this->_loadImageFromDisk();
        }

        try {
            $this->_getFundsFromAPI();
            $this->_formatFunds();
            $this->_determineImageHeight();
            $this->_initImage();
            $this->_addDataToImage();
            $this->_flushImageToString();
            $this->_saveImageToDisk();
        } catch (InvalidDataException $e) {
            // @TODO Logging und Mailversand
        } finally {
            return $this->_loadImageFromDisk();
        }
    }

    /**
     * @deprecated Farbe über Request ermitteln
     *
     * @return FundImageController
     */
    public function blackColor() : FundImageController
    {
        $this->_font['color'] = FundImageController::COLORS['black'];
        return $this;
    }

    /**
     * @deprecated Farbe über Request ermitteln
     *
     * @return FundImageController
     */
    public function blueColor() : FundImageController
    {
        $this->_font['color'] = FundImageController::COLORS['blue'];
        return $this;
    }

    private function _checkIfImageCanBeCreated() : void
    {
        if (!in_array('gd', get_loaded_extensions())) {
            throw new MissingExtensionException('GD Library is missing!');
        }
    }

    private function _determineImageHeight() : void
    {
        if ($this->_image['type'] === FundImageController::FUNDING_ONLY) {
            $this->_image['height'] = 35;
        } else {
            $this->_image['height'] = 75;
        }
    }

    private function _initImage() : void
    {
        $this->_image['width'] = 280;
        $this->_image['pointer'] = imagecreatetruecolor( $this->_image['width'], $this->_image['height'] );
        imagesavealpha( $this->_image['pointer'], true );

        $transparentColor = imagecolorallocatealpha($this->_image['pointer'], 0, 0, 0, 127);
        imagefill($this->_image['pointer'], 0, 0, $transparentColor);
    }

    private function _addDataToImage() : void
    {
        $fontColor = $this->_makeColorFromArray();
        if ($this->_image['type'] === FundImageController::FUNDING_AND_TEXT) {
            imagettftext($this->_image['pointer'], 25, 0, 0, 30, $fontColor, $this->_font['path'], $this->_image['text']);
            imagettftext($this->_image['pointer'], 25, 0, 2, 70, $fontColor, $this->_font['path'], $this->_funds);
        } else {
            imagettftext($this->_image['pointer'], 20, 0, 2, 30, $fontColor, $this->_font['path'], $this->_funds);
        }
    }

    private function _makeColorFromArray()
    {
        return imagecolorallocate($this->_image['pointer'], $this->_font['color'][0], $this->_font['color'][1], $this->_font['color'][2]);
    }

    private function _flushImageToString() : void
    {
        ob_start();
        imagepng($this->_image['pointer']);
        $this->_image['data'] = ob_get_contents();
        ob_end_clean();
    }

    private function _saveImageToDisk() : void
    {
        Storage::disk(FUNDIMAGE_DISK_SAVE_PATH)->put($this->_image['name'], $this->_image['data']);
    }

    private function _loadImageFromDisk()
    {
        return response()->file(storage_path(FUNDIMAGE_RELATIVE_SAVE_PATH.$this->_image['name']));
    }

    private function _assembleFileName() : void
    {
        if ($this->_font['color'] === FundImageController::COLORS['black']) {
            $color = '_black';
        } else {
            $color = '_blue';
        }
        $this->_image['name'] = $this->_image['type'].$color.'.png';
    }

    private function _getFundsFromAPI() : void
    {
        $funds = $this->_api->lastHours()->getCrowdfundStats()->asArray();
        $this->_funds = $funds['data']['funds'];
    }

    private function _formatFunds() : void
    {
        $this -> _funds = number_format(substr($this->_funds, 0, -2), 0, ',', '.') . '$';
    }

	private function _setImageType() : void
	{
		if (in_array(Route::getCurrentRoute()->getAction()['type'], FundImageController::SUPPORTED_FUNDS)) {
			$this->_image['type'] = Route::getCurrentRoute()->getAction()['type'];
		} else {
			throw new \InvalidArgumentException('FundImage function only accepts Supported Image Types('
				.implode(", ",FundImageController::SUPPORTED_FUNDS).'). Input was: '
				.Route::getCurrentRoute()->getAction()['type']);
		}
	}

	private function _checkIfImageCanBeLoadedFromCache() : bool
    {
        if (Storage::disk(FUNDIMAGE_DISK_SAVE_PATH)->exists($this->_image['name']) &&
            Storage::disk(FUNDIMAGE_DISK_SAVE_PATH)->lastModified($this->_image['name']) > time() - FUNDIMAGE_CACHE_TIME) {
            return true;
        }
        return false;
    }

}
