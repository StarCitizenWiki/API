<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 19.10.2018
 * Time: 11:41
 */

namespace App\Traits\Jobs;

/**
 * Trait LoginWikiBotAccountTrait
 */
trait LoginWikiBotAccountTrait
{
    /**
     * Resolves the MediaWikiApi Manager and logs the set Bot Acount in
     */
    private function loginWikiBotAccount()
    {
        $manager = app('mediawikiapi.manager');

        $manager->setConsumerFromCredentials(
            (string) config('services.wiki_translations.consumer_token'),
            (string) config('services.wiki_translations.consumer_secret')
        );
        $manager->setTokenFromCredentials(
            (string) config('services.wiki_translations.access_token'),
            (string) config('services.wiki_translations.access_secret')
        );
    }
}
