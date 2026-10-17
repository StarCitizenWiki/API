<?php

declare(strict_types=1);

namespace App\Jobs\Rsi\CommLink\Translate;

use App\Models\Rsi\CommLink\CommLink;
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
 * Translate all Comm-Links without a translation in the configured target locale.
 */
class TranslateCommLinks implements ShouldQueue
{
    use Dispatchable;
    use HandlesTranslationExceptions;
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

        [$targetLocale, $translationLocale] = TranslationService::resolveLocale();

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

                    app('Log')::info(sprintf('Translating Comm-Link %d', $commLink->cig_id));
                    $translation = $this->translateOrAbort(
                        fn (): string => $translator->translate($english, $targetLocale, 'en', $formality),
                        ['comm_link_id' => $commLink->cig_id],
                    );

                    if ($translation === false) {
                        return false;
                    }

                    if ($translation === null) {
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
