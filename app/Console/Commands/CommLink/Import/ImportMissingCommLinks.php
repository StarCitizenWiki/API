<?php

declare(strict_types=1);

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
    protected $signature = 'comm-links:import-missing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download missing Comm-Links. Parse the download, create metadata and hashes. ' .
    'If a DeepL API key is set, Comm-Links will be translated. ' .
    'If a MediaWiki Account is configured, Wiki Comm-Link pages will be created';

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

        $chain = [
            new ParseCommLinkDownload($missingOffset),
            new CreateImageMetadata($missingOffset),
            new CreateImageHashes($missingOffset),
        ];

        if (config('services.deepl.auth_key', null) !== null) {
            $chain[] = new TranslateCommLinks($missingOffset);
        }

        $clientNotNull = config('services.mediawiki.client_id', null) !== null;
        $apiUrlNotNull = config('mediawiki.api_url', null) !== null;

        if ($clientNotNull && $apiUrlNotNull) {
            $chain[] = new CreateCommLinkWikiPages();
        }

        DownloadMissingCommLinks::withChain($chain)
            ->dispatch(new Client(), $missingOffset);

        return 0;
    }
}
