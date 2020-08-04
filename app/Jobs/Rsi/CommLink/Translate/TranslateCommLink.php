<?php

declare(strict_types=1);

namespace App\Jobs\Rsi\CommLink\Translate;

use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use InvalidArgumentException;
use Octfx\DeepLy\DeepLy;
use Octfx\DeepLy\Exceptions\AuthenticationException;
use Octfx\DeepLy\Exceptions\QuotaException;
use Octfx\DeepLy\Exceptions\RateLimitedException;
use Octfx\DeepLy\Exceptions\TextLengthException;
use Octfx\DeepLy\HttpClient\CallException;
use Octfx\DeepLy\Integrations\Laravel\DeepLyFacade;

/**
 * Translate a single Comm-Link.
 */
class TranslateCommLink implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var CommLink
     */
    private CommLink $commLink;

    /**
     * List of categories that should be translated more formally
     *
     * @var array|string[]
     */
    private array $formalCategories = [
        'Lore',
        'Short Stories',
    ];

    /**
     * Create a new job instance.
     *
     * @param CommLink $commLink
     */
    public function __construct(CommLink $commLink)
    {
        $this->commLink = $commLink;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        app('Log')::info("Translating Comm-Link with ID {$this->commLink->cig_id}");

        if (null === optional($this->commLink->german())->translation) {
            $english = $this->commLink->english()->translation;
            $translation = '';
            $formality = 'less';

            if (in_array($this->commLink->category->name, $this->formalCategories, true)) {
                $formality = 'more';
            }

            try {
                if (mb_strlen($english) > DeepLy::MAX_TRANSLATION_TEXT_LEN) {
                    foreach (str_split_unicode($english, DeepLy::MAX_TRANSLATION_TEXT_LEN) as $chunk) {
                        $chunkTranslation = DeepLyFacade::translate($chunk, 'DE', 'EN', $formality);
                        $translation .= " {$chunkTranslation}";
                    }
                } else {
                    $translation = DeepLyFacade::translate($english, 'DE', 'EN', $formality);
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

            $this->commLink->translations()->updateOrCreate(
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
}
