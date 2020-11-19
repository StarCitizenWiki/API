<?php

declare(strict_types=1);

namespace App\Traits\Jobs;

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
            (string)config("${prefix}.consumer_token"),
            (string)config("${$prefix}.consumer_secret")
        );
        $manager->setTokenFromCredentials(
            (string)config("${prefix}.access_token"),
            (string)config("${$prefix}.access_secret")
        );
    }
}
