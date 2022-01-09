<?php

declare(strict_types=1);

namespace App\Jobs\Wiki\CommLink;

use App\Models\Rsi\CommLink\CommLink;
use App\Services\TranslateSectionizer;
use App\Services\WrappedWiki;
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

        $createPage = static function (int $clId, string $locale, int $id, string $content) use ($token) {
            MediaWikiApi::edit("Translations:Comm-Link:{$clId}/{$id}/{$locale}")
                ->text($content)
                ->summary("Importing Comm-Link Translation")
                ->csrfToken($token)
                ->markBotEdit()
                ->createOnly()
                ->request();
        };

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
                function (Collection $commlinks) use ($createPage) {
                    $commlinks->filter(function (CommLink $commLink) {
                        return $commLink->german() !== null;
                    })
                        ->map(function (CommLink $commLink) {
                            $commLink->splittedEnglish = TranslateSectionizer::sectionise(
                                $commLink->english()->translation
                            );

                            if ($commLink->german()->proofread === true) {
                                $splittedGerman = TranslateSectionizer::sectionise(
                                    WrappedWiki::getWikiPageText(sprintf('Comm-Link:%d', $commLink->cig_id)) ?? ''
                                );

                                $fix = $splittedGerman[0] ?? '';
                                $fix = str_replace('<noinclude>{{Comm-Link}}</noinclude>', '', $fix);
                                $fix = explode(']]', $fix);
                                $splittedGerman[0] = trim(array_pop($fix));

                                $commLink->splittedGerman = $splittedGerman;
                            } else {
                                $commLink->splittedGerman = TranslateSectionizer::sectionise(
                                    $commLink->german()->translation
                                );
                            }

                            return $commLink;
                        })
                        ->filter(function (CommLink $commLink) {
                            $equal = count($commLink->splittedEnglish) === count($commLink->splittedGerman);

                            if ($equal === false) {
                                app('Log')::warning("Splits for Comm-Link {$commLink->cig_id} arent equal!");
                            }

                            return $equal;
                        })
                        ->each(function (CommLink $commLink) use ($createPage) {
                            for ($i = 1; $i < count($commLink->splittedEnglish) + 1; $i++) {
                                $createPage($commLink->cig_id, 'en', $i, $commLink->splittedEnglish[$i - 1]);
                            }
                            for ($i = 1; $i < count($commLink->splittedGerman) + 1; $i++) {
                                $createPage($commLink->cig_id, 'de', $i, $commLink->splittedGerman[$i - 1]);
                            }
                        });
                }
            );
    }
}
