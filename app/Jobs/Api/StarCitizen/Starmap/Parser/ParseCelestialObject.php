<?php declare(strict_types = 1);
/**
 * User: Keonie
 * Date: 19.08.2018 21:01
 */

namespace App\Jobs\Api\StarCitizen\Starmap\Parser;

use App\Models\Api\StarCitizen\Starmap\CelestialObject\CelestialObject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

/**
 * Class ParseCelestialObject
 */
class ParseCelestialObject implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected const LANGUAGE_EN = 'en_EN';

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $rawData;

    /**
     * @var int Starsystem Id
     */
    private $starsystemId;

    /**
     * Create a new job instance.
     *
     * @param \Illuminate\Support\Collection $rawData
     * @param int                            $starsystemId
     */
    public function __construct(Collection $rawData, $starsystemId)
    {
        $this->rawData = $rawData;
        $this->starsystemId = $starsystemId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (empty($this->rawData['subtype'])) {
            app('Log')::warning("Parse Celestial Object: empty=true");
        }

        /** @var \App\Models\Api\StarCitizen\Starmap\CelestialObject\CelestialObject $celestialObject */
        $celestialObject = CelestialObject::updateOrCreate(
            [
                'code' => $this->rawData['code'],
                'starsystem_id' => $this->starsystemId,
            ],
            [
                'cig_id' => $this->rawData['id'],
                'cig_time_modified' => $this->rawData['time_modified'],
                'type' => $this->rawData['type'],
                'designation' => $this->rawData['designation'],
                'name' => $this->rawData['name'],
                'age' => $this->rawData['age'],
                'distance' => $this->rawData['distance'],
                'latitude' => $this->rawData['latitude'],
                'longitude' => $this->rawData['longitude'],
                'axial_tilt' => $this->rawData['axial_tilt'],
                'orbit_period' => $this->rawData['orbit_period'],
                'info_url' => $this->rawData['info_url'],
                'habitable' => $this->rawData['habitable'],
                'fairchanceact' => $this->rawData['fairchanceact'],
                'appearance' => $this->rawData['appearance'],
                'sensor_population' => $this->rawData['sensor_population'],
                'sensor_economy' => $this->rawData['sensor_economy'],
                'sensor_danger' => $this->rawData['sensor_danger'],
                'size' => $this->rawData['size'],
                'parent_id' => $this->rawData['parent_id'],
                'subtype_id' => !empty($this->rawData['subtype']) ?
                    ParseCelestialSubtype::getCelestialSubtype($this->rawData['subtype']) : null,
                'affiliation_id' => !empty($this->rawData['affiliation']) ?
                    ParseAffiliation::getAffiliation($this->rawData['affiliation'][0]) : null,
            ]
        );

        $celestialObject->translations()->updateOrCreate(
            [
                'celestial_object_id' => $celestialObject->id,
                'locale_code' => self::LANGUAGE_EN,
            ],
            [
                'translation' => !empty($this->rawData['description']) ? $this->rawData['description'] : "",
            ]
        );
    }
}
