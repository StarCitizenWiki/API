<?php

declare(strict_types=1);

namespace App\Traits\Jobs;

use GuzzleHttp\Exception\GuzzleException;
use StarCitizenWiki\MediaWikiApi\Facades\MediaWikiApi;

trait CreateEnglishSubpageTrait
{
    private function createEnglishSubpage(string $pageName, string $csrfToken): void
    {
        if (config('services.wiki_translations.create_english_subpage') === true) {
            try {
                MediaWikiApi::edit(sprintf('%s/en', $pageName))
                    ->withAuthentication()
                    ->text(sprintf('#redirect [[tools:%s]]', $pageName))
                    ->csrfToken($csrfToken)
                    ->summary('Redirecting english subpage')
                    ->request();
            } catch (GuzzleException $e) {
                return;
            }
        }
    }
}
