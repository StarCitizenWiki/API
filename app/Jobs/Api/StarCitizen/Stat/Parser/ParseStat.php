<?php

declare(strict_types=1);

namespace App\Jobs\Api\StarCitizen\Stat\Parser;

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
     * @param string|null $statFileName
     */
    public function __construct(?string $statFileName = null)
    {
        if (null === $statFileName) {
            $timestamp = now()->format('Y-m-d');
            $statFileName = "stats_{$timestamp}.json";
        }

        $this->statFileName = $statFileName;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        app('Log')::info('Parsing Stat Download');
        $year = now()->year;

        try {
            $stat = json_decode(Storage::disk(self::STATS_DISK)->get(sprintf('%d/%s', $year, $this->statFileName)));
        } catch (FileNotFoundException $e) {
            app('Log')::error(
                "File {$this->statFileName} not found on Disk " . self::STATS_DISK,
                [
                    'message' => $e->getMessage(),
                ]
            );

            $this->fail($e);

            return;
        } catch (InvalidArgumentException $e) {
            app('Log')::error(
                "File {$this->statFileName} does not contain valid JSON",
                [
                    'message' => $e->getMessage(),
                ]
            );

            $this->fail($e);

            return;
        }

        // RSI liefert Funds als String ohne Dezimalpunkt aus, letzten beiden Zahlen sind Cent-Beträge der Funds
        $funds = substr_replace($stat->funds, '.', -2, 0);

        Stat::create(
            [
                'funds' => number_format((float)$funds, 2, '.', ''),
                'fans' => $stat->fans,
                'fleet' => $stat->fleet ?? $stat->fans,
            ]
        );
    }
}
