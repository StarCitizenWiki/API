<?php

declare(strict_types=1);

namespace App\Services;

use GuzzleHttp\Exception\GuzzleException;
use StarCitizenWiki\MediaWikiApi\Facades\MediaWikiApi;

/**
 * Wraps some wiki api calls in static methods
 */
class WrappedWiki
{
    /**
     * Tries to resolve a title to the final redirected title on the wiki
     *
     * @param string $title
     * @return string
     */
    public static function getRedirectTitle(string $title): string
    {
        try {
            $query = MediaWikiApi::query()
                ->prop('redirects')
                ->titles($title)
                ->redirects(1)
                ->request();
        } catch (GuzzleException $e) {
            return $title;
        }

        if ($query->hasErrors() || !isset($query->getQuery()['redirects'][0])) {
            return $title;
        }

        return $query->getQuery()['redirects'][0]['to'] ?? $title;
    }


    /**
     * Page content of the wiki page or null on error or not found
     *
     * @param string $title
     * @return string|null
     */
    public static function getWikiPageText(string $title): ?string
    {
        try {
            $pageContent = MediaWikiApi::query()
                ->prop('revisions')
                ->addParam('rvprop', 'content')
                ->addParam('rvslot', 'main')
                ->titles($title)
                ->request();
        } catch (GuzzleException $e) {
            return null;
        }

        if ($pageContent->hasErrors() || isset($pageContent->getQuery()['pages']['-1'])) {
            return null;
        }

        $query = $pageContent->getQuery()['pages'];
        $first = array_shift($query);

        return $first['revisions'][0]['slots']['main']['*'] ?? $first['revisions'][0]['*'] ?? null;
    }
}
