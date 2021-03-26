<?php

declare(strict_types=1);

namespace App\Jobs\Wiki\Galactapedia;

use App\Jobs\AbstractBaseDownloadData;
use App\Models\StarCitizen\Galactapedia\Article;
use App\Models\StarCitizen\Galactapedia\Category;
use App\Services\UploadWikiImage;
use App\Traits\GetWikiCsrfTokenTrait;
use ErrorException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use JsonException;

/**
 * Uploads the image of a galactapedia article to the wiki
 */
class UploadGalactapediaWikiImages extends AbstractBaseDownloadData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use GetWikiCsrfTokenTrait;

    /**
     * @var Article
     */
    private Article $article;

    /**
     * @var string CSRF Token
     */
    private string $token;

    public function __construct(Article $article)
    {
        $this->article = $article;
    }

    /**
     * Response of the thumbnail head request
     *
     * @var Response|null
     */
    private ?Response $response = null;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        try {
            $this->token = $this->getCsrfToken('services.wiki_translations') ?? $this->token;
        } catch (ErrorException $e) {
            $this->release(60);

            return;
        }

        $this->loadThumbnailMetadata();
        $this->uploadGalactapediaImage();
    }


    /**
     * Makes a head request on the thumbnail url of the article
     */
    private function loadThumbnailMetadata(): void
    {
        if ($this->article->thumbnail === null) {
            return;
        }

        $head = $this->makeClient()->head($this->article->thumbnail);

        if ($head->successful()) {
            $this->response = $head;
        }
    }

    /**
     * Uploads the article thumbnail to the wiki
     */
    private function uploadGalactapediaImage(): void
    {
        if ($this->response === null) {
            return;
        }

        // Todo: Default image has exact size of 5003 bytes
        // phpcs:disable
        if (
            $this->response->header('ETag') === '278879e3c41a001689260f0933a7f4ba' ||
            $this->response->header('Content-Length') === '5003'
        ) {
            return;
        }
        // phpcs:enable

        /** @var Collection $categories */
        $categories = $this->article->categories->map(function (Category $category) {
            return sprintf(
                '[[Category:%s]]',
                CreateGalactapediaWikiPage::$categoryTranslations[$category->name] ?? $category->name
            );
        });
        $categories->push('[[Category:Galactapedia]]');
        $categories->push(sprintf('[[Category:%s]]', $this->article->cleanTitle));

        $uploader = new UploadWikiImage();
        try {
            $uploader->upload(
                sprintf(
                    'Galactapedia_%s.%s',
                    str_replace('/', '_', $this->article->cleanTitle),
                    (str_contains($this->response->header('Content-Type'), 'jpeg') ? 'jpg' : 'png')
                ),
                $this->article->thumbnail,
                [
                    'date' => $this->response->header('Last-Modified'),
                    'sources' => implode(',', [
                        $this->article->thumbnail,
                        $this->article->url,
                    ]),
                    'description' => sprintf('Bild des Galactapedia Artikels [[%s]]', $this->article->cleanTitle),
                    'filesize' => $this->response->header('Content-Length'),
                ],
                $categories->implode("\n"),
            );

        } catch (ConnectException $e) {
            $this->release(60);

            return;
        } catch (GuzzleException | JsonException $e) {
            app('Log')::error('Could not upload Galactapedia Image: ' . $e->getMessage());
        }
    }
}
