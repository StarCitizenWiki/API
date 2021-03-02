<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Image\Image;
use App\Traits\GetWikiCsrfTokenTrait as GetWikiCsrfToken;
use Carbon\Carbon;
use ErrorException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use StarCitizenWiki\MediaWikiApi\Facades\MediaWikiApi;

class UploadWikiImage
{
    use GetWikiCsrfToken;

    /**
     * @param string $filename Filename on the wiki
     * @param string $url Remote url
     * @param array $metadata Array containing 'sources', 'date', 'description', 'filesize' keys
     * @param string $categories
     * @return false|string
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function upload(string $filename, string $url, array $metadata, string $categories)
    {
        $this->loginWikiBotAccount('services.wiki_upload_image');

        try {
            $token = $this->getCsrfToken('services.wiki_upload_image');
        } catch (ErrorException $e) {
            return $e->getMessage();
        }

        $response = MediaWikiApi::action('upload', 'POST')
            ->withAuthentication()
            ->addParam(
                'filename',
                $filename
            )
            ->addParam('comment', sprintf('Upload image from %s', $url))
            ->addParam(
                'text',
                sprintf(
                    "%s\n\n%s",
                    $this->makeContent($metadata),
                    $categories
                )
            )
            ->addParam('url', $url);

        if (isset($metadata['filesize'])) {
            $response->addParam('filesize', $metadata['filesize']);
        }

        $response = $response->csrfToken($token)->request();

        return json_encode($response->getBody(), JSON_THROW_ON_ERROR);
    }

    /**
     * @param array $data
     *
     * @return string
     * @throws GuzzleException
     * @throws ModelNotFoundException
     * @throws \JsonException
     */
    public function uploadCommLinkImage(array $data): string
    {
        /** @var Image $image */
        $image = Image::query()->findOrFail($data['image']);

        $firstCommLinkId = $image->commLinks->pluck('cig_id')->min();

        $metadata = [
            'sources' => $image->commLinks->map(
                function (CommLink $commLink) {
                    return sprintf(
                        'https://robertsspaceindustries.com%s',
                        $commLink->url ?? sprintf('/SCW/%d-IMPORT', $commLink->cig_id)
                    );
                }
            )
                ->push($image->getLocalOrRemoteUrl())->implode(','),
            'date' => $image->metadata->created_at,
            'filesize' => $image->metadata->size,
            'description' => $data['description'],
        ];

        return $this->upload(
            sprintf(
                'Comm-Link %d %s%s',
                $firstCommLinkId,
                trim($data['filename']),
                $image->getExtension()
            ),
            $image->getLocalOrRemoteUrl(),
            $metadata,
            $this->createCommLinkImageCategories($data, $image)
        );
    }

    private function makeContent(array $data): string
    {
        // Todo this should be dynamic
        return sprintf(
            <<<TEXT
=={{int:filedesc}}==
{{Information
|description={{de|1=%s}}
|date=%s
|source=%s
|author=RSI
|permission=
|other versions=
}}

=={{int:license-header}}==
{{license-rsi}}
TEXT
            ,
            $data['description'],
            Carbon::parse($data['date'])->format('Y-m-d H:i:s'),
            $data['sources']
        );
    }

    /**
     * Parse categories from string
     *
     * @param array $data
     * @param Image $image
     *
     * @return string
     */
    private function createCommLinkImageCategories(array $data, Image $image): string
    {
        return $image->commLinks
            ->pluck('cig_id')
            ->map(
                function ($id) {
                    return sprintf('Comm-Link %d', $id);
                }
            )
            ->sort()
            ->push(...explode(',', $data['categories']))
            ->map(
                function (string $item) {
                    return trim($item);
                }
            )
            ->filter(
                function (string $item) {
                    return strlen($item) > 5;
                }
            )
            ->unique()
            ->map(
                function (string $item) {
                    return str_replace(['Kategorie', 'Categorie', ':'], '', $item);
                }
            )
            ->map(
                function (string $item) {
                    return sprintf('[[Kategorie:%s]]', $item);
                }
            )
            ->implode("\n");
    }
}
