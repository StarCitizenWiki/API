<?php

declare(strict_types=1);

namespace App\Jobs\Rsi\CommLink\Import;

use App\Jobs\Rsi\CommLink\Image\CreateImageHashes;
use App\Jobs\Rsi\CommLink\Image\CreateImageMetadata;
use App\Jobs\Rsi\CommLink\Translate\TranslateCommLinks;
use App\Jobs\Wiki\CommLink\CreateCommLinkWikiPages;
use App\Models\Rsi\CommLink\CommLink;
use App\Traits\Jobs\GetFoldersTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

/**
 * Dispatches a ParseCommLink Job for the newest file in every Comm-Link Folder.
 */
class ImportCommLinks implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use GetFoldersTrait;

    /**
     * @var int Offset to start parsing from
     */
    private int $modifiedFolderTime;

    /**
     * Create a new job instance.
     *
     * @param int $modifiedFolderTime Include folders that were created in the last x minutes. -1 = all
     */
    public function __construct(int $modifiedFolderTime = 5)
    {
        $this->modifiedFolderTime = $modifiedFolderTime;
    }

    /**
     * Import Comm-Links that where created in the last modifiedFolderTime minutes
     * Also create Metadata, Image Hashes, Translations and Wiki Pages for each imported Comm-Link
     */
    public function handle(): void
    {
        $commLinks = CommLink::query()->get();
        $commLinks = $commLinks->keyBy('cig_id');

        $newCommLinkIds = $this->filterDirectories('comm_links', $this->modifiedFolderTime)
            ->each(
                function ($commLinkDir) use ($commLinks) {
                    $file = Arr::last(Storage::disk('comm_links')->files($commLinkDir));

                    if (null !== $file) {
                        $file = preg_split('/\/|\\\/', $file);
                        $commLink = $commLinks->get((int)$commLinkDir, null);

                        dispatch(new ImportCommLink((int)$commLinkDir, Arr::last($file), $commLink));
                    }
                }
            )
            ->map(
                function ($directory) {
                    return (int)$directory;
                }
            )
            ->toArray();

        $this->dispatchChain($newCommLinkIds);
    }

    /**
     * Create Metadata, Image Hashes, Translations and Wiki Pages
     *
     * @param array $commLinkIds
     */
    private function dispatchChain(array $commLinkIds): void
    {
        CreateImageMetadata::withChain(
            [
                new CreateImageHashes($commLinkIds),
            ]
        )->dispatch($commLinkIds);

        if (config('services.deepl.auth_key', null) !== null) {
            dispatch(new TranslateCommLinks($commLinkIds));
        }

        $clientNotNull = config('services.mediawiki.client_id') !== null;
        $apiUrlNotNull = config('mediawiki.api_url') !== null;

        if ($clientNotNull && $apiUrlNotNull) {
            dispatch(new CreateCommLinkWikiPages())->delay(90);
        }

        Artisan::call('comm-links:compute-similar-image-ids --recent');
    }
}
