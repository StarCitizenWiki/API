<?php declare(strict_types = 1);

namespace App\Console\Commands\CommLink\Import;

use App\Jobs\Rsi\CommLink\Download\DownloadMissingCommLinks;
use App\Jobs\Rsi\CommLink\Image\CreateImageHashes;
use App\Jobs\Rsi\CommLink\Image\CreateImageMetadata;
use App\Jobs\Rsi\CommLink\Parser\ParseCommLinkDownload;
use App\Jobs\Rsi\CommLink\Translate\TranslateCommLinks;
use App\Jobs\Wiki\CommLink\CreateCommLinkWikiPages;
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
    protected $description = 'Downloads missing Comm-Links. Creates Wiki-Pages and metadata.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $missingOffset = optional(CommLink::query()->orderByDesc('cig_id')->first())->cig_id ?? 0;
        if ($missingOffset > 0) {
            $missingOffset++;
        }

        DownloadMissingCommLinks::withChain(
            [
                new ParseCommLinkDownload($missingOffset),
                new TranslateCommLinks($missingOffset),
                new CreateCommLinkWikiPages(),
                new CreateImageMetadata($missingOffset),
                new CreateImageHashes($missingOffset),
            ]
        )->dispatch(new Client(), $missingOffset);

        return 0;
    }
}
