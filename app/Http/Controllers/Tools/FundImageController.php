<?php

namespace App\Http\Controllers\Tools;

use App\Exceptions\InvalidDataException;
use App\Exceptions\MissingExtensionException;
use App\Facades\Log;
use App\Http\Controllers\Controller;
use App\Repositories\StarCitizen\APIv1\StatsRepository;
use App\Traits\ProfilesMethodsTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

/**
 * Class FundImageController
 *
 * @package App\Http\Controllers\Tools
 */
class FundImageController extends Controller
{
    use ProfilesMethodsTrait;

    const COLORS = [
        'blue' => [0, 231, 255],
        'darkblue' => [69, 117, 129],
        'black' => [51, 51, 51],
    ];

    const FUNDING_ONLY = FUNDIMAGE_FUNDING_ONLY;
    const FUNDING_AND_TEXT = FUNDIMAGE_FUNDING_AND_TEXT;
    const FUNDING_AND_BARS = FUNDIMAGE_FUNDING_AND_BARS;

    const SUPPORTED_FUNDS = [
        FundImageController::FUNDING_ONLY,
        FundImageController::FUNDING_AND_TEXT,
        FundImageController::FUNDING_AND_BARS,
    ];

    /**
     * The Request Object
     *
     * @var Request
     */
    private $request;

    /**
     * StatsRepository
     *
     * @var StatsRepository
     */
    private $repository;

    private $funds = [
        'current' => null,
        'currentFormatted' => null,
        'nextMillion' => null,
        'nextMillionFormatted' => null,
        'percentageToNextMillion' => null,
        'substractor' => null,
    ];

    private $image = [
        'pointer' => null,
        'width' => null,
        'height' => null,
        'data' => null,
        'type' => null,
        'text' => 'Crowdfunding:',
        'name' => null,
    ];

    private $font = [
        'path' => null,
        'color' => null,
    ];

    /**
     * FundImageController constructor.
     *
     * @param Request         $request    HTTP Request
     * @param StatsRepository $repository StatsApi
     */
    public function __construct(Request $request, StatsRepository $repository)
    {
        $this->startProfiling(__FUNCTION__);

        parent::__construct();
        $this->middleware('throttle');
        $this->middleware('token_usage');
        $this->checkIfImageCanBeCreated();
        $this->request = $request;
        $this->repository = $repository;
        $this->font['path'] = resource_path(
            'assets/fonts/orbitron-light-webfont.ttf'
        );
        $this->font['color'] = FundImageController::COLORS['black'];

        $this->stopProfiling(__FUNCTION__);
    }

    /**
     * Sets the Image Type to FUNDING_AND_TEXT
     *
     * @return mixed
     */
    public function getImageWithText()
    {
        $this->image['type'] = FundImageController::FUNDING_AND_TEXT;
        $this->addTrace('Setting Image Type to ' . FUNDIMAGE_FUNDING_AND_TEXT, __FUNCTION__, __LINE__);

        return $this->getImage();
    }

    /**
     * Sets the Image Type to FUNDING_AND_BARS
     *
     * @return mixed
     */
    public function getImageWithBars()
    {
        $this->image['type'] = FundImageController::FUNDING_AND_BARS;
        $this->addTrace('Setting Image Type to ' . FUNDIMAGE_FUNDING_AND_BARS, __FUNCTION__, __LINE__);

        return $this->getImage();
    }

    /**
     * Generates the Image with the defined Values
     * @return mixed
     * @throws \Exception
     */
    public function getImage()
    {
        $this->startProfiling(__FUNCTION__);

        try {
            $this->addTrace("Setting Image Type", __FUNCTION__, __LINE__);
            $this->setImageType();
        } catch (InvalidArgumentException $e) {
            $this->addTrace("Setting Image Type failed with Message {$e->getMessage()}", __FUNCTION__, __LINE__);
            $this->stopProfiling(__FUNCTION__);

            abort(400, $e->getMessage());
        }

        $this->setFontColorFromRequest();
        $this->assembleFilename();

        if ($this->checkIfImageCanBeLoadedFromCache()) {
            $this->stopProfiling(__FUNCTION__);

            return $this->loadImageFromDisk();
        }

        try {
            $this->getFundsFromAPI();
            $this->formatFunds();
            $this->determineImageWidth();
            $this->determineImageHeight();
            $this->initImage();
            $this->addDataToImage();
            $this->flushImageToString();
            $this->saveImageToDisk();
        } catch (Exception $e) {
            Log::warning('Fund Image generation failed', [
                'type' => $this->image['type'],
                'requester' => $this->request->getHost(),
                'message' => $e,
            ]);

            $this->stopProfiling(__FUNCTION__);

            throw new Exception('Fund Image generation failed');
        }

        app('Log')::info('Fund Image Requested', [
            'type' => $this->image['type'],
            'name' => $this->image['name'],
            'requester' => $this->request->getHost(),
        ]);

        $this->stopProfiling(__FUNCTION__);

        return $this->loadImageFromDisk();
    }

