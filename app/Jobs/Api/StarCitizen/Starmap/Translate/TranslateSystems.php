<?php

declare(strict_types=1);

namespace App\Jobs\Api\StarCitizen\Starmap\Translate;

use App\Models\Api\StarCitizen\Starmap\Starsystem\Starsystem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Octfx\DeepLy\Exceptions\AuthenticationException;
use Octfx\DeepLy\Exceptions\QuotaException;
use Octfx\DeepLy\Exceptions\RateLimitedException;
use Octfx\DeepLy\Exceptions\TextLengthException;
use Octfx\DeepLy\HttpClient\CallException;
use Octfx\DeepLy\Integrations\Laravel\DeepLyFacade;

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
     *
     * @return void
     */
    public function handle(): void
    {
        app('Log')::info('Translating Systems');

        Starsystem::query()->whereHas(
            'translations',
            function (Builder $query) {
                $query->where('locale_code', 'en_EN')->whereRaw("translation <> ''");
            }
        )
            ->chunk(
                25,
                function (Collection $systems) {
                    $systems->each(
                        function (Starsystem $starsystem) {
                            if (null !== optional($starsystem->german())->translation) {
                                return;
                            }

                            try {
                                app('Log')::info(sprintf('Translating system %s', $starsystem->name));
                                $translation = DeepLyFacade::translate(
                                    $starsystem->english()->translation,
                                    config('services.deepl.target_locale'),
                                    'EN',
                                    'more'
                                );
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
                                app('Log')::warning(
                                    sprintf('%s: %s', 'Translation failed with Message', $e->getMessage())
                                );

                                $this->fail($e);

                                return;
                            }

                            $starsystem->translations()->updateOrCreate(
                                [
                                    'locale_code' => 'de_DE',
                                ],
                                [
                                    'translation' => trim($translation),
                                ]
                            );
                        }
                    );
                }
            );
    }
}
