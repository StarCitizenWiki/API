<?php

declare(strict_types=1);

namespace App\Jobs\Rsi\CommLink\Translate;

use App\Exceptions\Translation\AuthenticationException;
use App\Exceptions\Translation\QuotaExceededException;
use App\Exceptions\Translation\RateLimitException;
use App\Exceptions\Translation\TranslationException;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\System\Language;
use App\Services\Translation\TranslationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

/**
 * Translate all Comm-Links without a translation in the configured target locale.
 */
class TranslateCommLinks implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Categories that should be translated with more formal language.
     */
    private array $formalCategories = ['Lore', 'Short Stories'];

    /**
     * @param  array<int, int>  $commLinkIds
     */
    public function __construct(public readonly array $commLinkIds = []) {}

    /**
     * Execute the job.
     */
    public function handle(TranslationService $translator): void
    {
        app('Log')::info('Translating Comm-Links');

        $targetLocale = (string) config('services.deepl.target_locale', Language::GERMAN);
        $configuredTranslationLocale = config('services.deepl.translation_locale');
        $translationLocale = is_string($configuredTranslationLocale) && $configuredTranslationLocale !== ''
            ? $configuredTranslationLocale
            : (strtolower(substr($targetLocale, 0, 2)) ?: Language::GERMAN);

        $query = CommLink::query()
            ->with(['category'])
            ->whereNotNull('translation');

        if ($this->commLinkIds !== []) {
            $query->whereIn('cig_id', $this->commLinkIds);
        }

        $query->chunk(
            25,
            function (Collection $commLinks) use ($translator, $targetLocale, $translationLocale) {
                foreach ($commLinks as $commLink) {
                    $english = $commLink->getTranslation('translation', Language::ENGLISH, false);
                    $existingTranslation = $commLink->getTranslation('translation', $translationLocale, false);

                    if ($english === null || $english === '') {
                        continue;
                    }

                    if ($existingTranslation !== null && $existingTranslation !== '') {
                        continue;
                    }

                    $formality = 'less';
                    if ($commLink->category !== null && in_array($commLink->category->name, $this->formalCategories, true)) {
                        $formality = 'more';
                    }

                    try {
                        app('Log')::info(sprintf('Translating Comm-Link %d', $commLink->cig_id));
                        $translation = $translator->translate($english, $targetLocale, 'en', $formality);
                    } catch (QuotaExceededException $e) {
                        app('Log')::warning('DeepL quota exceeded');

                        $this->fail($e);

                        return false;
                    } catch (RateLimitException $e) {
                        app('Log')::info('Got rate limit exception. Trying job again in 60 seconds.');

                        $this->release(60);

                        return false;
                    } catch (AuthenticationException $e) {
                        app('Log')::error('DeepL authentication failed', ['error' => $e->getMessage()]);

                        $this->fail($e);

                        return false;
                    } catch (TranslationException $e) {
                        app('Log')::warning('Translation failed', [
                            'comm_link_id' => $commLink->cig_id,
                            'error' => $e->getMessage(),
                        ]);

                        continue;
                    }

                    $commLink->setTranslation('translation', $translationLocale, $translation);
                    $commLink->save();
                }

                return true;
            }
        );
    }
}
