<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizen\Galactapedia;

use App\Models\StarCitizen\Galactapedia\Article;
use App\Services\TranslateText;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Octfx\DeepLy\Exceptions\RateLimitedException;

class TranslateArticle implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private Article $article;

    /**
     * Create a new job instance.
     *
     * @param Article $article
     */
    public function __construct(Article $article)
    {
        $this->article = $article;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        app('Log')::info("Translating Galactapedia Article {$this->article->cig_id}");
        $targetLocale = config('services.deepl.target_locale');


        $english = $this->article->english()->translation;
        $german = optional($this->article->german())->translation;

        // Delete job german and english translation length don't differ in length by <= 20%
        if (null !== $german && ((strlen($german) / strlen($english)) > 0.80)) {
            $this->delete();
            return;
        }

        $translator = new TranslateText($english);

        try {
            $translation = $translator->translate(config('services.deepl.target_locale'));
        } catch (RateLimitedException $e) {
            $this->release(60);

            return;
        } catch (Exception $e) {
            $this->fail($e);

            return;
        }

        $this->article->translations()->updateOrCreate(
            [
                'locale_code' => sprintf('%s_%s', Str::lower($targetLocale), $targetLocale),
            ],
            [
                'translation' => trim(TranslateText::runTextReplacements($translation)),
                'proofread' => false,
            ]
        );
    }
}
