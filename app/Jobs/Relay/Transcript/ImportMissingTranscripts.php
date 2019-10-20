<?php

declare(strict_types=1);

namespace App\Jobs\Relay\Transcript\Import;

use App\Jobs\AbstractBaseDownloadData as BaseDownloadData;
use App\Models\Transcript\Transcript;
use Carbon\Carbon;
use Exception;
use GuzzleCloudflare\Middleware;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use SimpleXMLElement;

/**
 * Class ImportMissingTranscripts.
 */
class ImportMissingTranscripts extends BaseDownloadData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private const BASE_URL = 'https://relay.sc/feed/transcripts/atom';
    private const SOURCE_URL = 'source_url';

    /**
     * Execute the job.
     */
    public function handle()
    {
        app('Log')::info('Starting Missing Transcripts Download Job');

        $this->initClient();

        $this->importTranscripts(self::BASE_URL);

        app('Log')::info('Transcripts imported');
    }

    /**
     * {@inheritdoc}
     */
    protected function initClient(bool $withTokenHeader = true): void
    {
        $client = new Client([
            'cookies' => new CookieJar(),
            'headers' => [
                'Referer' => self::BASE_URL,
            ],
            'timeout' => 60.0,
        ]);

        /** @var \GuzzleHttp\HandlerStack $handlerStack */
        $handlerStack = $client->getConfig('handler');
        $handlerStack->push(Middleware::create());

        self::$client = $client;
    }

    /**
     * Walks all available feed urls and imports a transcript if missing.
     *
     * @param string $url
     */
    private function importTranscripts(string $url)
    {
        $response = self::$client->get($url);
        $feed = simplexml_load_string((string) $response->getBody());

        app('Log')::debug(sprintf('Got %d entries from feed.', count($feed->entry)));

        foreach ($feed->entry as $entry) {
            $url = $this->getPlainString($entry->id);

            if (!Transcript::query()->where(self::SOURCE_URL, $url)->exists()) {
                $this->importTranscript($entry);
            } else {
                app('Log')::debug(sprintf('Transcript %s exists', $url));
            }
        }

        $next = $this->extractNextLink($feed);
        if (null !== $next) {
            app('Log')::debug(sprintf('Next url exists, loading more transcripts from %s', $next));
            // Don't trigger CF
            try {
                $sleep = random_int(1000000, 10000000);
            } catch (Exception $e) {
                $sleep = 1000000;
            }

            app('Log')::debug(sprintf('Sleeping for %dms', $sleep));
            usleep($sleep);

            $this->importTranscripts($next);
        }
    }

    /**
     * Imports a transcript and its translation into the db.
     *
     * @param SimpleXMLElement $feedElement
     */
    private function importTranscript(SimpleXMLElement $feedElement)
    {
        $data = [
            self::SOURCE_URL => $this->getPlainString($feedElement->id),
            'source_title' => $this->getPlainString($feedElement->title),
            'source_published_at' => Carbon::parse((string) $feedElement->published)->toDateTimeString(),
            'updated_at' => Carbon::parse((string) $feedElement->updated)->toDateTimeString(),
        ];

        $translation = [
            'locale_code' => 'en_EN',
            'translation' => $this->cleanContent($this->getPlainString($feedElement->content)),
        ];

        if (!empty($data[self::SOURCE_URL]) && !empty($data['source_title']) && !empty($translation['translation'])) {
            app('Log')::info(sprintf('Importing Transcript: %s', $data[self::SOURCE_URL]));

            $transcript = new Transcript($data);
            $transcript->save($data);

            $transcript->translations()->create($translation);
        } else {
            app('Log')::warning(sprintf('Empty Transcript: %s', (string) $feedElement->link['href']));
        }
    }

    /**
     * @param SimpleXMLElement $feed
     *
     * @return string|null
     */
    private function extractNextLink(SimpleXMLElement $feed): ?string
    {
        foreach ($feed->link as $link) {
            if ('next' === (string) $link['rel']) {
                return (string) $link['href'];
            }
        }

        return null;
    }

    /**
     * @param SimpleXMLElement $element
     *
     * @return string
     */
    private function getPlainString(SimpleXMLElement $element): string
    {
        return trim((string) $element);
    }

    /**
     * Removes multiple New Lines and spaces.
     *
     * @param string $content
     *
     * @return string
     */
    private function cleanContent(string $content): string
    {
        // Remove Non breaking Spaces with normal space
        $cleanedContent = str_replace(['&nbsp;', "\xc2\xa0"], ' ', $content);

        // Replace multiple spaces with one
        $cleanedContent = preg_replace('/ +/', ' ', $cleanedContent);

        // Trim each Start of Line
        $cleanedContent = preg_replace('/^ +/m', '', $cleanedContent);

        return trim(html_entity_decode($cleanedContent));
    }
}
