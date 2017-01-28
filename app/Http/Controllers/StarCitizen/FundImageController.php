<?php

namespace App\Http\Controllers\StarCitizen;

use App\Repositories\StarCitizen\APIv1\Stats\StatsRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class FundImageController extends Controller
{
    const COLORS = ['blue' => [0, 231, 255], 'black' => [51, 51, 51]];
    const FUNDING_ONLY = 'funding_only';
    const FUNDING_AND_TEXT = 'funding_and_text';

    private $_request;
    private $_api;
    private $_funds;
    private $_image = [
        'pointer' => null,
        'width' => null,
        'height' => null,
        'data' => null,
        'type' => FundImageController::FUNDING_AND_TEXT,
        'text' => 'Crowdfunding:',
        'name' => null
    ];
    private $_font = [
        'path' => null,
        'color' => null,
    ];

    public function __construct(Request $request, StatsRepository $api)
    {
        $this->_request = $request;
        $this->_api = $api;
        $this->_font['path'] = resource_path('assets/fonts/orbitron-light-webfont.ttf');
        $this->_font['color'] = FundImageController::COLORS['black'];
    }

    public function getImage()
    {
        $this->_getFundsFromAPI();
        $this->_formatFunds();
        $this->_determineImageHeight();
        $this->_initImage();
        $this->_addDataToImage();
        $this->_flushImageToString();
        $this->_saveImageToDisk();
        return response()->file(storage_path('app/public/'.$this->_image['name']));
    }

    public function blackColor()
    {
        $this->_font['color'] = FundImageController::COLORS['black'];
        return $this;
    }

    public function blueColor()
    {
        $this->_font['color'] = FundImageController::COLORS['blue'];
        return $this;
    }

    public function fundingOnly()
    {
        $this->_image['type'] = FundImageController::FUNDING_ONLY;
        return $this;
    }

    public function fundingAndText()
    {
        $this->_image['type'] = FundImageController::FUNDING_AND_TEXT;
        return $this;
    }

    private function _determineImageHeight()
    {
        if ($this->_image['type'] === FundImageController::FUNDING_ONLY) {
            $this->_image['height'] = 35;
        } else {
            $this->_image['height'] = 75;
        }
    }

    private function _initImage()
    {
        $this->_image['width'] = 280;
        $this->_image['pointer'] = imagecreatetruecolor( $this->_image['width'], $this->_image['height'] );
        imagesavealpha( $this->_image['pointer'], true );

        $transparentColor = imagecolorallocatealpha($this->_image['pointer'], 0, 0, 0, 127);
        imagefill($this->_image['pointer'], 0, 0, $transparentColor);
    }

    private function _addDataToImage()
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

    private function _flushImageToString()
    {
        ob_start();
        imagepng($this->_image['pointer']);
        $this->_image['data'] = ob_get_contents();
        ob_end_clean();
    }

    private function _saveImageToDisk()
    {
        $this->_assembleFileName();
        Storage::disk('public')->put($this->_image['name'], $this->_image['data']);
    }

    private function _assembleFileName()
    {
        if ($this->_font['color'] === FundImageController::COLORS['black']) {
            $color = '_black';
        } else {
            $color = '_blue';
        }
        $this->_image['name'] = $this->_image['type'].$color.'.png';
    }

    private function _getFundsFromAPI()
    {
        $funds = $this->_api->lastHours()->getCrowdfundStats()->asArray();
        $this->_funds = $funds['data']['funds'];
    }

    private function _formatFunds()
    {
        $this -> _funds = number_format(substr($this->_funds, 0, -2), 0, ',', '.') . '$';
    }
}
