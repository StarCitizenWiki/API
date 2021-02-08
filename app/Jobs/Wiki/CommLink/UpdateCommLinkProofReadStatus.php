<?php

declare(strict_types=1);

namespace App\Jobs\Wiki\CommLink;

use App\Models\Rsi\CommLink\CommLink;
use App\Traits\Jobs\GetCommLinkWikiPageInfoTrait as GetCommLinkWikiPageInfo;
use App\Traits\LoginWikiBotAccountTrait as LoginWikiBotAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Update the Proof Read Status of Comm-Link Translations
 */
class UpdateCommLinkProofReadStatus implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use GetCommLinkWikiPageInfo;
    use LoginWikiBotAccount;

    private const CATEGORIES = 'categories';
    private const LOCALE_CODE = 'locale_code';

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app('Log')::info('Starting Update of Proofread Status');

        $this->loginWikiBotAccount('services.wiki_translations');

        $config = $this->getCommLinkConfig();

        CommLink::query()->whereHas(
            'translations',
            static function (Builder $query) {
                $query->where(self::LOCALE_CODE, 'de_DE')->whereRaw("translation <> ''");
            }
        )->chunk(
            100,
            function (Collection $commLinks) use ($config) {
                try {
                    $pageInfoCollection = $this->getPageInfoForCommLinks($commLinks, true);
                } catch (\RuntimeException $e) {
                    app('Log')::error($e->getMessage());

                    $this->fail($e);

                    return;
                }

                $commLinks->each(
                    static function (CommLink $commLink) use ($pageInfoCollection, $config) {
                        $wikiPage = $pageInfoCollection->get($commLink->cig_id, []);

                        app('Log')::info("Updating Proofread Status for Comm-Link: {$commLink->cig_id}");

                        app('Log')::debug('Wiki Page Data', $wikiPage);

                        if (isset($wikiPage[self::CATEGORIES])) {
                            $proofread = true;
                            collect($wikiPage[self::CATEGORIES])->each(
                                static function (array $category) use (&$proofread, $config) {
                                    if (str_contains($category['title'], $config['category'])) {
                                        $proofread = false;
                                    }
                                }
                            );

                            $commLink->translations()->where(
                                [
                                    self::LOCALE_CODE => 'de_DE',
                                ]
                            )->update(
                                [
                                    'proofread' => $proofread,
                                ]
                            );
                        }
                    }
                );
            }
        );
    }
}
