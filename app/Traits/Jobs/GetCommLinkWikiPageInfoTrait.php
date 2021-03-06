<?php

declare(strict_types=1);

namespace App\Traits\Jobs;

use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Support\Collection;
use RuntimeException;
use StarCitizenWiki\MediaWikiApi\Api\Response\MediaWikiResponse;
use StarCitizenWiki\MediaWikiApi\Facades\MediaWikiApi;

trait GetCommLinkWikiPageInfoTrait
{
    /**
     * MediaWiki API Query with Authentication
     *
     * @var bool
     */
    private bool $queryWithAuth = false;

    /**
     * Gets Page Info for given Comm-Links keyed by CIG ID
     *
     * @param Collection $commLinks
     * @param bool       $queryWithAuth
     *
     * @return Collection
     */
    private function getPageInfoForCommLinks(Collection $commLinks, bool $queryWithAuth = false): Collection
    {
        $this->queryWithAuth = $queryWithAuth;

        $pages = $commLinks->map(
            static function (CommLink $commLink) {
                return sprintf('%s:%d', 'Comm-Link', $commLink->cig_id);
            }
        )->implode('|');

        $res = $this->getMediaWikiQuery($pages);
        $query = $res->getQuery();

        if (!isset($query['pages'])) {
            return new Collection();
        }

        return collect($query['pages'])->keyBy(
            function (array $value) {
                return str_replace('Comm-Link:', '', $value['title']);
            }
        );
    }

    /**
     * Query the Wiki for given Pages
     *
     * @param string $pages
     *
     * @return MediaWikiResponse
     */
    private function getMediaWikiQuery(string $pages): MediaWikiResponse
    {
        $response = MediaWikiApi::query()->prop('info')->prop('categories')->cllimit(-1)->titles($pages);

        if ($this->queryWithAuth) {
            $response->withAuthentication();
        }

        $response = $response->request();

        if ($response->hasErrors()) {
            $this->formatApiError($response);
        }

        return $response;
    }

    /**
     * @param MediaWikiResponse $response
     */
    private function formatApiError(MediaWikiResponse $response): void
    {
        throw new RuntimeException(
            sprintf(
                '%s: "%s"',
                'MediaWiki Api Result has Error(s)',
                is_array($response->getErrors()) ? implode(', ', $response->getErrors()) : $response->getErrors(),
            )
        );
    }

    /**
     * @param string $page
     *
     * @return array
     */
    private function getCommLinkConfig(string $page = 'Comm-Link:Translation-Header'): array
    {
        $response = MediaWikiApi::parse()->page($page)->prop('wikitext')->request();

        if ($response->hasErrors()) {
            $this->formatApiError($response);
        }

        $response = $response->getBody()['parse']['wikitext']['*'];

        if (preg_match('/<pre>(.*?)<\/pre>/s', $response, $template)) {
            $template = $template[1];
        }

        if (preg_match('/<pre id=\"cleanup-category\">(.*?)<\/pre>/s', $response, $cleanCategory)) {
            $cleanCategory = $cleanCategory[1];
        }

        return [
            'template' => $template,
            'category' => $cleanCategory,
        ];
    }
}
