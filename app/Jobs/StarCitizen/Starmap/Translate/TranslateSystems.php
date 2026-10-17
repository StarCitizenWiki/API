<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizen\Starmap\Translate;

use App\Models\StarCitizen\Starmap\Starsystem;
use App\Models\System\Language;
use App\Services\Translation\TranslationService;
use App\Traits\HandlesTranslationExceptions;
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
    use HandlesTranslationExceptions;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(TranslationService $translator): void
    {
        app('Log')::info('Translating Systems');

        [$targetLocale, $translationLocale] = TranslationService::resolveLocale();

        Starsystem::query()
            ->whereNotNull('translation')
            ->chunk(
                25,
                function (Collection $systems) use ($translator, $targetLocale, $translationLocale) {
                    foreach ($systems as $starsystem) {
                        $english = $starsystem->getTranslation('translation', Language::ENGLISH, false);
                        $existingTranslation = $starsystem->getTranslation('translation', $translationLocale, false);

                        if ($english === null || $english === '') {
                            continue;
                        }

                        if ($existingTranslation !== null && $existingTranslation !== '') {
                            continue;
                        }

                        app('Log')::info(sprintf('Translating system %s', $starsystem->name));
                        $translation = $this->translateOrAbort(
                            fn (): string => $translator->translate($english, $targetLocale),
                            ['system' => $starsystem->name],
                        );

                        if ($translation === false) {
                            return false;
                        }

                        if ($translation === null) {
                            continue;
                        }

                        $starsystem->setTranslation('translation', $translationLocale, $translation);
                        $starsystem->save();
                    }

                    return true;
                }
            );
    }
}
