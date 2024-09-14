<?php

declare(strict_types=1);

namespace App\Jobs\Rsi\CommLink\Translate;

use App\Models\Rsi\CommLink\CommLink;
use App\Models\System\Language;
use App\Traits\Jobs\GetCommLinkWikiPageInfoTrait as GetCommLinkWikiPageInfo;
use App\Traits\LoginWikiBotAccountTrait as LoginWikiBotAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;

/**
 * Translate new Comm-Links
 */
class TranslateCommLinks implements ShouldQueue
{
    use Dispatchable;
    use GetCommLinkWikiPageInfo;
    use InteractsWithQueue;
    use LoginWikiBotAccount;
    use Queueable;
    use SerializesModels;

    /**
     * @var int Comm-Link IDs to operate on
     */
    private $commLinkIds;

    /**
     * Create a new job instance.
     */
    public function __construct(array $commLinkIds = [])
    {
        $this->commLinkIds = $commLinkIds;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        app('Log')::info('Starting Comm-Link Translations');

        $this->loginWikiBotAccount('services.wiki_translations');

        CommLink::query()->whereHas(
            'translations',
            function (Builder $query) {
                $query->where('locale_code', Language::ENGLISH)->whereRaw("translation <> ''");
            }
        )
            ->whereIn('cig_id', $this->commLinkIds)
            ->chunk(
                100,
                function (Collection $commLinks) {
                    try {
                        $pageInfoCollection = $this->getPageInfoForCommLinks($commLinks, true);
                    } catch (RuntimeException $e) {
                        app('Log')::error($e->getMessage());

                        if (str_contains($e->getMessage(), 'Guru Meditation')) {
                            $this->release(60);
                        } else {
                            $this->fail($e);
                        }

                        return;
                    }

                    $commLinks->each(
                        function (CommLink $commLink) use ($pageInfoCollection) {
                            $wikiPage = $pageInfoCollection->get($commLink->cig_id, []);

                            if (isset($wikiPage['missing'])) {
                                dispatch(new TranslateCommLink($commLink));
                            }
                        }
                    );

                    usleep(100000);
                }
            );
    }
}
