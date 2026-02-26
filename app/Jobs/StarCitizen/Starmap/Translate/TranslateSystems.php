<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizen\Starmap\Translate;

use App\Exceptions\Translation\AuthenticationException;
use App\Exceptions\Translation\QuotaExceededException;
use App\Exceptions\Translation\RateLimitException;
use App\Exceptions\Translation\TranslationException;
use App\Models\StarCitizen\Starmap\Starsystem;
use App\Models\System\Language;
use App\Services\Translation\TranslationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

/**
 * Translate all systems
 */
class TranslateSystems implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(TranslationService $translator): void
    {
        app('Log')::info('Translating Systems');

        $targetLocale = config('services.deepl.target_locale', 'de');

        Starsystem::query()
            ->whereNotNull('translation')
            ->chunk(
                25,
                function (Collection $systems) use ($translator, $targetLocale) {
                    foreach ($systems as $starsystem) {
                        $english = $starsystem->getTranslation('translation', Language::ENGLISH, false);
                        $german = $starsystem->getTranslation('translation', Language::GERMAN, false);

                        if ($english === null || $english === '') {
                            continue;
                        }

                        if ($german !== null && $german !== '') {
                            continue;
                        }

                        try {
                            app('Log')::info(sprintf('Translating system %s', $starsystem->name));
                            $translation = $translator->translate(
                                $english,
                                $targetLocale
                            );
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
                                'system' => $starsystem->name,
                                'error' => $e->getMessage(),
                            ]);

                            continue;
                        }

                        $starsystem->setTranslation('translation', Language::GERMAN, $translation);
                        $starsystem->save();
                    }

                    return true;
                }
            );
    }
}
