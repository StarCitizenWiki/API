<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Facades\Session;
use RuntimeException;

/**
 * Trait LoginWikiUserAccount
 */
trait LoginWikiUserAccountTrait
{
    /**
     * Resolves the MediaWikiApi Manager and logs the current user in
     *
     * @throws RuntimeException
     */
    private function loginWikiUserAccount(): void
    {
        if (!Session::has(config('mediawiki.driver.session.token'))) {
            throw new RuntimeException('Session has no user data');
        }

        $manager = app('mediawikiapi.manager');
        $userToken = $manager->getToken();

        $manager->setConsumerFromCredentials(
            config('services.mediawiki.client_id'),
            config('services.mediawiki.client_secret')
        );
        $manager->setTokenFromCredentials(
            $userToken->key,
            $userToken->secret,
        );
    }
}
