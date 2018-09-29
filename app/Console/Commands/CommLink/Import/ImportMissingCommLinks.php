<?php declare(strict_types = 1);

namespace App\Console\Commands\CommLink\Import;

use App\Jobs\Rsi\CommLink\DownloadMissingCommLinks;
use App\Jobs\Rsi\CommLink\Parser\ParseCommLinkDownload;
use App\Models\Rsi\CommLink\CommLink;
use Goutte\Client;
use Illuminate\Console\Command;

class ImportMissingCommLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:missing-comm-links';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Missing Comm Links';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $missingOffset = optional(CommLink::query()->orderByDesc('cig_id')->first())->cig_id ?? 0;
        if ($missingOffset > 0) {
            $missingOffset++;
        }

        DownloadMissingCommLinks::withChain(
            [
                new ParseCommLinkDownload($missingOffset),
            ]
        )->dispatch(new Client(), $missingOffset);
    }
}
