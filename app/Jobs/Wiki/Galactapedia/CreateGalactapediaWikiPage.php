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
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use JsonException;
use Normalizer;
use RuntimeException;
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

    /**
     * TODO Move into DB
     *
     * @var string[]
     */
    private static array $categoryTranslations = [
        "Human" => "Menschen",
        "Vanduul" => "Vanduul",
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
        app('Log')::info("Creating Wiki Page '{$this->article->title}'");

        $wikiText = $this->getWikiPageText();
        $this->loadThumbnailMetadata();

        try {
            $text = $this->getFormattedText($this->getArticleText(), $wikiText);

            // Skip if texts are equal
            if (strcmp($text, $wikiText ?? '') === 0) {
                $this->delete();
                return;
            }

            MediaWikiApi::edit($this->article->title)
                ->text($text)
                ->redirect(1)
                ->summary(
                    sprintf(
                        "%s Galactapedia Article %s",
                        ($wikiText === null ? 'Importing' : 'Updating'),
                        $this->article->title
                    )
                )
                ->csrfToken($this->token)
                ->markBotEdit()
                ->request();

            if (config('services.wiki_approve_revs.access_secret', null) !== null) {
                dispatch(new ApproveRevisions([$this->article->title]));
            }

            $this->uploadGalactapediaImage();
        } catch (GuzzleException | RuntimeException $e) {
            app('Log')::error('Could not get an CSRF Token', $e->getResponse()->getErrors());

            $this->fail($e);

            return;
        }
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
            return sprintf('[[Category:%s]]', self::$categoryTranslations[$category->name] ?? $category->name);
        });
        $categories->push('[[Category:Galactapedia]]');

        $uploader = new UploadWikiImage();
        try {
            $uploader->upload(
                sprintf(
                    'Galactapedia_%s.%s',
                    $this->article->title,
                    (str_contains($this->response->header('Content-Type'), 'jpeg') ? 'jpg' : 'png')
                ),
                $this->article->thumbnail,
                [
                    'date' => $this->response->header('Last-Modified'),
                    'sources' => implode(',', [
                        $this->article->thumbnail,
                        $this->article->url,
                    ]),
                    'description' => sprintf('Bild des Galactapedia Artikels %s', $this->article->title),
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
                ->titles(
                    Normalizer::isNormalized($this->article->title) ?
                        $this->article->title :
                        Normalizer::normalize($this->article->title)
                )
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

        if ($text !== null && !Normalizer::isNormalized($text)) {
            $text = Normalizer::normalize($text);
        }

        return Article::fixMarkdownLinks($text);
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
%s<div class="imported-text"><!--

!!! Achtung, der folgende Text wird automatisiert verwaltet, alle Änderungen werden gelöscht. !!!
!!! Du kannst Text in einer neuen Zeile unter END-- einfügen. Dieser wird nicht gelöscht.     !!!

START-->%s%s<!--
-->%s<!--
END--></div>
FORMAT;

        $content = $this->createContent($markdown, str_contains($pageContent ?? '', 'galactapedia-box'));
        $categories = $this->createCategories();
        $ref = $this->createRef();

        if ($pageContent !== null) {
            $formatted = sprintf(
                $format,
                '', // Don't replace template
                $content,
                $ref,
                $categories,
            );

            return preg_replace(
                '/<div class="imported-text"><!--.*END--><\/div>/s',
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
                return sprintf('[[%s]]', $article->title);
            })
            ->implode("<br>\n");

        // The actual template content
        return <<<TEMPLATE
{{Galactapedia
|title={$this->article->title}
|image=Galactapedia_{$this->article->title}.{$fileEnding}
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
        $wikitext = trim($parser->render($markdown));

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
            $this->article->title,
            $this->article->created_at->format('d.m.Y'),
            $this->article->updated_at->format('d.m.Y'),
        );
    }
}