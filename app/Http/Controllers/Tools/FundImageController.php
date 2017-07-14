<?php

namespace App\Http\Controllers\Tools;

use App\Exceptions\InvalidDataException;
use App\Exceptions\MissingExtensionException;
use App\Facades\Log;
use App\Http\Controllers\Controller;
use App\Repositories\StarCitizen\APIv1\StatsRepository;
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
        parent::__construct();
        Log::debug('Setting defaults');
        $this->checkIfImageCanBeCreated();
        $this->request = $request;
        $this->repository = $repository;
        $this->font['path'] = resource_path(
            'assets/fonts/orbitron-light-webfont.ttf'
        );
        $this->font['color'] = FundImageController::COLORS['black'];
        Log::debug('Finished setting defaults');
    }

    /**
     * Sets the Image Type to FUNDING_AND_TEXT
     *
     * @return mixed
     */
    public function getImageWithText()
    {
        $this->image['type'] = FundImageController::FUNDING_AND_TEXT;
        Log::debug('Setting Image Type to '.FUNDIMAGE_FUNDING_AND_TEXT);

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
        Log::debug('Setting Image Type to '.FUNDIMAGE_FUNDING_AND_BARS);

        return $this->getImage();
    }

    /**
     * Generates the Image with the defined Values
     * @return mixed
     * @throws \Exception
     */
    public function getImage()
    {
        Log::debug('Starting Fund Image Request');
        try {
            $this->setImageType();
        } catch (InvalidArgumentException $e) {
            abort(400, $e->getMessage());
        }

        $this->setFontColorFromRequest();
        $this->assembleFilename();

        if ($this->checkIfImageCanBeLoadedFromCache()) {
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
            throw new Exception('Fund Image generation failed');
        }

        Log::notice('Fund Image Requested', [
            'type' => $this->image['type'],
            'name' => $this->image['name'],
            'requester' => $this->request->getHost(),
        ]);

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
        Log::debug('Checking if Image can be created');
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
        Log::debug('Setting Image Type');
        $action = Route::getCurrentRoute()->getAction()['type'];
        if (in_array($action, FundImageController::SUPPORTED_FUNDS)) {
            $this->image['type'] = Route::getCurrentRoute()->getAction()['type'];
        } else {
            $message = 'FundImage function only accepts Supported Image Types('.
                        implode(', ', FundImageController::SUPPORTED_FUNDS).'). Input was: '.
                        Route::getCurrentRoute()->getAction()['type'];
            Log::warning('Requested Image type does not exist', [
                'message' => $message,
            ]);
            throw new InvalidArgumentException($message);
        }
    }

    /**
     * Checks if the request contains a color field and tries to parse it
     */
    private function setFontColorFromRequest()
    {
        Log::debug('Getting Font Color from request');
        $requestColor = $this->request->get('color');
        if (!is_null($requestColor) && !empty($requestColor)) {
            Log::debug('Requested Color is '.$requestColor);
            $colorArray = $this->convertHexToRGBColor($requestColor);
            if (!empty($colorArray)) {
                $this->font['color'] = $colorArray;
            }
            Log::debug('Finished converting and setting Font Color');
        }
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

        return $rgbArray;
    }

    /**
     * Generates the Filename based on the request
     *
     * @return void
     */
    private function assembleFilename() : void
    {
        Log::debug('Starting Filename Assembly');
        $color = implode('', $this->font['color']);
        $this->image['name'] = $this->image['type'].'_'.$color.'.png';
        Log::debug('Finished Filename Assembly', [
            'name' => $this->image['name'],
        ]);
    }

    /**
     * Checks it the requested Image is already cached and new enough
     *
     * @return bool
     */
    private function checkIfImageCanBeLoadedFromCache() : bool
    {
        Log::debug('Starting Check if Image can be loaded from Cache');
        if (Storage::disk(FUNDIMAGE_DISK_SAVE_PATH)->exists($this->image['name'])) {
            Log::debug('Image Exists in Cache');
            $imageCreationTime = Storage::disk(FUNDIMAGE_DISK_SAVE_PATH)->lastModified($this->image['name']);
            $cacheDuration = time() - FUNDIMAGE_CACHE_TIME;
            if ($imageCreationTime > $cacheDuration) {
                Log::debug('Image is valid and can be loaded');

                return true;
            }
        }

        return false;
    }

    /**
     * Requests the API and saves the funds
     *
     * @return void
     */
    private function getFundsFromAPI() : void
    {
        Log::debug('Starting Funds Request from Repository');
        $funds = $this->repository->getFunds()->asArray();
        $this->funds['current'] = substr($funds['data']['funds'], 0, -2);
        Log::debug('Finished getting Funds from Repository', [
            'funds' => $this->funds['current'],
        ]);
    }

    /**
     * Formates the funds and appends a dollar sign
     *
     * @param string $source Array Key to use the funds from
     */
    private function formatFunds($source = 'current') : void
    {
        if ($source !== 'current') {
            $source = 'nextMillion';
        }

        Log::debug('Formatting funds', [
            'source' => $source,
        ]);

        $this->funds[$source.'Formatted'] = number_format(
            $this->funds[$source],
            0,
            ',',
            '.'
        ).' $';

        Log::debug('Funds formatted', [
            'formatted' => $this->funds[$source.'Formatted'],
        ]);
    }

    /**
     * Determines the image Width based on Image type
     *
     * @return void
     */
    private function determineImageWidth() : void
    {
        Log::debug('Starting Image Width Determination');
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
        Log::debug('Finished Image Width Determination', [
            'width' => $this->image['width'],
        ]);
    }

    /**
     * Determines the image Height based on Image type
     *
     * @return void
     */
    private function determineImageHeight() : void
    {
        Log::debug('Starting Image Height Determination');
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
        Log::debug('Finished Image Height Determination', [
            'height' => $this->image['height'],
        ]);
    }

    /**
     * Initializes the image
     */
    private function initImage() : void
    {
        Log::debug('Initializing Image');
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
        Log::debug('Image initialized');
    }

    /**
     * Adds Text and Funds to the Image
     *
     * @return void
     */
    private function addDataToImage() : void
    {
        Log::debug('Adding Data to Image', [
            'type' => $this->image['type'],
        ]);
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
        Log::debug('Data added');
    }

    /**
     * Initializes the old 'bar-style' image
     *
     * @return void
     */
    private function initBarImage() : void
    {
        $this->font['color'] = FundImageController::COLORS['blue'];
        $this->roundFundsToNextMillion();
        $this->calculatePercentageToNextMillion();
        $this->image['text'] = 'Crowdfunding: '.$this->funds['currentFormatted'].' von '.$this->funds['nextMillionFormatted'].' ('.$this->funds['percentageToNextMillion'].'%)';
    }

    /**
     * Adds filled and unfilled bars to the image
     *
     * @return void
     */
    private function addBarsToBarImage() : void
    {
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
        Log::debug('Flushing Image to String');
        ob_start();
        imagepng($this->image['pointer']);
        $this->image['data'] = ob_get_contents();
        ob_end_clean();
        Log::debug('Finished Flushing Image');
    }

    /**
     * Takes the image data and saves it to disk
     *
     * @return void
     */
    private function saveImageToDisk() : void
    {
        Log::debug('Starting saving Image to disk');
        Storage::disk(FUNDIMAGE_DISK_SAVE_PATH)->put($this->image['name'], $this->image['data']);
        Log::debug('Finished saving Image to disk');
    }

    /**
     * Retrieves the image from disk
     *
     * @return mixed
     */
    private function loadImageFromDisk()
    {
        Log::debug('Requesting Image from disk');

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
