<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Image\Image;
use App\Traits\Jobs\LoginWikiBotAccountTrait as LoginWikiBotAccount;
use Illuminate\Support\Collection;
use StarCitizenWiki\MediaWikiApi\Facades\MediaWikiApi;

class UploadWikiImage
{
    use LoginWikiBotAccount;

    /**
     * @param array $data
     *
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \JsonException
     */
    public function upload(array $data): string
    {
        /** @var Image $image */
        $image = Image::query()->findOrFail($data['image']);

        $this->loginWikiBotAccount('services.wiki_upload_image');

        $token = MediaWikiApi::query()->meta('tokens')->request();

        if ($token->hasErrors()) {
            return json_encode($token->getBody(), JSON_THROW_ON_ERROR);
        }

        $token = $token->getQuery()['tokens']['csrftoken'];

        $firstCommLinkId = $image->commLinks->pluck('cig_id')->min();

        $response = MediaWikiApi::action('upload', 'POST')
            ->withAuthentication()
            ->addParam(
                'filename',
                sprintf(
                    'Comm-Link %d %s%s',
                    $firstCommLinkId,
                    trim($data['filename']),
                    $image->getExtension()
                )
            )
            ->addParam('comment', sprintf('Upload image from %s', $image->getLocalOrRemoteUrl()))
            ->addParam(
                'text',
                sprintf(
                    "%s\n\n%s",
                    $this->makeContent($data, $image),
                    $this->parseCategories($data, $image)
                )
            )
            ->addParam('url', $image->getLocalOrRemoteUrl())
            ->addParam('filesize', $image->metadata->size)
            ->csrfToken($token)
            ->request();

        return json_encode($response->getBody(), JSON_THROW_ON_ERROR);
    }

    private function makeContent(array $data, Image $image): string
    {
        /** @var Collection $sources */
        $sources = $image->commLinks->map(
            function (CommLink $commLink) {
                return sprintf('https://robertsspaceindustries.com%s', $commLink->url);
            }
        );

        $sources->push($image->getLocalOrRemoteUrl());

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
            $image->metadata->created_at->format('Y-m-d H:i:s'),
            $sources->implode(',')
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
    private function parseCategories(array $data, Image $image): string
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
