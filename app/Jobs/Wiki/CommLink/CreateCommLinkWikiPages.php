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
 * Create Comm-Link Wiki Pages
 */
class CreateCommLinkWikiPages implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use GetCommLinkWikiPageInfo;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app('Log')::info('Starting creation of Comm-Link Wiki Pages');

        $manager = app('mediawikiapi.manager');

        $manager->setConsumerFromCredentials(
            (string) config('services.wiki_translations.access_token'),
            (string) config('services.wiki_translations.access_secret')
        );
        $manager->setTokenFromCredentials(
            (string) config('services.wiki_translations.consumer_token'),
            (string) config('services.wiki_translations.consumer_secret')
        );

        $config = $this->getCommLinkConfig();
        app('Log')::debug('Current config:', $config);

        CommLink::query()->chunk(
            100,
            function (Collection $commLinks) use ($config) {
                $commLinks = $commLinks->filter(
                    function (CommLink $commLink) {
                        return $commLink->german() !== null;
                    }
                );

                $wikiPages = $this->getPageInfoForCommLinks($commLinks);

                $commLinks->each(
                    function (CommLink $commLink) use ($wikiPages, $config) {
                        $wikiPage = $wikiPages->get($commLink->cig_id, []);

                        if (isset($wikiPage['missing'])) {
                            dispatch(new CreateCommLinkWikiPage($commLink, $config['template']));
                        }
                    }
                );
            }
        );
    }
}
