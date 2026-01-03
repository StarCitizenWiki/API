<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizen\Starmap\Translate;

use App\Exceptions\Translation\AuthenticationException;
use App\Exceptions\Translation\QuotaExceededException;
use App\Exceptions\Translation\RateLimitException;
use App\Exceptions\Translation\TranslationException;
use App\Models\StarCitizen\Starmap\Starsystem\Starsystem;
use App\Models\System\Language;
use App\Services\Translation\TranslationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
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

        Starsystem::query()->whereHas(
            'translations',
            function (Builder $query) {
                $query->where('locale_code', Language::ENGLISH)->whereRaw("translation <> ''");
            }
        )
            ->chunk(
                25,
                function (Collection $systems) use ($translator, $targetLocale) {
                    $systems->each(
                        function (Starsystem $starsystem) use ($translator, $targetLocale) {
                            if (optional($starsystem->german())->translation !== null) {
                                return;
                            }

                            try {
                                app('Log')::info(sprintf('Translating system %s', $starsystem->name));
                                $translation = $translator->translate(
                                    $starsystem->english()->translation,
                                    $targetLocale
                                );
                            } catch (QuotaExceededException $e) {
                                app('Log')::warning('DeepL quota exceeded');

                                $this->fail($e);

                                return;
                            } catch (RateLimitException $e) {
                                app('Log')::info('Got rate limit exception. Trying job again in 60 seconds.');

                                $this->release(60);

                                return;
                            } catch (AuthenticationException $e) {
                                app('Log')::error('DeepL authentication failed', ['error' => $e->getMessage()]);

                                $this->fail($e);

                                return;
                            } catch (TranslationException $e) {
                                app('Log')::warning('Translation failed', [
                                    'system' => $starsystem->name,
                                    'error' => $e->getMessage(),
                                ]);

                                return;
                            }

                            $starsystem->translations()->updateOrCreate(
                                [
                                    'locale_code' => 'de_DE',
                                ],
                                [
                                    'translation' => $translation,
                                ]
                            );
                        }
                    );
                }
            );
    }
}
