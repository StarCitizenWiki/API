<?php

declare(strict_types=1);

namespace App\Jobs\Transcript\Translate;

use App\Models\Transcript\Transcript;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use InvalidArgumentException;
use Octfx\DeepLy\Exceptions\AuthenticationException;
use Octfx\DeepLy\Exceptions\QuotaException;
use Octfx\DeepLy\Exceptions\RateLimitedException;
use Octfx\DeepLy\Exceptions\TextLengthException;
use Octfx\DeepLy\HttpClient\CallException;
use Octfx\DeepLy\Integrations\Laravel\DeepLyFacade;

/**
 * Translate a singleTranscript.
 */
class TranslateTranscript implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private const DEEPL_MAX_LENGTH = 30000;

    /**
     * @var Transcript
     */
    private Transcript $transcript;

    /**
     * Create a new job instance.
     *
     * @param Transcript $transcript
     */
    public function __construct(Transcript $transcript)
    {
        $this->transcript = $transcript;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (null === $this->transcript->english()) {
            return;
        }

        app('Log')::info("Translating Transcript {$this->transcript->url}");

        $english = $this->transcript->english()->translation;
        $translation = '';

        try {
            if (mb_strlen($english) > self::DEEPL_MAX_LENGTH) {
                foreach (str_split_unicode($english, self::DEEPL_MAX_LENGTH) as $chunk) {
                    $chunkTranslation = DeepLyFacade::translate($chunk, 'DE', 'EN');
                    $translation .= " {$chunkTranslation}";
                }
            } else {
                $translation = DeepLyFacade::translate($english, 'DE', 'EN');
            }
        } catch (QuotaException $e) {
            app('Log')::warning('Deepl Quote exceeded!');

            $this->fail($e);

            return;
        } catch (RateLimitedException $e) {
            app('Log')::info('Got rate limit exception. Trying job again in 60 seconds.');

            $this->release(60);

            return;
        } catch (TextLengthException $e) {
            app('Log')::warning($e->getMessage());

            return;
        } catch (CallException | AuthenticationException | InvalidArgumentException $e) {
            app('Log')::warning(sprintf('%s: %s', 'Translation failed with Message', $e->getMessage()));

            $this->fail($e);

            return;
        }

        $this->transcript->translations()->updateOrCreate(
            [
                'locale_code' => 'de_DE',
            ],
            [
                'translation' => trim($translation),
                'proofread' => false,
            ]
        );
    }
}
