<?php

declare(strict_types=1);

namespace App\Traits\Jobs;

/**
 * Trait LoginWikiBotAccountTrait
 */
trait LoginWikiBotAccountTrait
{
    /**
     * Resolves the MediaWikiApi Manager and logs the set Bot Acount in
     */
    private function loginWikiBotAccount(): void
    {
        $manager = app('mediawikiapi.manager');

        $manager->setConsumerFromCredentials(
            (string)config('services.wiki_translations.consumer_token'),
            (string)config('services.wiki_translations.consumer_secret')
        );
        $manager->setTokenFromCredentials(
            (string)config('services.wiki_translations.access_token'),
            (string)config('services.wiki_translations.access_secret')
        );
    }
}
