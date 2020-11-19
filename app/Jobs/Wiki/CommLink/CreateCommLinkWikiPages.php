<?php

declare(strict_types=1);

namespace App\Jobs\Wiki\CommLink;

use App\Jobs\Wiki\ApproveRevisions;
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
use RuntimeException;
use StarCitizenWiki\MediaWikiApi\Facades\MediaWikiApi;

/**
 * Create Comm-Link Wiki Pages.
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
    public function handle(): void
    {
        app('Log')::info('Starting creation of Comm-Link Wiki Pages');

        $this->loginWikiBotAccount('services.wiki_translations');

        $token = MediaWikiApi::query()->meta('tokens')->request();

        if ($token->hasErrors()) {
            app('Log')::info(
                sprintf(
                    '%s: %s',
                    'Token retrieval failed',
                    $token->getErrors()['code'] ?? ''
                )
            );

            $this->release(300);

            return;
        }

        $token = $token->getQuery()['tokens']['csrftoken'];

        $config = $this->getCommLinkConfig();
        $config['token'] = $token;

        app('Log')::debug('Current config:', $config);

        CommLink::query()->whereHas(
            'translations',
            static function (Builder $query) {
                $query->where('locale_code', config('services.wiki_translations.locale'))->whereRaw("translation <> ''");
            }
        )->chunk(
            25,
            function (Collection $commLinks) use ($config) {
                try {
                    $pageInfoCollection = $this->getPageInfoForCommLinks($commLinks, true);
                } catch (RuntimeException $e) {
                    app('Log')::error($e->getMessage());

                    return;
                }

                $titleArray = $commLinks->each(
                    static function (CommLink $commLink) use ($pageInfoCollection, $config) {
                        $wikiPage = $pageInfoCollection->get($commLink->cig_id, []);

                        if (isset($wikiPage['missing'])) {
                            dispatch(new CreateCommLinkWikiPage($commLink, $config['token'], $config['template']));
                        }
                    }
                )
                    ->map(
                        function (CommLink $commLink) {
                            return sprintf('Comm-Link:%s', $commLink->cig_id);
                        }
                    );

                if (config('services.wiki_approve_revs.access_secret', null) !== null) {
                    dispatch(new ApproveRevisions($titleArray->toArray()));
                }
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
                } catch (RuntimeException $e) {
                    app('Log')::error($e->getMessage());

                    return;
                }

                $commLinks->each(
                    static function (CommLink $commLink) use ($pageInfoCollection, $config) {
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
                } catch (RuntimeException $e) {
                    app('Log')::error($e->getMessage());

                    return;
                }

                $commLinks->each(
                    static function (CommLink $commLink) use ($pageInfoCollection, $config) {
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
