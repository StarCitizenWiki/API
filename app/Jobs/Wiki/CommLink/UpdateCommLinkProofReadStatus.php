<?php

namespace App\Jobs\Wiki\CommLink;

use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use StarCitizenWiki\MediaWikiApi\Api\Response\MediaWikiResponse;
use StarCitizenWiki\MediaWikiApi\Facades\MediaWikiApi;

class UpdateCommLinkProofReadStatus implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    const CLEANUP_CATEGORY = 'Beitragsüberprüfung';
    const CATEGORIES = 'categories';

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $manager = app('mediawikiapi.manager');

        $manager->setConsumerFromCredentials(
            (string) config('services.wiki_translations.access_token'),
            (string) config('services.wiki_translations.access_secret')
        );
        $manager->setTokenFromCredentials(
            (string) config('services.wiki_translations.consumer_token'),
            (string) config('services.wiki_translations.consumer_secret')
        );

        CommLink::query()->chunk(
            100,
            function (Collection $commLinks) {
                $commLinks = $commLinks->filter(
                    function (CommLink $commLink) {
                        return !empty($commLink->english()->translation);
                    }
                );

                $pages = $commLinks->map(
                    function (CommLink $commLink) {
                        return sprintf('%s:%d', 'Comm-Link', $commLink->cig_id);
                    }
                )->implode('|');


                $res = $this->getMediaWikiQuery($pages);


                $res = collect($res->getQuery()['pages'])->keyBy(
                    function (array $value) {
                        return str_replace('Comm-Link:', '', $value['title']);
                    }
                );

                $commLinks->each(
                    function (CommLink $commLink) use ($res) {
                        $wikiPage = $res->get($commLink->cig_id, []);

                        if (isset($wikiPage[self::CATEGORIES])) {
                            $proofread = true;
                            collect($wikiPage[self::CATEGORIES])->each(
                                function (array $category) use (&$proofread) {
                                    if (str_contains($category['title'], self::CLEANUP_CATEGORY)) {
                                        $proofread = false;
                                    }
                                }
                            );

                            $commLink->translations()->updateOrCreate(
                                [
                                    'locale_code' => 'de_DE',
                                ],
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

    /**
     * Query the Wiki for given Pages
     *
     * @param string $pages
     *
     * @return \StarCitizenWiki\MediaWikiApi\Api\Response\MediaWikiResponse
     */
    private function getMediaWikiQuery(string $pages): MediaWikiResponse
    {
        $res = MediaWikiApi::query()->prop('info')->prop(self::CATEGORIES)->titles($pages)->request();

        if ($res->hasErrors()) {
            $this->fail(
                new \RuntimeException(
                    sprintf(
                        '%s: "%s"',
                        'MediaWiki Api Result has Error(s)',
                        collect($res->getErrors())->implode('code', ', ')
                    )
                )
            );
        }

        return $res;
    }
}
