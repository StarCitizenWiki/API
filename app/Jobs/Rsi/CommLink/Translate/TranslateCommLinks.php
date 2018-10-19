<?php declare(strict_types = 1);

namespace App\Jobs\Rsi\CommLink\Translate;

use App\Models\Rsi\CommLink\CommLink;
use App\Traits\Jobs\GetCommLinkWikiPageInfoTrait as GetCommLinkWikiPageInfo;
use App\Traits\Jobs\LoginWikiBotAccountTrait as LoginWikiBotAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Translate new Comm-Links
 */
class TranslateCommLinks implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use GetCommLinkWikiPageInfo;
    use LoginWikiBotAccount;

    private $offset;

    /**
     * Create a new job instance.
     *
     * @param int $offset
     */
    public function __construct(int $offset = 0)
    {
        $this->offset = $offset;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app('Log')::info('Starting Comm-Link Translations');

        $this->loginWikiBotAccount();

        CommLink::query()->where('cig_id', '>=', $this->offset)->chunk(
            100,
            function (Collection $commLinks) {
                $commLinks = $commLinks->filter(
                    function (CommLink $commLink) {
                        return !empty($commLink->english()->translation);
                    }
                );

                try {
                    $pageInfoCollection = $this->getPageInfoForCommLinks($commLinks, true);
                } catch (\RuntimeException $e) {
                    app('Log')::error($e->getMessage());

                    $this->fail($e);

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
            }
        );
    }
}
