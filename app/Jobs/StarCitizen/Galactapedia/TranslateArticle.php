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
        $targetLocale = config('services.deepl.target_locale', 'de');

        $english = $this->article->getTranslation('translation', Language::ENGLISH, false);
        $german = $this->article->getTranslation('translation', Language::GERMAN, false);

        if ($english === null || $english === '') {
            return;
        }

        // Delete job german and english translation length don't differ in length by <= 20%
        if ($german !== null && ((strlen($german) / strlen($english)) > 0.80)) {
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

        $this->article->setTranslation('translation', Language::GERMAN, $translation);
        $this->article->save();
    }
}
