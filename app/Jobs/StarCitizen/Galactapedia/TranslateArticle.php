<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizen\Galactapedia;

use App\Exceptions\Translation\QuotaExceededException;
use App\Exceptions\Translation\RateLimitException;
use App\Exceptions\Translation\TranslationException;
use App\Models\StarCitizen\Galactapedia\Article;
use App\Models\System\Language;
use App\Services\Translation\TranslationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TranslateArticle implements ShouldQueue
{
    use Dispatchable;
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
        $targetLocale = (string) config('services.deepl.target_locale', Language::GERMAN);
        $configuredTranslationLocale = config('services.deepl.translation_locale');
        $translationLocale = is_string($configuredTranslationLocale) && $configuredTranslationLocale !== ''
            ? $configuredTranslationLocale
            : (strtolower(substr($targetLocale, 0, 2)) ?: Language::GERMAN);

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

        try {
            $translation = $translator->translate($english, $targetLocale);
        } catch (RateLimitException $e) {
            $this->release(60);

            return;
        } catch (QuotaExceededException $e) {
            $this->fail($e);

            return;
        } catch (TranslationException $e) {
            $this->fail($e);

            return;
        }

        $this->article->setTranslation('translation', $translationLocale, $translation);
        $this->article->save();
    }
}
