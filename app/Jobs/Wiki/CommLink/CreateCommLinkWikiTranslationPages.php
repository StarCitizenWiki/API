<?php

declare(strict_types=1);

namespace App\Jobs\Wiki\CommLink;

use App\Jobs\Wiki\ApproveRevisions;
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
use StarCitizenWiki\MediaWikiApi\Facades\MediaWikiApi;

/**
 * Create Comm-Link Wiki Translation Pages.
 */
class CreateCommLinkWikiTranslationPages implements ShouldQueue
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
        app('Log')::info('Starting creation of Comm-Link Wiki Translation Pages');

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

        CommLink::query()
            ->whereHas(
                'translations',
                static function (Builder $query) {
                    $query->where('locale_code', config('services.wiki_translations.locale'))
                        ->whereRaw(
                            "translation <> ''"
                        );
                }
            )->chunk(
                10,
                function (Collection $commlinks) use ($token) {
                    $commlinks->each(function (CommLink $commLink) use ($token) {
                        MediaWikiApi::edit("Comm-Link:{$commLink->cig_id}/en")
                            ->text(sprintf(
                                "<noinclude>{{Comm-Link}}</noinclude>\n%s",
                                optional($commLink->english())->translation
                            ))
                            ->summary("Importing Comm-Link Translation")
                            ->csrfToken($token)
                            ->markBotEdit()
                            ->request();
                    });


                    if (config('services.wiki_approve_revs.access_secret', null) !== null) {
                        dispatch(new ApproveRevisions($commlinks->map(function (CommLink $commLink) {
                            return "Comm-Link:{$commLink->cig_id}/en";
                        })->toArray()));
                    }
                }
            );
    }
}
