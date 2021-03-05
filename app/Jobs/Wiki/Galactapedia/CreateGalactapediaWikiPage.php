<?php

declare(strict_types=1);

namespace App\Jobs\Wiki\Galactapedia;

use App\Jobs\AbstractBaseDownloadData;
use App\Jobs\Wiki\ApproveRevisions;
use App\Models\StarCitizen\Galactapedia\Article;
use App\Models\StarCitizen\Galactapedia\ArticleProperty;
use App\Models\StarCitizen\Galactapedia\Category;
use App\Services\CommonMark\WikiTextRenderer;
use App\Services\UploadWikiImage;
use App\Traits\GetWikiCsrfTokenTrait;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use JsonException;
use RuntimeException;
use StarCitizenWiki\MediaWikiApi\Api\Response\MediaWikiResponse;
use StarCitizenWiki\MediaWikiApi\Facades\MediaWikiApi;

/**
 * Creates a Galactapedia Page on a Wiki
 */
class CreateGalactapediaWikiPage extends AbstractBaseDownloadData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use GetWikiCsrfTokenTrait;

    /**
     * TODO Move into DB
     *
     * @var string[]
     */
    private static array $categoryTranslations = [
        "Human" => "Menschen",
        "Food and Beverages" => "Essen und Trinken",
        "Entertainment" => "Unterhaltung",
        "Law" => "Recht",
        "Planetary Systems" => "Planetares System",
        "Education" => "Bildung",
        "Art" => "Kunst",
        "Animals" => "Tier",
        "Space" => "Weltraum",
        "Ground Transportation" => "Bodentransport",
        "Culture" => "Kultur",
        "Music" => "Musik",
        "Military" => "Militär",
        "Exploration" => "Erforschung",
        "Archaeology" => "Archäologie",
        "Weapons" => "Waffe",
        "Commerce" => "Handel",
        "People" => "Persönlichkeit",
        "Civilizations" => "Zivilisation",
        "History" => "Geschichte",
        "Government" => "Regierung",
        "Fiction" => "Belletristik",
        "Illegal Activity" => "Illegale Aktivität",
        "Locations" => "Standort",
        "Factions" => "Fraktion",
        "Plants" => "Pflanze",
        "Politics" => "Politik",
        "Science and Technology" => "Wissenschaft und Technik",
        "Settlements" => "Siedlung",
        "Spacecraft" => "Raumschiff",
        "Sports" => "Sport",
        "Holidays" => "Feiertag",
        "Geography" => "Geographie",
        "Publications" => "Publikation",
        "Moons" => "Mond",
        "Planets" => "Planet",
    ];

    /**
     * @var Article
     */
    private Article $article;

    /**
     * @var string CSRF Token
     */
    private string $token;

    /**
     * Response of the thumbnail head request
     *
     * @var Response|null
     */
    private ?Response $response = null;

    /**
     * The article wiki page title
     *
     * @var string
     */
    private string $title = '';

    /**
     * Create a new job instance.
     *
     * @param Article $article
     * @param string $token
     */
    public function __construct(Article $article, string $token)
    {
        $this->article = $article;
        $this->token = $token;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        app('Log')::info("Creating Wiki Page '{$this->article->cleanTitle}'");

        $this->getRedirectTitle();
        $wikiText = $this->getWikiPageText();
        $this->loadThumbnailMetadata();

        try {
            $text = $this->getFormattedText($this->getArticleText(), $wikiText);

            // Skip if texts are equal
            if (strcmp($text, $wikiText ?? '') === 0) {
                $this->delete();
                return;
            }

            $response = $this->editRequest($text, $wikiText);

            if ($response->hasErrors()) {
                $response = $this->editRequest($text, $wikiText, true);
                // Oof
                if ($response->hasErrors()) {
                    app('Log')::error(json_encode($response->getBody()));
                }
            }

            if ($response->hasWarnings()) {
                app('Log')::warning(json_encode($response->getBody()));
            }

            if (config('services.wiki_approve_revs.access_secret', null) !== null) {
                dispatch(new ApproveRevisions([
                    $this->article->cleanTitle,
                    $this->title,
                ]));
            }

            $this->uploadGalactapediaImage();
        } catch (GuzzleException | RuntimeException $e) {
            app('Log')::error('Could not get an CSRF Token', $e->getResponse()->getErrors());

            $this->fail($e);

            return;
        }
    }

    /**
     * Make the edit request
     *
     * @param string $text
     * @param string|null $wikiText
     * @param bool $refreshToken
     * @return MediaWikiResponse
     * @throws GuzzleException
     */
    private function editRequest(string $text, ?string $wikiText, bool $refreshToken = false): MediaWikiResponse
    {
        if ($refreshToken === true) {
            try {
                $this->token = $this->getCsrfToken('services.wiki_translations') ?? $this->token;
            } catch (Exception $e) {
                return MediaWikiResponse::fromGuzzleResponse(
                    new \GuzzleHttp\Psr7\Response($e->getCode(), [], $e->getMessage())
                );
            }
        }

        return MediaWikiApi::edit($this->title)
            ->text($text)
            ->summary(
                sprintf(
                    "%s Galactapedia Article %s",
                    ($wikiText === null ? 'Importing' : 'Updating'),
                    $this->article->cleanTitle
                )
            )
            ->csrfToken($this->token)
            ->markBotEdit()
            ->request();
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
     * Tries to resolve the article title to the final on the wiki
     *
     * @return void
     */
    private function getRedirectTitle(): void
    {
        try {
            $query = MediaWikiApi::query()
                ->prop('redirects')
                ->titles($this->article->cleanTitle)
                ->redirects(1)
                ->request();
        } catch (GuzzleException $e) {
            $this->title = $this->article->cleanTitle;

            return;
        }

        if ($query->hasErrors() || !isset($query->getQuery()['redirects'][0])) {
            $this->title = $this->article->cleanTitle;

            return;
        }

        $this->title = $query->getQuery()['redirects'][0]['to'] ?? $this->article->cleanTitle;
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
            return sprintf('[[Category:%s]]', self::$categoryTranslations[$category->name] ?? $category->name);
        });
        $categories->push('[[Category:Galactapedia]]');

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
                    'description' => sprintf('Bild des Galactapedia Artikels %s', $this->article->cleanTitle),
                    'filesize' => $this->response->header('Content-Length'),
                ],
                $categories->implode("\n"),
            );
        } catch (GuzzleException | JsonException $e) {
            app('Log')::error('Could not upload Galactapedia Image: ' . $e->getMessage());
        }
    }

    /**
     * Page content of the wiki page or null on error or not found
     *
     * @return string|null
     */
    private function getWikiPageText(): ?string
    {
        try {
            $pageContent = MediaWikiApi::query()
                ->prop('revisions')
                ->addParam('rvprop', 'content')
                ->addParam('rvslot', 'main')
                ->titles(Article::normalizeContent($this->title))
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

    /**
     * Get the normalized translation of the article
     *
     * @return string
     */
    private function getArticleText(): string
    {
        if (config('services.wiki_translations.locale') === 'de_DE') {
            $text = optional($this->article->german())->translation;
        } else {
            $text = optional($this->article->english())->translation;
        }

        if ($text === null) {
            $text = optional($this->article->english())->translation;
        }

        return Article::normalizeContent($text);
    }

    /**
     * TODO somehow clean up
     *
     * @param string $markdown
     * @param string|null $pageContent
     * @return string
     */
    public function getFormattedText(string $markdown, ?string $pageContent): string
    {
        $format = <<<FORMAT
%s<!--imported-text

!!! Achtung, der folgende Text wird automatisiert verwaltet, alle Änderungen werden gelöscht. !!!
!!! Du kannst Text in einer neuen Zeile unter END-- einfügen. Dieser wird nicht gelöscht.     !!!
!!! Weitere Informationen findest du hier: https://star-citizen.wiki/Vorlage:Galactapedia     !!!

START-->%s%s<!--
-->%s<!--%s
END-->
FORMAT;

        $content = $this->createContent($markdown, str_contains($pageContent ?? '', 'galactapedia-box'));
        $categories = $this->createCategories();
        $ref = $this->createRef();

        if ($pageContent !== null) {
            if (str_contains($pageContent, 'DISABLE-CATS-->')) {
                $categories = '<!--DISABLE-CATS-->';
            }

            $content = $this->runTextReplacements($content, $pageContent);

            $formatted = sprintf(
                $format,
                '', // Don't replace template
                $content['content'],
                $ref,
                $categories,
                $content['repl'] ?? ''
            );

            return preg_replace(
                '/(?:<div class="imported-text">)?<!--(?:imported-text)?.*END-->(?:<\/div>)?/s',
                $formatted,
                $pageContent,
                1
            );
        }

        return sprintf(
            $format,
            $this->createTemplate(),
            $content,
            $ref,
            $categories,
            ''
        );
    }

    /**
     * Creates the galactapedia template with content
     *
     * @return string
     */
    private function createTemplate(): string
    {
        $fileEnding = 'jpg';
        if ($this->response !== null) {
            $fileEnding = (str_contains($this->response->header('Content-Type'), 'jpeg') ? 'jpg' : 'png');
        }

        $properties = collect();
        $this->article->properties
            ->sortBy('name')
            ->each(function (ArticleProperty $property) use ($properties) {
                $counter = 0;

                if ($properties->has($property->name)) {
                    do {
                        $counter++;
                        $key = sprintf('%s%d', $property->name, $counter);
                    } while ($properties->has($key));
                    $properties[$key] = $property->content;
                } else {
                    $properties[$property->name] = $property->content;
                }
            });

        $properties = $properties->map(function ($item, $key) {
            return sprintf("|%s=%s", $key, $item);
        })
            ->implode("\n");

        $relatedArticles = $this->article->related
            ->map(function (Article $article) {
                return sprintf('[[%s]]', $article->cleanTitle);
            })
            ->implode("<br>\n");

        $normalizedFileName = str_replace('/', '_', $this->article->cleanTitle);

        // The actual template content
        return <<<TEMPLATE
{{Galactapedia
|title={$normalizedFileName}
|image=Galactapedia_{$this->article->cleanTitle}.{$fileEnding}
{$properties}
|related={$relatedArticles}
}}
TEMPLATE;
    }

    /**
     * Creates the page text content and optionally wraps it in a fancy box
     * THx @alistair
     *
     * @param string $markdown The raw galactapedia markdown
     * @param bool $boxed Flag to box the content
     *
     * @return string Parsed Wikitext
     */
    private function createContent(string $markdown, bool $boxed = false): string
    {
        $parser = new WikiTextRenderer();

        // Fix headings
        $markdown = preg_replace('/^(#+)\s+?(\w)/', '$1 $2', $markdown);

        $wikitext = trim($parser->render($markdown));

        // Remove first heading
        if ($wikitext[0] === '=') {
            $wikitext = preg_replace('/^=+.*\s?/', '', $wikitext, 1);
        }

        if (!$boxed) {
            return $wikitext;
        }

        return <<<CONTENT
<div class="galactapedia-box">
  <div class="galactapedia-box-header">
    <span class="galactapedia-box-header-icon">[[File:Roberts Space Industries.svg|x16px|link=]]</span>Galactapedia
  </div>
  <div class="galactapedia-box-content">{$wikitext}</div>
</div>
CONTENT;
    }

    /**
     * Maps the article categories to string
     *
     * @return string
     */
    private function createCategories(): string
    {
        return
            $this->article->categories
                ->map(function (Category $category) {
                    return sprintf(
                        '[[Category:%s]]',
                        self::$categoryTranslations[$category->name] ?? $category->name
                    );
                })
                ->implode("\n");
    }

    /**
     * Creates a ref Template which links to the original galactapedia article
     *
     * @return string
     */
    private function createRef(): string
    {
        return sprintf(
            '{{Quelle|url=%s|title=Galactapedia %s|date=%s|access_date=%s|ref=true|ref_name=galactapedia}}',
            $this->article->url,
            $this->article->cleanTitle,
            $this->article->created_at->format('d.m.Y'),
            $this->article->updated_at->format('d.m.Y'),
        );
    }

    /**
     * Replaces text tokens formulated as (FROM|TO)
     *
     * @param string $content Galactapedia translation
     * @param string $pageContent Wikitext
     * @return array|string[] Array containing 'content' 'repl'
     */
    private function runTextReplacements(string $content, string $pageContent): array
    {
        $found = preg_match_all('/(?:^\((.+?)\|(.+?)\)$)+/m', $pageContent, $matches);

        if ($found === false || $found === 0 || count($matches[1]) !== count($matches[2])) {
            return [
                'content' => $content
            ];
        }

        foreach ($matches[1] as $key => $from) {
            if (trim($matches[2][$key]) === '') {
                continue;
            }

            $content = str_replace($from, $matches[2][$key], $content);
        }

        return [
            'content' => $content,
            'repl' => sprintf("\n%s", implode("\n", $matches[0]))
        ];
    }
}
