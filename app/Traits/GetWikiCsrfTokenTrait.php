<?php

namespace App\Traits;

use App\Traits\LoginWikiBotAccountTrait as LoginWikiBotAccount;
use ErrorException;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use StarCitizenWiki\MediaWikiApi\Facades\MediaWikiApi;

trait GetWikiCsrfTokenTrait
{
    use LoginWikiBotAccount;

    private static ?string $csrfToken = null;

    /**
     * Requests an csrf token
     *
     * @param string $configPrefix The config prefix see loginWikiBotAccount
     * @param bool   $refresh      Set to true to force request a new token
     *
     * @return string|null
     * @throws ErrorException If the request was not successfull
     */
    protected function getCsrfToken(string $configPrefix, bool $refresh = false): ?string
    {
        if (self::$csrfToken !== null && !$refresh) {
            return self::$csrfToken;
        }

        $this->loginWikiBotAccount($configPrefix);

        try {
            $token = MediaWikiApi::query()->meta('tokens')->request();

            if ($token->hasErrors()) {
                throw new ErrorException(json_encode($token->getBody(), JSON_THROW_ON_ERROR));
            }
        } catch (GuzzleException | JsonException $e) {
            throw new ErrorException($e->getMessage());
        }

        self::$csrfToken = $token->getQuery()['tokens']['csrftoken'] ?? null;

        return self::$csrfToken;
    }
}
