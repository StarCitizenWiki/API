<?php

declare(strict_types=1);

namespace App\Traits;

/**
 * Trait LoginWikiBotAccountTrait
 */
trait LoginWikiBotAccountTrait
{
    /**
     * Resolves the MediaWikiApi Manager and logs the set Bot Account in
     *
     * @param string $prefix Config key prefix excluding trailing dot
     */
    private function loginWikiBotAccount(string $prefix): void
    {
        $manager = app('mediawikiapi.manager');

        $manager->setConsumerFromCredentials(
            (string)config(sprintf('%s.consumer_token', $prefix)),
            (string)config(sprintf('%s.consumer_secret', $prefix))
        );
        $manager->setTokenFromCredentials(
            (string)config(sprintf('%s.access_token', $prefix)),
            (string)config(sprintf('%s.access_secret', $prefix))
        );
    }
}
