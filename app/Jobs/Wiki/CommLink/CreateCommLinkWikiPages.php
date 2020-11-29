<?php

declare(strict_types=1);

namespace App\Jobs\Wiki\CommLink;

use App\Models\Rsi\CommLink\CommLink;
use App\Traits\GetWikiCsrfTokenTrait as GetWikiCsrfToken;
use App\Traits\Jobs\GetCommLinkWikiPageInfoTrait as GetCommLinkWikiPageInfo;
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
    use GetWikiCsrfToken;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        app('Log')::info('Starting creation of Comm-Link Wiki Pages');

        $token = MediaWikiApi::query()->meta('tokens')->request();

        try {
            $token = $this->getCsrfToken('services.wiki_translations');
        } catch (\ErrorException $e) {
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

        $config = $this->getCommLinkConfig();
        $config['token'] = $token;

        app('Log')::debug('Current config:', $config);

        CommLink::query()->whereHas(
            'translations',
            static function (Builder $query) {
                $query->where('locale_code', config('services.wiki_translations.locale'))->whereRaw(
                    "translation <> ''"
                );
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
