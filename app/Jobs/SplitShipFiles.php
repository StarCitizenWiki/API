<?php

namespace App\Jobs;

use App\Exceptions\InvalidDataException;
use App\Transformers\StarCitizenDB\ShipsTransformer;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\Fractal\Fractal;

/**
 * Class SplitShipFiles
 * @package App\Jobs
 */
class SplitShipFiles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var array
     */
    private $content;

    /**
     * @var array
     */
    private $baseVersion;

    /**
     * @var \Spatie\Fractalistic\Fractal
     */
    private $fractalManager;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->fractalManager = Fractal::create();
        $this->fractalManager->transformWith(ShipsTransformer::class);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() : void
    {
        Log::info('Starting Split Ship Files Job');
        foreach (File::allFiles(config('filesystems.disks.scdb_ships.root')) as $ship) {
            $this->setContent((String) $ship);

            try {
                $this->checkIfShipHasNameField();
                $this->getDataForBaseVersion();
                if ($this->checkIfShipHasModifications()) {
                    Log::debug('Ship has Modifications');
                    foreach ($this->content['Modifications'] as $modification) {
                        if ($this->checkIfModificationIsValid($modification)) {
                            $this->content = $modification;
                            $this->prepareModificationArray();
                            $this->getDataForModifications();
                        }
                    }
                }
            } catch (InvalidDataException $e) {
                Log::warning('Ship has no Name field', [
                    'method' => __METHOD__,
                    'file' => $ship,
                ]);
            }
        }
        Log::info('Finished Split Ship Files Job');
    }

    /**
     * Gets the ships file content and parses it
     *
     * @param String $data
     */
    private function setContent(String $data) : void
    {
        $this->content = (String) File::get($data);
        $this->content = json_decode($this->content, true);
    }

    /**
     * @throws InvalidDataException
     */
    private function checkIfShipHasNameField() : void
    {
        if (!isset($this->content['@name']) || empty($this->content['@name'])) {
            throw new InvalidDataException('Name field is missing or empty');
        }
    }

    /**
     * Checks if a Modifications key is present in the content array
     *
     * @return bool
     */
    private function checkIfShipHasModifications() : bool
    {
        return isset($this->content['Modifications']) ?? false;
    }

    /**
     * @param array $modification
     *
     * @return bool
     */
    private function checkIfModificationIsValid(array $modification) : bool
    {
        return isset($modification['@patchFile']) &&
               isset($modification['mod']) &&
               isset($modification['ifcs']);
    }

    /**
     * Transformes the data and sets a base version
     */
    private function getDataForBaseVersion() : void
    {
        Log::info('Processing '.$this->content['@name']);

        $baseVersion = $this->content;
        unset($baseVersion['Modifications']);

        $collectedData = $this->fractalManager->item($baseVersion)->toArray()['data'];

        $this->saveDataToDisk($collectedData);

        $this->baseVersion = $collectedData;
    }

    /**
     * Transformes the Modification Data and merges it with the base version
     */
    private function getDataForModifications() : void
    {
        Log::info('Processing Modification '.$this->getShipNameForModification());

        $collectedData = $this->fractalManager->item($this->content)->toArray()['data'];

        $collectedData = $this->filterModificationArray($collectedData);

        $collectedData = array_merge($this->baseVersion, $collectedData);

        $this->saveDataToDisk($collectedData);
    }

    /**
     * Flattens the Modification array
     */
    private function prepareModificationArray() : void
    {
        $mod = $this->content['mod'];
        unset($this->content['mod']);
        $this->content = array_merge($this->content, $mod);
        $this->content['@name'] = $this->getShipNameForModification();
        $this->content['@manufacturer'] = $this->content['@manufacturer'] ?? '';
    }

    /**
     * @return String
     * @throws InvalidDataException
     */
    private function getShipNameForModification() : String
    {
        if (isset($this->content['@patchFile'])) {
            return str_replace('Modifications/', '', $this->content['@patchFile']);
        }
        throw new InvalidDataException('Data Seems to be for Baseversion '.$this->content['@name']);
    }

    /**
     * @param $array
     *
     * @return array
     */
    private function filterModificationArray(&$array) : array
    {
        foreach ($array as $key => $item) {
            if (is_array($item)) {
                $array[$key] = $this->filterModificationArray($item);
            }
            if (empty($array[$key])) {
                unset($array[$key]);
            }
        }

        return $array;
    }

    /**
     * @param array $content
     */
    private function saveDataToDisk(array $content) : void
    {
        $content['name'] = $this->prepareFilename($content['name']);

        Log::info('Saving '.$content['name']);
        Storage::disk('scdb_ships_splitted')->put(
            $content['name'].'.json',
            json_encode($content, JSON_PRETTY_PRINT)
        );
    }

    /**
     * Adjusts the Filename to Match the Wiki Site Name
     *
     * @param String $name
     *
     * @return String
     */
    private function prepareFilename(String $name) : String
    {
        $names = [
            'ORIG_m50' => 'ORIG_M50_Interceptor',
        ];

        return $names[$name] ?? $name;
    }
}
