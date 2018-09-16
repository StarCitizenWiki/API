<?php
/**
 * User: Keonie
 * Date: 19.08.2018 19:58
 */

namespace App\Jobs\Api\StarCitizen\Starmap\Parser;

use App\Jobs\Api\StarCitizen\Starmap\DownloadStarmap;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use function GuzzleHttp\json_decode;

/**
 * Class ParseStarmapDownload
 * @package App\Jobs\Api\StarCitizen\Starmap\Parser
 */
class ParseStarmapDownload implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    const OVERVIEWDATA_CHECKLIST = ['data', 'systems', 'resultset', 0];
    const CELESTIALOBJECTS_CHECKLIST = ['data', 'resultset', 0, 'celestial_objects', 0];

    private $starmapFolder;
    private $starmapFiles = [];
    private $starmapBootupFile;

    private const STARSYSTEM_DISK = 'starsystem';

    public function __construct(?string $starmapFileName = null)
    {
        if (null !== $starmapFileName) {
            $this->starmapFileName = $starmapFileName;
        } else {
            $this->starmapFolder = $this->getNewestStarmapFolder();
            $diskPath = Storage::disk(self::STARSYSTEM_DISK)->path('/'.$this->starmapFolder);
//            $diskPath = Storage::disk('starmap/'.$this->starmapFolder)->path('');
            $files = scandir($diskPath, SCANDIR_SORT_DESCENDING);

            if (is_array($files)) {
                foreach ($files as $file) {
                    if (0 === strcmp($file, DownloadStarmap::STARMAP_BOOTUP_FILENAME)) {
                        $this->starmapBootupFile = $file;
                    } else {
                        $this->starmapFiles[$file.""] = $file;
                    }
                }
            } else {
                app('Log')::error("No Starmap Files on Disk 'starmap\{$this->starmapFolder}' found");
                $this->fail();
            }
        }
    }

    private function getNewestStarmapFolder()
    {
        $diskPath = Storage::disk(self::STARSYSTEM_DISK)->path('');

        $folders = scandir($diskPath, SCANDIR_SORT_DESCENDING);
        $newestStarmapFolder = null;
        if (is_array($folders)) {
            return $folders[0];
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app('Log')::info('Parsing Starmap Download');

        try {
            $bootup = Storage::disk(self::STARSYSTEM_DISK)->get('/'.$this->starmapFolder . '/'.
                                                               $this->starmapBootupFile);
            $starmaps = json_decode($bootup, true);
        } catch (FileNotFoundException $e) {
            app('Log')::error(
                "File {$this->starmapBootupFile} not found",
                [
                    'message' => $e->getMessage(),
                ]
            );

            return;
        } catch (InvalidArgumentException $e) {
            app('Log')::error(
                "File {$this->starmapBootupFile} does not contain valid JSON",
                [
                    'message' => $e->getMessage(),
                ]
            );

            return;
        }

        if ($this->checkIfDataCanBeProcessed($starmaps, static::OVERVIEWDATA_CHECKLIST)) {
            $starsystems = $starmaps['data']['systems']['resultset'];
        } else {
            app('Log')::error('Can not read Starsystems from RawData');
            return;
        }


        foreach ($starsystems as $starsystem) {
            try {
                dispatch(new ParseStarsytem(new Collection($starsystem)));
                $this->handleCelestialObjects($starsystem);
            } catch (FileNotFoundException $e) {
                app('Log')::error(
                    "File {$starsystem}_system.json not found",
                    [
                        'message' => $e->getMessage(),
                    ]
                );

                return;
            } catch (InvalidArgumentException $e) {
                app('Log')::error(
                    "File {$starsystem}_system.json does not contain valid JSON",
                    [
                        'message' => $e->getMessage(),
                    ]
                );

                return;
            }
        }
    }

    private function handleCelestialObjects($starsystem) {

        try {
            $starsystemFile = Storage::disk(self::STARSYSTEM_DISK)->get('/'.$this->starmapFolder . '/'.
                                                                            $starsystem['code']."_system.json");
            $celestialObjects = json_decode($starsystemFile, true);
//            $starsystemData = json_decode($this->starmapFiles[$starsystem['code']."_system.json"], true);
        } catch (InvalidArgumentException $e) {
            app('Log')::error(
                "File {$this->starmapFiles[$starsystem]} does not contain valid JSON",
                [
                    'message' => $e->getMessage(),
                ]
            );

            return;
        }

//        if ($this->checkArrayStructure($starsystemData, static::CELESTIALOBJECTS_CHECKLIST)) {
//            $celestialObjects = $starsystemData['data']['resultset'][0]['celestial_objects'];
//            //TODO Celestial Subobjects in Files laden
//            //$allCelestialObjects = $this->addCelestialSubobjects($celestialObjects);
//        } else {
//            app('Log')::error("Can not read System {$starsystem} from RSI");
//            return;
//        }

        foreach ($celestialObjects as $celestialObject) {
            dispatch(new ParseCelestialObject(new Collection($celestialObject), $starsystem['id']));
        }
    }

    /**
     * Check if Data is successful, and if Data contains the check Array values in is structure
     * e.g. for check ['data, 'resultset'], data hs to contain the key 'data' with an array value,
     * which contains a key with 'resultset'
     *
     * @param array $data  Checked Array
     * @param array $check List of Keys that are checked
     *
     * @return bool true when all Elements of $check in $data and success = 1, otherwise false
     */
    protected function checkIfDataCanBeProcessed($data, $check): bool
    {
        if (is_array($data) && $data['success'] === 1) {
            return $this->checkArrayStructure($data, $check);
        }

        return false;
    }

    /**
     * Recursive Check of Array Structure
     *
     * @param array $data  Checked Array
     * @param array $check List of Keys that are checked
     *
     * @return bool true when all Elements of $check in $data, otherwise false
     */
    protected function checkArrayStructure($data, $check)
    {
        if (!empty($check) && !empty($data)) {
            if (array_key_exists($check[0], $data)) {
                $checkKey = array_shift($check);

                return $this->checkArrayStructure($data[$checkKey], $check);
            } else {
                return false;
            }
        }

        return true;
    }
}