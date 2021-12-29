<?php

declare(strict_types=1);

namespace App\Jobs\Wiki\Galactapedia;

use App\Jobs\AbstractBaseDownloadData;
use App\Jobs\Wiki\ApproveRevisions;
use App\Models\StarCitizen\Galactapedia\Article;
use App\Models\StarCitizen\Galactapedia\ArticleProperty;
use App\Models\StarCitizen\Galactapedia\Category;
use App\Services\CommonMark\WikiTextRenderer;
use App\Services\WrappedWiki;
use App\Traits\GetWikiCsrfTokenTrait;
use Exception;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Octfx\DeepLy\Integrations\Laravel\DeepLyFacade;
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
    /* jscpd:ignore-start */
    public static array $categoryTranslations = [
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
        "Commerce" => "Unternehmen",
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
    /* jscpd:ignore-end */

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

        $this->title = WrappedWiki::getRedirectTitle($this->article->cleanTitle);
        $wikiText = WrappedWiki::getWikiPageText(Article::normalizeContent($this->title));

        if (preg_match('/(REDIRECT|WEITERLEITUNG)/', $wikiText ?? '') === 1) {
            app('Log')::warning(sprintf('Could not determine redirect title for "%s"', $this->title));
            $this->release(7200);
            return;
        }

        if ($wikiText === null && WrappedWiki::pageExists($this->title)) {
            app('Log')::warning(sprintf('Could not load content for "%s"', $this->title));
            $this->release(7200);
            return;
        }

        try {
            $text = $this->getFormattedText($this->getArticleText(), $wikiText);

            // Skip if texts are equal
            if (strcmp($text, $wikiText ?? '') === 0) {
                $this->article->update(['in_wiki' => true]);
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
                ], false, true));
            }

            $this->article->update(['in_wiki' => true]);

            UploadGalactapediaWikiImages::dispatch($this->article);
        } catch (ConnectException $e) {
            $this->release(60);

            return;
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
-->[[Category:Galactapedia]]%s<!--%s
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

            if (strpos($pageContent, '(Getrennter erster Satz)') !== false) {
                if (isset($content['repl'])) {
                    $content['repl'] = sprintf("%s\n(Getrennter erster Satz)", $content['repl']);
                } else {
                    $content['repl'] = "\n(Getrennter erster Satz)";
                }

                $text = explode('. ', $content['content'], 2);
                $content['content'] = implode(".\n\n", array_map('trim', $text));
            }

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
            return sprintf(
                "|%s=%s",
                $key,
                DeepLyFacade::translate($item, config('services.deepl.target_locale'), 'EN')
            );
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
     * Thx @alistair
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
        $cats = $this->article->categories
            ->map(function (Category $category) {
                return sprintf(
                    '[[Category:%s]]',
                    self::$categoryTranslations[$category->name] ?? $category->name
                );
            })
            ->implode("\n");

        if (strpos($cats, 'Waffe') !== false && strpos($cats, 'Unternehmen') !== false) {
            $cats = str_replace('Category:Waffe', 'Category:Waffenhersteller', $cats);
        }

        return $cats;
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
        $tags = ['[[Humans|Human]]', '[[Menschen|Human]]'];

        // Replace humans -> menschen
        if (preg_match('/\[\[(Humans|Menschen)\|Human]].*(konzern|hersteller|händler|mann)/iu', $content)) {
            $content = str_replace($tags, '[[Menschen|menschlicher]]', $content);
        } elseif (preg_match('/\[\[(Humans|Menschen)\|Human]].*(show|serie|regierung)/i', $content)) {
            $content = str_replace($tags, '[[Menschen|menschliche]]', $content);
        } else {
            $content = str_replace($tags, '[[Menschen|menschliches]]', $content);
        }

        // Add a space between two consecutive links
        $content = str_replace(']][[', ']] [[', $content);

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
