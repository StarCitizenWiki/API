<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 17.10.2018
 * Time: 10:37
 */

namespace App\Traits\Jobs;


use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Support\Collection;
use StarCitizenWiki\MediaWikiApi\Api\Response\MediaWikiResponse;
use StarCitizenWiki\MediaWikiApi\Facades\MediaWikiApi;

trait GetCommLinkWikiPageInfoTrait
{
    /**
     * MediaWiki API Query with Authentication
     *
     * @var bool
     */
    private $queryWithAuth = false;

    /**
     * Gets Page Info for given Comm-Links keyed by CIG ID
     *
     * @param \Illuminate\Support\Collection $commLinks
     * @param bool                           $queryWithAuth
     *
     * @return \Illuminate\Support\Collection
     */
    private function getPageInfoForCommLinks(Collection $commLinks, bool $queryWithAuth = false)
    {
        $this->queryWithAuth = $queryWithAuth;

        $pages = $commLinks->map(
            function (CommLink $commLink) {
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
     * @return \StarCitizenWiki\MediaWikiApi\Api\Response\MediaWikiResponse
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
     * @return array
     */
    private function getCommLinkConfig(): array
    {
        $response = MediaWikiApi::parse()->page('Comm-Link:Translation-Header')->prop('wikitext')->request();

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

    /**
     * @param \StarCitizenWiki\MediaWikiApi\Api\Response\MediaWikiResponse $response
     */
    private function formatApiError(MediaWikiResponse $response)
    {
        throw new \RuntimeException(
            sprintf(
                '%s: "%s"',
                'MediaWiki Api Result has Error(s)',
                collect($response->getErrors())->implode('code', ', ')
            )
        );
    }
}
