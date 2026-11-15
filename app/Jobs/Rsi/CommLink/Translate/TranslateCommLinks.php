<?php

declare(strict_types=1);

namespace App\Jobs\Rsi\CommLink\Translate;

use App\Models\Rsi\CommLink\CommLink;
use App\Services\Translation\TranslationService;
use App\Traits\TranslatesModelBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Translate all Comm-Links without a translation in the configured target locale.
 */
class TranslateCommLinks implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use TranslatesModelBatch;

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
        Log::info('Translating Comm-Links');

        [$targetLocale] = TranslationService::resolveLocale();

        $query = CommLink::query()
            ->with(['category'])
            ->whereNotNull('translation');

        if ($this->commLinkIds !== []) {
            $query->whereIn('cig_id', $this->commLinkIds);
        }

        $this->translateChunk(
            $query,
            function (CommLink $commLink, string $english) use ($translator, $targetLocale): string {
                $formality = 'less';
                if ($commLink->category !== null && in_array($commLink->category->name, $this->formalCategories, true)) {
                    $formality = 'more';
                }

                return $translator->translate($english, $targetLocale, 'en', $formality);
            },
            function (CommLink $commLink): array {
                Log::info(sprintf('Translating Comm-Link %d', $commLink->cig_id));

                return ['comm_link_id' => $commLink->cig_id];
            },
        );
    }
}
