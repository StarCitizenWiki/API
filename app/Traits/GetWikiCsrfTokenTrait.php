<?php

namespace App\Traits;

use App\Traits\LoginWikiBotAccountTrait as LoginWikiBotAccount;
use App\Traits\LoginWikiUserAccountTrait as LoginWikiUserAccount;
use ErrorException;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use RuntimeException;
use StarCitizenWiki\MediaWikiApi\Api\Response\MediaWikiResponse;
use StarCitizenWiki\MediaWikiApi\Facades\MediaWikiApi;

trait GetWikiCsrfTokenTrait
{
    use LoginWikiBotAccount;
    use LoginWikiUserAccount;

    private static ?string $csrfToken = null;

    /**
     * Requests an csrf token
     *
     * @param string $configPrefix The config prefix see loginWikiBotAccount
     * @param bool $refresh Set to true to force request a new token
     * @param bool $tryUser Try to use user credentials
     * @return string|null
     * @throws ErrorException If the request was not successful
     */
    protected function getCsrfToken(string $configPrefix, bool $refresh = false, bool $tryUser = false): ?string
    {
        if (self::$csrfToken !== null && !$refresh) {
            return self::$csrfToken;
        }

        if ($tryUser === true) {
            try {
                $this->loginWikiUserAccount();
            } catch (RuntimeException $e) {
                $this->loginWikiBotAccount($configPrefix);
            }
        } else {
            $this->loginWikiBotAccount($configPrefix);
        }

        $token = $this->requestToken();

        self::$csrfToken = $token->getQuery()['tokens']['csrftoken'] ?? null;

        return self::$csrfToken;
    }

    /**
     * Requests an csrf token
     *
     * @param bool $refresh Set to true to force request a new token
     * @return string|null
     * @throws ErrorException If the request was not successful
     */
    protected function getCsrfTokenForUser(bool $refresh = false): ?string
    {
        if (self::$csrfToken !== null && !$refresh) {
            return self::$csrfToken;
        }

        $this->loginWikiUserAccount();

        $token = $this->requestToken();

        self::$csrfToken = $token->getQuery()['tokens']['csrftoken'] ?? null;

        return self::$csrfToken;
    }

    /**
     * Do the actual request
     *
     * @return MediaWikiResponse
     * @throws ErrorException
     */
    private function requestToken(): MediaWikiResponse
    {
        try {
            $token = MediaWikiApi::query()->withAuthentication()->meta('tokens')->request();

            if ($token->hasErrors()) {
                throw new ErrorException(json_encode($token->getBody(), JSON_THROW_ON_ERROR));
            }
        } catch (GuzzleException | JsonException $e) {
            throw new ErrorException($e->getMessage());
        }

        return $token;
    }
}
