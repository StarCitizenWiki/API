<?php declare(strict_types = 1);

namespace App\Jobs\Rsi\CommLink\Translate;

use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use StarCitizenWiki\MediaWikiApi\Api\Response\MediaWikiResponse;
use StarCitizenWiki\MediaWikiApi\Facades\MediaWikiApi;

class TranslateCommLinks implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

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
        $manager = app('mediawikiapi.manager');

        $manager->setConsumerFromCredentials(
            (string) config('services.wiki_translations.access_token'),
            (string) config('services.wiki_translations.access_secret')
        );
        $manager->setTokenFromCredentials(
            (string) config('services.wiki_translations.consumer_token'),
            (string) config('services.wiki_translations.consumer_secret')
        );

        CommLink::query()->where('cig_id', '>=', $this->offset)->chunk(
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

                        if (isset($wikiPage['missing'])) {
                            dispatch(new TranslateCommLink($commLink));
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
        $res = MediaWikiApi::query()->prop('info')->prop('categories')->titles($pages)->request();

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