    /**
     * Checks if the GD Library is installed
     *
     * @throws MissingExtensionException
     *
     * @return void
     */
    private function checkIfImageCanBeCreated() : void
    {
        $this->addTrace('Checking if Image can be created', __FUNCTION__, __LINE__);
        if (!in_array('gd', get_loaded_extensions())) {
            throw new MissingExtensionException('GD Library is missing!');
        }
    }

    /**
     * Sets the Image type based on the request
     *
     * @return void
     */
    private function setImageType() : void
    {
        $this->startProfiling(__FUNCTION__);

        $action = Route::getCurrentRoute()->getAction()['type'];
        if (in_array($action, FundImageController::SUPPORTED_FUNDS)) {
            $this->addTrace("{$action} is a supported ImageType", __FUNCTION__, __LINE__);
            $this->image['type'] = Route::getCurrentRoute()->getAction()['type'];
        } else {
            $message = 'FundImage function only accepts Supported Image Types('.
                        implode(', ', FundImageController::SUPPORTED_FUNDS).'). Input was: '.
                        Route::getCurrentRoute()->getAction()['type'];
            Log::warning('Requested Image type does not exist', [
                'message' => $message,
            ]);

            $this->stopProfiling(__FUNCTION__);

            throw new InvalidArgumentException($message);
        }

        $this->stopProfiling(__FUNCTION__);
    }

    /**
     * Checks if the request contains a color field and tries to parse it
     */
    private function setFontColorFromRequest()
    {
        $this->startProfiling(__FUNCTION__);

        $requestColor = $this->request->get('color');
        if (!is_null($requestColor) && !empty($requestColor)) {
            $this->addTrace("Requested Color is {$requestColor}", __FUNCTION__, __LINE__);
            $colorArray = $this->convertHexToRGBColor($requestColor);
            if (!empty($colorArray)) {
                $this->addTrace("Color is " . implode('', $colorArray) . " in HEX", __FUNCTION__, __LINE__);
                $this->font['color'] = $colorArray;
            }
        }

        $this->stopProfiling(__FUNCTION__);
    }

    /**
     * Convert a hexa decimal color code to its RGB equivalent
     * http://php.net/manual/de/function.hexdec.php#99478
     *
     * @param string $hexStr (hexadecimal color value)
     *
     * @return array or string (depending on second parameter. Returns False if invalid hex color value)
     */
    private function convertHexToRGBColor($hexStr) : array
    {
        $this->startProfiling(__FUNCTION__);

        $hexStr = preg_replace("/[^0-9A-Fa-f]/", '', $hexStr); // Gets a proper hex string
        $rgbArray = [];
        if (strlen($hexStr) == 6) { //If a proper hex code, convert using bitwise operation. No overhead... faster
            $colorVal = hexdec($hexStr);
            $rgbArray[] = 0xFF & ($colorVal >> 0x10);
            $rgbArray[] = 0xFF & ($colorVal >> 0x8);
            $rgbArray[] = 0xFF & $colorVal;
        } elseif (strlen($hexStr) == 3) { //if shorthand notation, need some string manipulations
            $rgbArray[] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
            $rgbArray[] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
            $rgbArray[] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
        } else {
            return [];
        }

        $this->stopProfiling(__FUNCTION__);

        return $rgbArray;
    }

    /**
     * Generates the Filename based on the request
     *
     * @return void
     */
    private function assembleFilename() : void
    {
        $this->startProfiling(__FUNCTION__);

        $color = implode('', $this->font['color']);
        $this->image['name'] = $this->image['type'].'_'.$color.'.png';
        $this->addTrace("Filename is: {$this->image['name']}", __FUNCTION__, __LINE__);

        $this->stopProfiling(__FUNCTION__);
    }

    /**
     * Checks it the requested Image is already cached and new enough
     *
     * @return bool
     */
    private function checkIfImageCanBeLoadedFromCache() : bool
    {
        $this->startProfiling(__FUNCTION__);

        if (Storage::disk(FUNDIMAGE_DISK_SAVE_PATH)->exists($this->image['name'])) {
            $this->addTrace("Image Exists in Cache", __FUNCTION__, __LINE__);
            $imageCreationTime = Storage::disk(FUNDIMAGE_DISK_SAVE_PATH)->lastModified($this->image['name']);
            $cacheDuration = time() - FUNDIMAGE_CACHE_TIME;
            if ($imageCreationTime > $cacheDuration) {
                $this->addTrace("Image is valid and can be loaded", __FUNCTION__, __LINE__);
                $this->stopProfiling(__FUNCTION__);

                return true;
            }
        }

        $this->stopProfiling(__FUNCTION__);

        return false;
    }

