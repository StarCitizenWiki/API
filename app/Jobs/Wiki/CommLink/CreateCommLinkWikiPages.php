<?php declare(strict_types = 1);

namespace App\Jobs\Wiki\CommLink;

use App\Models\Rsi\CommLink\CommLink;
use App\Traits\Jobs\GetCommLinkWikiPageInfoTrait as GetCommLinkWikiPageInfo;
use App\Traits\Jobs\LoginWikiBotAccountTrait as LoginWikiBotAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use StarCitizenWiki\MediaWikiApi\Facades\MediaWikiApi;

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
    use LoginWikiBotAccount;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app('Log')::info('Starting creation of Comm-Link Wiki Pages');

        $this->loginWikiBotAccount();

        $token = MediaWikiApi::query()->meta('tokens')->request();
        if ($token->hasErrors()) {
            $this->fail(
                new \RuntimeException(
                    sprintf('%s: %s', 'Token retrieval failed', collect($token->getErrors())->implode('code', ', '))
                )
            );

            return;
        }

        $token = $token->getQuery()['tokens']['csrftoken'];

        $config = $this->getCommLinkConfig();
        $config['token'] = $token;

        app('Log')::debug('Current config:', $config);

        CommLink::query()->whereHas(
            'translations',
            function (Builder $query) {
                $query->where('locale_code', 'de_DE')->whereRaw("translation <> ''");
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
                    function (CommLink $commLink) use ($pageInfoCollection, $config) {
                        $wikiPage = $pageInfoCollection->get($commLink->cig_id, []);

                        if (isset($wikiPage['missing'])) {
                            dispatch(new CreateCommLinkWikiPage($commLink, $config['token'], $config['template']));
                        }
                    }
                );
            }
        );

        $config = $this->getCommLinkConfig('Comm-Link:Subscriber-Header');
        $config['token'] = $token;

        CommLink::query()->whereHas(
            'channel',
            static function (Builder $query) {
                $query->where('name', 'Subscriber');
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
                    function (CommLink $commLink) use ($pageInfoCollection, $config) {
                        $wikiPage = $pageInfoCollection->get($commLink->cig_id, []);

                        if (isset($wikiPage['missing'])) {
                            dispatch(new CreateCommLinkWikiPage($commLink, $config['token'], $config['template']));
                        }
                    }
                );
            }
        );

        $config = $this->getCommLinkConfig('Comm-Link:Press-Header');
        $config['token'] = $token;

        CommLink::query()->whereHas(
            'channel',
            static function (Builder $query) {
                $query->where('name', 'Press');
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
                    function (CommLink $commLink) use ($pageInfoCollection, $config) {
                        $wikiPage = $pageInfoCollection->get($commLink->cig_id, []);

                        if (isset($wikiPage['missing'])) {
                            dispatch(new CreateCommLinkWikiPage($commLink, $config['token'], $config['template']));
                        }
                    }
                );
            }
        );
    }
}
