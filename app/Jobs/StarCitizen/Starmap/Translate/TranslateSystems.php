<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizen\Starmap\Translate;

use App\Models\StarCitizen\Starmap\Starsystem;
use App\Services\Translation\TranslationService;
use App\Traits\TranslatesModelBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Translate all systems
 */
class TranslateSystems implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use TranslatesModelBatch;

    /**
     * Execute the job.
     */
    public function handle(TranslationService $translator): void
    {
        Log::info('Translating Systems');

        [$targetLocale] = TranslationService::resolveLocale();

        $this->translateChunk(
            Starsystem::query()->whereNotNull('translation'),
            fn (Starsystem $starsystem, string $english): string => $translator->translate($english, $targetLocale),
            function (Starsystem $starsystem): array {
                Log::info(sprintf('Translating system %s', $starsystem->name));

                return ['system' => $starsystem->name];
            },
        );
    }
}
