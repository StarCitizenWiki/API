<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizen\Galactapedia;

use App\Models\StarCitizen\Galactapedia\Article;
use App\Services\Translation\TranslationService;
use App\Traits\TranslatesModelBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TranslateArticle implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use TranslatesModelBatch;

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
        Log::info("Translating Galactapedia Article {$this->article->cig_id}");
        [$targetLocale, $translationLocale] = TranslationService::resolveLocale();

        $this->translateModel(
            $this->article,
            $translationLocale,
            fn (Article $article, string $english): string => $translator->translate($english, $targetLocale),
            fn (Article $article): array => ['article_id' => $article->cig_id],
            function (string $english, ?string $existingTranslation): bool {
                if ($existingTranslation !== null && ((strlen($existingTranslation) / strlen($english)) > 0.80)) {
                    $this->delete();

                    return true;
                }

                return false;
            },
        );
    }
}