    /**
     * Requests the API and saves the funds
     *
     * @return void
     */
    private function getFundsFromAPI() : void
    {
        $this->startProfiling(__FUNCTION__);

        $funds = $this->repository->getFunds()->asArray();
        $this->funds['current'] = substr($funds['data']['funds'], 0, -2);
        $this->addTrace("Got Funds from API ({$this->funds['current']})", __FUNCTION__, __LINE__);

        $this->stopProfiling(__FUNCTION__);
    }

    /**
     * Formates the funds and appends a dollar sign
     *
     * @param string $source Array Key to use the funds from
     */
    private function formatFunds($source = 'current') : void
    {
        $this->startProfiling(__FUNCTION__);

        if ($source !== 'current') {
            $source = 'nextMillion';
        }

        $this->addTrace("Formatting Funds. Source: {$source}", __FUNCTION__, __LINE__);

        $this->funds[$source.'Formatted'] = number_format(
            $this->funds[$source],
            0,
            ',',
            '.'
        ).' $';

        $this->addTrace("Funds formatted. Formatted: {$this->funds[$source.'Formatted']}", __FUNCTION__, __LINE__);

        $this->stopProfiling(__FUNCTION__);
    }

    /**
     * Determines the image Width based on Image type
     *
     * @return void
     */
    private function determineImageWidth() : void
    {
        $this->startProfiling(__FUNCTION__);

        switch ($this->image['type']) {
            case FundImageController::FUNDING_ONLY:
                $this->image['width'] = 230;
                break;

            case FundImageController::FUNDING_AND_TEXT:
                $this->image['width'] = 280;
                break;

            case FundImageController::FUNDING_AND_BARS:
                $this->image['width'] = 305;
                break;
        }
        $this->addTrace("Image Width is: {$this->image['width']}", __FUNCTION__, __LINE__);

        $this->stopProfiling(__FUNCTION__);
    }

    /**
     * Determines the image Height based on Image type
     *
     * @return void
     */
    private function determineImageHeight() : void
    {
        $this->startProfiling(__FUNCTION__);

        switch ($this->image['type']) {
            case FundImageController::FUNDING_ONLY:
                $this->image['height'] = 35;
                break;

            case FundImageController::FUNDING_AND_TEXT:
                $this->image['height'] = 75;
                break;

            case FundImageController::FUNDING_AND_BARS:
                $this->image['height'] = 41;
                break;
        }
        $this->addTrace("Image Height is: {$this->image['height']}", __FUNCTION__, __LINE__);

        $this->stopProfiling(__FUNCTION__);
    }

    /**
     * Initializes the image
     */
    private function initImage() : void
    {
        $this->startProfiling(__FUNCTION__);

        $this->image['pointer'] = imagecreatetruecolor(
            $this->image['width'],
            $this->image['height']
        );
        imagesavealpha($this->image['pointer'], true);

        $transparentColor = imagecolorallocatealpha(
            $this->image['pointer'],
            0,
            0,
            0,
            127
        );

        imagefill(
            $this->image['pointer'],
            0,
            0,
            $transparentColor
        );

        $this->stopProfiling(__FUNCTION__);
    }

    /**
     * Adds Text and Funds to the Image
     *
     * @return void
     */
    private function addDataToImage() : void
    {
        $this->startProfiling(__FUNCTION__);

        $this->addTrace("Adding Data to Image with Type: {$this->image['type']}", __FUNCTION__, __LINE__);

        $fontColor = $this->allocateColorFromFontArray();
        switch ($this->image['type']) {
            case FundImageController::FUNDING_AND_TEXT:
                imagettftext(
                    $this->image['pointer'],
                    25,
                    0,
                    0,
                    30,
                    $fontColor,
                    $this->font['path'],
                    $this->image['text']
                );
                imagettftext(
                    $this->image['pointer'],
                    25,
                    0,
                    2,
                    70,
                    $fontColor,
                    $this->font['path'],
                    $this->funds['currentFormatted']
                );
                break;

            case FundImageController::FUNDING_AND_BARS:
                $this->initBarImage();
                $fontColor = $this->allocateColorFromFontArray();
                imagestring(
                    $this->image['pointer'],
                    2,
                    0,
                    0,
                    $this->image['text'],
                    $fontColor
                );
                $this->addBarsToBarImage();
                break;

            case FundImageController::FUNDING_ONLY:
                imagettftext(
                    $this->image['pointer'],
                    20,
                    0,
                    2,
                    30,
                    $fontColor,
                    $this->font['path'],
                    $this->funds['currentFormatted']
                );
                break;
        }

        $this->stopProfiling(__FUNCTION__);
    }

