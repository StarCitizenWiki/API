<?php

namespace App\Http\Controllers\Tools;

use App\Exceptions\InvalidDataException;
use App\Exceptions\MissingExtensionException;
use App\Repositories\StarCitizen\APIv1\Stats\StatsRepository;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class FundImageController extends Controller
{
    const COLORS = ['blue' => [0, 231, 255], 'black' => [51, 51, 51]];
    const FUNDING_ONLY = 'funding_only';
    const FUNDING_AND_TEXT = 'funding_and_text';
    const FUNDING_AND_BARS = 'funding_and_bars';

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

    public function getImageWithText()
    {
        $this->_image['type'] = FundImageController::FUNDING_AND_TEXT;
        return $this->getImage();
    }

    public function getImageWithBars()
    {
        $this->_image['type'] = FundImageController::FUNDING_AND_BARS;
        return $this->getImage();
    }

    public function getImage()
    {
        // @TODO Aus $request schließen welche Bildversion verlangt wird, abgleichen ob Bild im Cache
        try {
            $this->_getFundsFromAPI();
            $this->_formatFunds();
            $this->_determineImageHeight();
            $this->_initImage();
            $this->_addDataToImage();
            $this->_flushImageToString();
            $this->_saveImageToDisk();
            return response()->file(storage_path('app/public/'.$this->_image['name']));
        } catch (InvalidDataException $e) {
            // @TODO Bild aus Cache laden
        }
    }

    public function blackColor() : FundImageController
    {
        $this->_font['color'] = FundImageController::COLORS['black'];
        return $this;
    }

    public function blueColor() : FundImageController
    {
        $this->_font['color'] = FundImageController::COLORS['blue'];
        return $this;
    }

    public function fundingOnly() : FundImageController
    {
        $this->_image['type'] = FundImageController::FUNDING_ONLY;
        return $this;
    }

    public function fundingAndText() : FundImageController
    {
        $this->_image['type'] = FundImageController::FUNDING_AND_TEXT;
        return $this;
    }

    private function _checkIfImageCanBeCreated()
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
        $this->_assembleFileName();
        Storage::disk('public')->put($this->_image['name'], $this->_image['data']);
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

}
