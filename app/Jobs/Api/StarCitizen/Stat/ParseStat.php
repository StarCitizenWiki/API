<?php declare(strict_types = 1);

namespace App\Jobs\Api\StarCitizen\Stat;

use App\Models\Api\StarCitizen\Stat\Stat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

/**
 * Class ParseStat
 */
class ParseStat implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private const STATS_DISK = 'stats';

    private $statFileName;

    /**
     * Create a new job instance.
     *
     * @param string $statFileName
     */
    public function __construct(string $statFileName)
    {
        $this->statFileName = $statFileName;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app('Log')::info('Parsing Stat Download');

        try {
            $stat = json_decode(Storage::disk(self::STATS_DISK)->get($this->statFileName));
        } catch (FileNotFoundException $e) {
            app('Log')::error(
                "File {$this->statFileName} not found on Disk ".self::STATS_DISK,
                [
                    'message' => $e->getMessage(),
                ]
            );

            return;
        } catch (InvalidArgumentException $e) {
            app('Log')::error(
                "File {$this->statFileName} does not contain valid JSON",
                [
                    'message' => $e->getMessage(),
                ]
            );

            return;
        }

        // RSI liefert Funds als String ohne Dezimalpunkt aus, letzten beiden Zahlen sind Cent-Beträge der Funds
        $funds = substr_replace($stat->funds, '.', -2, 0);

        Stat::create(
            [
                'funds' => number_format((float) $funds, 2, '.', ''),
                'fans' => $stat->fans,
                'fleet' => $stat->fleet,
            ]
        );
    }
}
