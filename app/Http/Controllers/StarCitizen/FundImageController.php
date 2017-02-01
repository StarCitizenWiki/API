<?php

namespace App\Http\Controllers\Tools;

use App\Exceptions\InvalidDataException;
use App\Exceptions\MissingExtensionException;
use App\Repositories\StarCitizen\APIv1\Stats\StatsRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;

class FundImageController extends Controller
{
    const COLORS = ['blue' => [0, 231, 255], 'darkblue' => [69, 117, 129], 'black' => [51, 51, 51]];
    const FUNDING_ONLY = FUNDIMAGE_FUNDING_ONLY;
    const FUNDING_AND_TEXT = FUNDIMAGE_FUNDING_AND_TEXT;
    const FUNDING_AND_BARS = FUNDIMAGE_FUNDING_AND_BARS;

	const SUPPORTED_FUNDS = [
		FundImageController::FUNDING_ONLY,
		FundImageController::FUNDING_AND_TEXT,
		FundImageController::FUNDING_AND_BARS
	];

	private $_request;
    private $_api;
    private $_funds = [
        'current' => null,
        'currentFormatted' => null,
        'nextMillion' => null,
        'nextMillionFormatted' => null,
        'percentageToNextMillion' => null,
        'substractor' => null,
    ];
    private $_image = [
        'pointer' => null,
        'width' => null,
        'height' => null,
        'data' => null,
        'type' => null,
        'text' => 'Crowdfunding:',
        'name' => null
    ];
    private $_font = [
        'path' => null,
        'color' => null,
    ];

    public function __construct(Request $request, StatsRepository $api)
    {
        $this->_checkIfImageCanBeCreated();
        $this->_request = $request;
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
            $this->_determineImageWidth();
            $this->_determineImageHeight();
            $this->_initImage();
            $this->_addDataToImage();
            $this->_flushImageToString();
            $this->_saveImageToDisk();
        } catch (\Exception $e) {
            // @TODO Logging und Mailversand
        }

