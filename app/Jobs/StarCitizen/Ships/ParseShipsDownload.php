<?php declare(strict_types = 1);

namespace App\Jobs\StarCitizen\Ships;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Rodenastyle\StreamParser\StreamParser;

/**
 * Class ParseShipsDownload
 */
class ParseShipsDownload implements ShouldQueue
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
        StreamParser::json(Storage::disk('ships')->path($this->shipMatrixFileName))->each(
            function (Collection $ship) {
                dispatch(new ParseShip($ship));
            }
        );
    }
}
