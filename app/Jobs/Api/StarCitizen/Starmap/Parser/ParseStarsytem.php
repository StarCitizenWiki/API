<?php
/**
 * User: Keonie
 * Date: 19.08.2018 21:01
 */

namespace App\Jobs\Api\StarCitizen\Starmap\Parser;

use App\Models\Api\StarCitizen\Starmap\Starsystem\Starsystem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

/**
 * Class ParseStarsytem
 * @package App\Jobs\Api\StarCitizen\Starmap\Parser
 */
class ParseStarsytem implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;


    /**
     * @var \Illuminate\Support\Collection
     */
    protected $rawData;

    /**
     * Create a new job instance.
     *
     * @param \Illuminate\Support\Collection $rawData
     */
    public function __construct(Collection $rawData)
    {
        $this->rawData = $rawData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        Starsystem::updateOrCreate(
            [
                'code'   => $this->rawData['code'],
                'cig_id' => $this->rawData['id'],
            ],
            [
                'status'                => $this->rawData['status'],
                'cig_time_modified'     => $this->rawData['time_modified'],
                'type'                  => $this->rawData['type'],
                'name'                  => $this->rawData['name'],
                'position_x'            => $this->rawData['position_x'],
                'position_y'            => $this->rawData['position_y'],
                'position_z'            => $this->rawData['position_z'],
                'info_url'              => $this->rawData['info_url'],
                'description'           => $this->rawData['description'],
                'affiliation_id'        => ParseAffiliation::getAffiliation($this->rawData['affiliation'][0]),
                'aggregated_size'       => $this->rawData['aggregated_size'],
                'aggregated_population' => $this->rawData['aggregated_population'],
                'aggregated_economy'    => $this->rawData['aggregated_economy'],
                'aggregated_danger'     => $this->rawData['aggregated_danger'],
            ]
        );
    }
}