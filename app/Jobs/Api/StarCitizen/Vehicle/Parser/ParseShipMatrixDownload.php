<?php declare(strict_types = 1);

namespace App\Jobs\Api\StarCitizen\Vehicle\Parser;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Rodenastyle\StreamParser\StreamParser;
use Tightenco\Collect\Support\Collection;

/**
 * Class ParseShipsDownload
 */
class ParseShipMatrixDownload implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $shipMatrixFileName;

    /**
     * Create a new job instance.
     *
     * @param string $shipMatrixFileName
     */
    public function __construct(string $shipMatrixFileName)
    {
        $this->shipMatrixFileName = $shipMatrixFileName;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app('Log')::info('Parsing Download');
        StreamParser::json(Storage::disk('vehicles')->path($this->shipMatrixFileName))->each(
            function (Collection $vehicle) {
                dispatch(new ParseVehicle($vehicle));
            }
        );
    }
}
