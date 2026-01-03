<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizen\Stat;

use App\Models\StarCitizen\Stat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use JsonException;

/**
 * Class ParseStat
 */
class ImportStat implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private const STATS_DISK = 'stats';

    private string $statFileName;

    private int $year;

    /**
     * Create a new job instance.
     */
    public function __construct(?string $statFileName = null, ?int $year = null)
    {
        $this->statFileName = $statFileName ?? sprintf('stats_%s.json', now()->format('Y-m-d'));
        $this->year = $year ?? $this->inferYear($this->statFileName);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        app('Log')::info('Parsing Stat Download');

        try {
            $content = Storage::disk(self::STATS_DISK)->get(sprintf('%d/%s', $this->year, $this->statFileName));
            if ($content === null) {
                throw new FileNotFoundException;
            }

            $stat = json_decode(
                $content,
                false,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (FileNotFoundException $e) {
            app('Log')::error(
                "File {$this->statFileName} not found on Disk ".self::STATS_DISK,
                [
                    'message' => $e->getMessage(),
                ]
            );

            $this->fail($e);

            return;
        } catch (JsonException $e) {
            app('Log')::error(
                "File {$this->statFileName} does not contain valid JSON",
                [
                    'message' => $e->getMessage(),
                ]
            );

            $this->delete();

            return;
        }

        // RSI liefert Funds als String ohne Dezimalpunkt aus, letzten beiden Zahlen sind Cent-Beträge der Funds
        $funds = substr_replace((string) $stat->funds, '.', -2, 0);

        Stat::create(
            [
                'funds' => number_format((float) $funds, 2, '.', ''),
                'fans' => $stat->fans,
                'fleet' => $stat->fleet ?? $stat->fans,
            ]
        );
    }

    private function inferYear(string $statFileName): int
    {
        if (preg_match('/^stats_(\d{4})-\d{2}-\d{2}\.json$/', $statFileName, $matches) === 1) {
            return (int) $matches[1];
        }

        return now()->year;
    }
}
