<?php

declare(strict_types=1);

namespace App\Jobs\Wiki\Galactapedia;

use App\Jobs\AbstractBaseDownloadData;
use App\Models\StarCitizen\Galactapedia\Article;
use App\Models\StarCitizen\Galactapedia\ArticleProperty;
use App\Models\StarCitizen\Galactapedia\Category;
use App\Services\CommonMark\WikiTextRenderer;
use App\Services\UploadWikiImage;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
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

        try {
            $text = $this->getFormattedText($this->getArticleText(), $wikiText);

            // Skip if texts are equal
            if (strcmp($text, $wikiText ?? '') === 0) {
                $this->delete();
                return;
            }

            MediaWikiApi::edit($this->article->title)
                ->text($text)
                ->summary("Importing Galactapedia Article {$this->article->title}")
                ->csrfToken($this->token)
                ->markBotEdit()
                ->request();

            $this->uploadGalactapediaImage();
        } catch (GuzzleException | RuntimeException $e) {
            app('Log')::error('Could not get an CSRF Token', $e->getResponse()->getErrors());

            $this->fail($e);

            return;
        }
    }

    private function uploadGalactapediaImage(): void
    {
        $client = $this->makeClient();
        $response = $client->head($this->article->thumbnail);

        // Todo: Default image has exact size of 5003 bytes
        if (
            $response->header('ETag') === '278879e3c41a001689260f0933a7f4ba' ||
            $response->header('Content-Length') === '5003'
        ) {
            return;
        }

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
                    (str_contains($response->header('Content-Type'), 'jpeg') ? 'jpg' : 'png')
                ),
                $this->article->thumbnail,
                [
                    'date' => $response->header('Last-Modified'),
                    'sources' => implode(',', [
                        $this->article->thumbnail,
                        $this->article->url,
                    ]),
                    'description' => sprintf('Bild des Galactapedia Artikels %s', $this->article->title),
                    'filesize' => $response->header('Content-Length'),
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
                ->titles($this->article->title)
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

        return $text;
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
        $parser = new WikiTextRenderer();

        $format = <<<FORMAT
%s<div class="imported-text"><!--

!!! Achtung, der folgende Text wird automatisiert verwaltet, alle Änderungen werden gelöscht. !!!
!!! Du kannst Text in einer neuen Zeile unter END--  einfügen. Dieser wird nicht gelöscht.    !!!

START-->%s<!--
-->%s<!--
END--></div>
FORMAT;

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

        $template = <<<TEMPLATE
{{Galactapedia
|title={$this->article->title}
|image=Galactapedia_{$this->article->title}.jpg
{$properties->map(function ($item, $key) {
            return sprintf("|%s=%s", $key, $item);
        })
            ->implode("\n")}
|related={$this->article->related->map(function (Article $article) {
                return sprintf('[[%s]]', $article->title);
            })
            ->implode("<br>\n")}
}}
TEMPLATE;

        $categoryMapper = function (Category $category) {
            return sprintf('[[Category:%s]]', self::$categoryTranslations[$category->name] ?? $category->name);
        };

        if (1 == 2 && $pageContent !== null) {
            $formatted = sprintf(
                $format,
                '', // Don't replace template
                trim($parser->render($markdown)),
                $this->article->categories->map($categoryMapper)->implode("\n"),
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
            $template,
            trim($parser->render($markdown)),
            $this->article->categories->map($categoryMapper)->implode("\n"),
        );
    }
}
