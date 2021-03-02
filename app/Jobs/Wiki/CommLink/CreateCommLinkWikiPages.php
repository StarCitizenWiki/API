<?php

declare(strict_types=1);

namespace App\Jobs\Wiki\CommLink;

use App\Models\Rsi\CommLink\CommLink;
use App\Traits\GetWikiCsrfTokenTrait as GetWikiCsrfToken;
use App\Traits\Jobs\GetCommLinkWikiPageInfoTrait as GetCommLinkWikiPageInfo;
use ErrorException;
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
     * Comm-Link creation config
     *
     * @var array
     */
    private array $config;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        app('Log')::info('Starting creation of Comm-Link Wiki Pages');

        try {
            $token = $this->getCsrfToken('services.wiki_translations');
        } catch (ErrorException $e) {
            app('Log')::info(
                sprintf(
                    '%s: %s',
                    'Token retrieval failed',
                    $e->getMessage()
                )
            );

            $this->release(300);

            return;
        }

        $commLinkConfig = $this->getCommLinkConfig();
        $commLinkConfig['token'] = $token;
        $this->config = $commLinkConfig;

        app('Log')::debug('Current config:', $commLinkConfig);

        $dispatchFunction = function (Collection $commLinks) {
            try {
                $pageInfoCollection = $this->getPageInfoForCommLinks($commLinks, true);
            } catch (RuntimeException $e) {
                app('Log')::error($e->getMessage());

                return;
            }

            $localConfig = $this->config;

            $commLinks->each(
                static function (CommLink $commLink) use ($pageInfoCollection, $localConfig) {
                    $wikiPage = $pageInfoCollection->get($commLink->cig_id, []);

                    if (isset($wikiPage['missing'])) {
                        dispatch(
                            new CreateCommLinkWikiPage(
                                $commLink,
                                $localConfig['token'],
                                $localConfig['template']
                            )
                        );
                    }
                }
            );
        };

        CommLink::query()->whereHas(
            'translations',
            static function (Builder $query) {
                $query->where('locale_code', config('services.wiki_translations.locale'))
                    ->whereRaw(
                        "translation <> ''"
                    );
            }
        )->chunk(
            100,
            $dispatchFunction
        );

        $commLinkConfig = $this->getCommLinkConfig('Comm-Link:Subscriber-Header');
        $commLinkConfig['token'] = $token;
        $this->config = $commLinkConfig;

        CommLink::query()->whereHas(
            'channel',
            static function (Builder $query) {
                $query->where('name', 'Subscriber');
            }
        )->chunk(
            100,
            $dispatchFunction
        );

        $commLinkConfig = $this->getCommLinkConfig('Comm-Link:Press-Header');
        $commLinkConfig['token'] = $token;
        $this->config = $commLinkConfig;

        CommLink::query()->whereHas(
            'channel',
            static function (Builder $query) {
                $query->where('name', 'Press');
            }
        )->chunk(
            100,
            $dispatchFunction
        );
    }
}
