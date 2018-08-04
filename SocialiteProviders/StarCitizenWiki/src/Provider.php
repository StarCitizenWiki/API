<?php declare(strict_types = 1);

namespace SocialiteProviders\StarCitizenWiki;

use SocialiteProviders\Manager\OAuth1\AbstractProvider;
use SocialiteProviders\Manager\OAuth1\User;

/**
 * Class Provider
 */
class Provider extends AbstractProvider
{
    /**
     * Unique Provider Identifier.
     */
    const IDENTIFIER = 'STARCITIZENWIKI';

    /**
     * {@inheritDoc}
     */
    public function user()
    {
        if (!$this->hasNecessaryVerifier()) {
            throw new \InvalidArgumentException("Invalid request. Missing OAuth verifier.");
        }

        /** @var \League\OAuth1\Client\Credentials\TokenCredentials $token */
        $token = $this->getToken()["tokenCredentials"];

        $user = $this->server->getUserDetails($token);

        return (new User())->setRaw($user->extra)->map(
            [
                'id' => $user->id,
                'username' => $user->username,
                'blocked' => $user->blocked,
            ]
        )->setToken($token->getIdentifier(), $token->getSecret());
    }
}
