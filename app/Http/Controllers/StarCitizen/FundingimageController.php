<?php

namespace App\Http\Controllers\StarCitizen;

use App\Repositories\StarCitizen\APIv1\Stats\StatsRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class FundingimageController extends Controller
{
    const COLORS = ['blue' => [0, 231, 255], 'black' => [51, 51, 51]];
    const FUNDING_ONLY = 'funding_only';
    const FUNDING_AND_TEXT = 'funding_and_text';
    const IMAGE_TEXT = 'Crowdfunding:';

    private $_api;
    private $_image;
    private $_imageWidth;
    private $_imageHeight;
    private $_imageType = FundingimageController::FUNDING_AND_TEXT;
    private $_fontPath;
    private $_fontColor;
    private $_funds;


    public function __construct(StatsRepository $api)
    {
        $this->_api = $api;
        $this->_fontColor = FundingimageController::COLORS['black'];
        $this->_fontPath = resource_path('assets/fonts/orbitron-light-webfont.ttf');
        $this->_imageWidth = 280;
        $this->_getFundsFromAPI();
    }

    public function getImage()
    {
        $this->_determineImageHeight();
        $this->_initImage();
        $this->_addDataToImage();
        ob_start();
        imagepng($this->_image);
        $image_data = ob_get_contents();
        ob_end_clean();
        Storage::disk('public')->put('fundImage.png', $image_data);
        return response()->file(Storage::disk('public')->get('fundImage.png'));
    }

    public function blackColor()
    {
        $this->_fontColor = FundingimageController::COLORS['black'];
        return $this;
    }

    public function blueColor()
    {
        $this->_fontColor = FundingimageController::COLORS['blue'];
        return $this;
    }

    public function fundingOnly()
    {
        $this->_imageType = FundingimageController::FUNDING_ONLY;
        return $this;
    }

    public function fundingAndText()
    {
        $this->_imageType = FundingimageController::FUNDING_AND_TEXT;
        return $this;
    }

    private function _determineImageHeight()
    {
        if ($this->_imageType === FundingimageController::FUNDING_ONLY) {
            $this->_imageHeight = 35;
        } else {
            $this->_imageHeight = 75;
        }
    }

    private function _initImage()
    {
        $this->_image = imagecreatetruecolor( $this->_imageWidth, $this->_imageHeight );
        imagesavealpha( $this->_image, true );

        $transparentColor = imagecolorallocatealpha($this->_image, 0, 0, 0, 127);
        imagefill($this->_image, 0, 0, $transparentColor);
    }

    private function _addDataToImage()
    {
        $fontColor = $this->_makeImageColorFromArray();
        if ($this->_imageType === FundingimageController::FUNDING_AND_TEXT) {
            imagettftext($this->_image, 25, 0, 0, 30, $fontColor, $this->_fontPath, FundingimageController::IMAGE_TEXT);
            imagettftext($this->_image, 25, 0, 2, 70, $fontColor, $this->_fontPath, $this->_funds);
        } else {
            imagettftext($this->_image, 20, 0, 2, 30, $fontColor, $this->_fontPath, $this->_funds);
        }
    }

    private function _makeImageColorFromArray()
    {
        return imagecolorallocate($this->_image, $this->_fontColor[0], $this->_fontColor[1], $this->_fontColor[2]);
    }

    private function _getFundsFromAPI()
    {
        $funds = $this->_api->lastHours()->getCrowdfundStats()->asArray();
        $this->_funds = $funds['data']['funds'];
        $this->_formatFunds();
    }

    private function _formatFunds()
    {
        $this -> _funds = number_format(substr($this->_funds, 0, -2), 0, ',', '.') . '$';
    }
}
