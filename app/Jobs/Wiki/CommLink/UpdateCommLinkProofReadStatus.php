<?php declare(strict_types = 1);

namespace App\Jobs\Wiki\CommLink;

use App\Models\Rsi\CommLink\CommLink;
use App\Traits\Jobs\GetCommLinkWikiPageInfoTrait as GetCommLinkWikiPageInfo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
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

    const CATEGORIES = 'categories';

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app('Log')::info('Starting Update of Proofread Status');

        $manager = app('mediawikiapi.manager');

        $manager->setConsumerFromCredentials(
            (string) config('services.wiki_translations.consumer_token'),
            (string) config('services.wiki_translations.consumer_secret')
        );
        $manager->setTokenFromCredentials(
            (string) config('services.wiki_translations.access_token'),
            (string) config('services.wiki_translations.access_secret')
        );

        $config = $this->getCommLinkConfig();

        CommLink::query()->chunk(
            100,
            function (Collection $commLinks) use ($config) {
                $commLinks = $commLinks->filter(
                    function (CommLink $commLink) {
                        return !empty($commLink->english()->translation);
                    }
                )->filter(
                    function (CommLink $commLink) {
                        return $commLink->german() !== null;
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
                    function (CommLink $commLink) use ($pageInfoCollection, $config) {
                        $wikiPage = $pageInfoCollection->get($commLink->cig_id, []);

                        if (isset($wikiPage[self::CATEGORIES])) {
                            $proofread = true;
                            collect($wikiPage[self::CATEGORIES])->each(
                                function (array $category) use (&$proofread, $config) {
                                    if (str_contains($category['title'], $config['category'])) {
                                        $proofread = false;
                                    }
                                }
                            );

                            $commLink->translations()->where(
                                [
                                    'locale_code' => 'de_DE',
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