    /**
     * Initializes the old 'bar-style' image
     *
     * @return void
     */
    private function initBarImage() : void
    {
        $this->startProfiling(__FUNCTION__);

        $this->font['color'] = FundImageController::COLORS['blue'];
        $this->roundFundsToNextMillion();
        $this->calculatePercentageToNextMillion();
        $this->image['text'] = 'Crowdfunding: '.$this->funds['currentFormatted'].' von '.$this->funds['nextMillionFormatted'].' ('.$this->funds['percentageToNextMillion'].'%)';

        $this->stopProfiling(__FUNCTION__);
    }

    /**
     * Adds filled and unfilled bars to the image
     *
     * @return void
     */
    private function addBarsToBarImage() : void
    {
        $this->startProfiling(__FUNCTION__);

        $this->font['color'] = FundImageController::COLORS['darkblue'];
        $darkBlue = $this->allocateColorFromFontArray();

        $this->font['color'] = FundImageController::COLORS['blue'];
        $blue = $this->allocateColorFromFontArray();

        for ($i = 0; $i <= 300; $i = $i + 5) {
            if ((($this->funds['nextMillion'] - $this->funds['substractor']) / 1000000) * 100 >= $i) {
                imageline($this->image['pointer'], $i, 15, $i, 40, $blue);
                imageline($this->image['pointer'], $i + 1, 15, $i + 1, 40, $blue);
                imageline($this->image['pointer'], $i + 2, 15, $i + 2, 40, $blue);
            } else {
                imageline($this->image['pointer'], $i, 15, $i, 40, $darkBlue);
                imageline($this->image['pointer'], $i + 1, 15, $i + 1, 40, $darkBlue);
                imageline($this->image['pointer'], $i + 2, 15, $i + 2, 40, $darkBlue);
            }
        }

        $this->stopProfiling(__FUNCTION__);
    }

    /**
     * Creates the font color from font array
     *
     * @return int
     */
    private function allocateColorFromFontArray()
    {
        return imagecolorallocate(
            $this->image['pointer'],
            $this->font['color'][0],
            $this->font['color'][1],
            $this->font['color'][2]
        );
    }

    /**
     * Flushes the generated image to a string and saves it in the 'data' key
     *
     * @return void
     */
    private function flushImageToString() : void
    {
        $this->startProfiling(__FUNCTION__);

        ob_start();
        imagepng($this->image['pointer']);
        $this->image['data'] = ob_get_contents();
        ob_end_clean();

        $this->stopProfiling(__FUNCTION__);
    }

    /**
     * Takes the image data and saves it to disk
     *
     * @return void
     */
    private function saveImageToDisk() : void
    {
        $this->startProfiling(__FUNCTION__);

        Storage::disk(FUNDIMAGE_DISK_SAVE_PATH)->put($this->image['name'], $this->image['data']);

        $this->stopProfiling(__FUNCTION__);
    }

    /**
     * Retrieves the image from disk
     *
     * @return mixed
     */
    private function loadImageFromDisk()
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return response()->file(
            storage_path(FUNDIMAGE_RELATIVE_SAVE_PATH.$this->image['name'])
        );
    }

    /**
     * Rounds the current funds to its next million
     *
     * @return void
     */
    private function roundFundsToNextMillion() : void
    {
        $currentFunds = $this->funds['current'] / 1000000;
        $this->funds['nextMillion'] = ceil($currentFunds) * 1000000;
        $this->formatFunds('nextMillion');
    }

    /**
     * Calculates the percent to next million based on current funds
     *
     * @throws InvalidDataException
     *
     * @return void
     */
    private function calculatePercentageToNextMillion() : void
    {
        if ($this->funds['nextMillion'] === null ||
            $this->funds['current'] === null) {
            throw new InvalidDataException('Did you call _roundFundsToNextMillion()?');
        }
        $this->funds['substractor'] = $this->funds['nextMillion'] - 1000000;
        $this->funds['percentageToNextMillion'] = round((($this->funds['current'] - $this->funds['substractor']) /
        ($this->funds['nextMillion'] - $this->funds['substractor'])) * 100);
    }
}