        return $this->_loadImageFromDisk();
    }

    private function _checkIfImageCanBeCreated() : void
    {
        if (!in_array('gd', get_loaded_extensions())) {
            throw new MissingExtensionException('GD Library is missing!');
        }
    }

    private function _setImageType() : void
    {
        if (in_array(Route::getCurrentRoute()->getAction()['type'], FundImageController::SUPPORTED_FUNDS)) {
            $this->_image['type'] = Route::getCurrentRoute()->getAction()['type'];
        } else {
            throw new \InvalidArgumentException('FundImage function only accepts Supported Image Types('
                .implode(', ',FundImageController::SUPPORTED_FUNDS).'). Input was: '
                .Route::getCurrentRoute()->getAction()['type']);
        }
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

    private function _checkIfImageCanBeLoadedFromCache() : bool
    {
        if (Storage::disk(FUNDIMAGE_DISK_SAVE_PATH)->exists($this->_image['name']) &&
            Storage::disk(FUNDIMAGE_DISK_SAVE_PATH)->lastModified($this->_image['name']) > time() - FUNDIMAGE_CACHE_TIME) {
            return true;
        }
        return false;
    }

    private function _getFundsFromAPI() : void
    {
        $funds = $this->_api->lastHours()->getCrowdfundStats()->asArray();
        $this->_funds['current'] = substr($funds['data']['funds'], 0, -2);
    }

    private function _formatFunds($source = 'current') : void
    {
        if ($source !== 'current') {
            $source = 'nextMillion';
        }
        $this->_funds[$source.'Formatted'] = number_format($this->_funds[$source], 0, ',', '.').' $';
    }

    private function _determineImageWidth() : void
    {
        switch ($this->_image['type']) {
            case FundImageController::FUNDING_ONLY:
                $this->_image['width'] = 230;
                break;

            case FundImageController::FUNDING_AND_TEXT:
                $this->_image['width'] = 280;
                break;

            case FundImageController::FUNDING_AND_BARS:
                $this->_image['width'] = 305;
                break;
        }
    }

    private function _determineImageHeight() : void
    {
        switch ($this->_image['type']) {
            case FundImageController::FUNDING_ONLY:
                $this->_image['height'] = 35;
                break;

            case FundImageController::FUNDING_AND_TEXT:
                $this->_image['height'] = 75;
                break;

            case FundImageController::FUNDING_AND_BARS:
                $this->_image['height'] = 41;
                break;
        }
    }

    private function _initImage() : void
    {
        $this->_image['pointer'] = imagecreatetruecolor( $this->_image['width'], $this->_image['height'] );
        imagesavealpha( $this->_image['pointer'], true );

        $transparentColor = imagecolorallocatealpha($this->_image['pointer'], 0, 0, 0, 127);
        imagefill($this->_image['pointer'], 0, 0, $transparentColor);
    }

    private function _addDataToImage() : void
    {
        $fontColor = $this->_makeColorFromArray();
        switch ($this->_image['type']) {
            case FundImageController::FUNDING_AND_TEXT:
                imagettftext($this->_image['pointer'], 25, 0, 0, 30, $fontColor, $this->_font['path'], $this->_image['text']);
                imagettftext($this->_image['pointer'], 25, 0, 2, 70, $fontColor, $this->_font['path'], $this->_funds['currentFormatted']);
                break;

            case FundImageController::FUNDING_AND_BARS:
                $this->_initBarImage();
                $fontColor = $this->_makeColorFromArray();
                imagestring($this->_image['pointer'], 2, 0, 0, $this->_image['text'], $fontColor);
                $this->_addBarsToBarImage();
                break;

            case FundImageController::FUNDING_ONLY:
                imagettftext($this->_image['pointer'], 20, 0, 2, 30, $fontColor, $this->_font['path'], $this->_funds['currentFormatted']);
                break;
        }
    }

    private function _initBarImage() : void
    {
        $this->_font['color'] = FundImageController::COLORS['blue'];
        $this->_roundFundsToNextMillion();
        $this->_calculatePercentageToNextMillion();
        $this->_image['text'] = 'Crowdfunding: '.$this->_funds['currentFormatted'].
                                ' von '.$this->_funds['nextMillionFormatted'].
                                ' ('.$this->_funds['percentageToNextMillion'].'%)';
    }

    private function _addBarsToBarImage() : void
    {
        $this->_font['color'] = FundImageController::COLORS['darkblue'];
        $darkBlue = $this->_makeColorFromArray();

        $this->_font['color'] = FundImageController::COLORS['blue'];
        $blue = $this->_makeColorFromArray();

        for ($i = 0; $i <= 300; $i = $i + 5) {
            if ((($this->_funds['nextMillion'] - $this->_funds['substractor']) / 1000000) * 100 >= $i) {
                imageline($this->_image['pointer'], $i, 15, $i, 40, $blue);
                imageline($this->_image['pointer'], $i + 1, 15, $i + 1, 40, $blue);
                imageline($this->_image['pointer'], $i + 2, 15, $i + 2, 40, $blue);
            } else {
                imageline($this->_image['pointer'], $i, 15, $i, 40, $darkBlue);
                imageline($this->_image['pointer'], $i + 1, 15, $i + 1, 40, $darkBlue);
                imageline($this->_image['pointer'], $i + 2, 15, $i + 2, 40, $darkBlue);
            }
        }

    }

    private function _makeColorFromArray()
    {
        return imagecolorallocate(
            $this->_image['pointer'],
            $this->_font['color'][0],
            $this->_font['color'][1],
            $this->_font['color'][2]
        );
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

    private function _roundFundsToNextMillion()
    {
        $this->_funds['nextMillion'] = ceil($this->_funds['current'] / 1000000) * 1000000;
        $this->_formatFunds('nextMillion');
    }

    private function _calculatePercentageToNextMillion()
    {
        if ($this->_funds['nextMillion'] === null ||
            $this->_funds['current'] === null) {
            throw new InvalidDataException('Did you call _roundFundsToNextMillion()?');
        }
        $this->_funds['substractor'] = $this->_funds['nextMillion'] - 1000000;
        $this->_funds['percentageToNextMillion'] = round((($this->_funds['current'] - $this->_funds['substractor']) /
                                                   ($this->_funds['nextMillion'] - $this->_funds['substractor'])) * 100);
    }
}
