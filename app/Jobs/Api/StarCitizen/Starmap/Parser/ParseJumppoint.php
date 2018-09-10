<?php
/**
 * User: Keonie
 * Date: 19.08.2018 21:01
 */

namespace App\Jobs\Api\StarCitizen\Starmap\Parser;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Bus\Queueable;

/**
 * Class ParseJumppoint
 * @package App\Jobs\Api\StarCitizen\Starmap\Parser
 */
class ParseJumppoint implements ShouldQueue
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
    public function handle()
    {

    }
}