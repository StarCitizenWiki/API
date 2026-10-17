<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizen\Galactapedia;

use App\Models\StarCitizen\Galactapedia\Article;
use App\Models\System\Language;
use App\Services\Translation\TranslationService;
use App\Traits\HandlesTranslationExceptions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TranslateArticle implements ShouldQueue
{
    use Dispatchable;
    use HandlesTranslationExceptions;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private Article $article;

    /**
     * Create a new job instance.
     */
    public function __construct(Article $article)
    {
        $this->article = $article;
    }

    /**
     * Execute the job.
     */
    public function handle(TranslationService $translator): void
    {
        app('Log')::info("Translating Galactapedia Article {$this->article->cig_id}");
        [$targetLocale, $translationLocale] = TranslationService::resolveLocale();

        $english = $this->article->getTranslation('translation', Language::ENGLISH, false);
        $existingTranslation = $this->article->getTranslation('translation', $translationLocale, false);

        if ($english === null || $english === '') {
            return;
        }

        // Delete job if an existing translation is already close in length to the English text.
        if ($existingTranslation !== null && ((strlen($existingTranslation) / strlen($english)) > 0.80)) {
            $this->delete();

            return;
        }

        $translation = $this->translateOrAbort(
            fn (): string => $translator->translate($english, $targetLocale),
            ['article_id' => $this->article->cig_id],
        );

        if ($translation === null || $translation === false) {
            return;
        }

        $this->article->setTranslation('translation', $translationLocale, $translation);
        $this->article->save();
    }
}
