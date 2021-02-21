<?php

declare(strict_types=1);

namespace App\Jobs\Rsi\CommLink\Translate;

use App\Models\Rsi\CommLink\CommLink;
use App\Services\TranslateText;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Octfx\DeepLy\Exceptions\AuthenticationException;
use Octfx\DeepLy\Exceptions\QuotaException;
use Octfx\DeepLy\Exceptions\RateLimitedException;
use Octfx\DeepLy\Exceptions\TextLengthException;
use Octfx\DeepLy\HttpClient\CallException;

/**
 * Translate a single Comm-Link.
 */
class TranslateCommLink implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var CommLink
     */
    private CommLink $commLink;

    /**
     * List of categories that should be translated more formally
     *
     * @var array|string[]
     */
    private array $formalCategories = [
        'Lore',
        'Short Stories',
    ];

    /**
     * Create a new job instance.
     *
     * @param CommLink $commLink
     */
    public function __construct(CommLink $commLink)
    {
        $this->commLink = $commLink;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        app('Log')::info("Translating Comm-Link with ID {$this->commLink->cig_id}");
        $targetLocale = config('services.deepl.target_locale');

        if (null !== optional($this->commLink->german())->translation) {
            $this->delete();
            return;
        }

        $english = $this->commLink->english()->translation;
        $formality = 'less';

        if (in_array($this->commLink->category->name, $this->formalCategories, true)) {
            $formality = 'more';
        }

        $translator = new TranslateText($english);

        // phpcs:disable
        try {
            $translation = $translator->translate(config('services.deepl.target_locale'), $formality);
        } catch (
            QuotaException |
            CallException |
            AuthenticationException |
            InvalidArgumentException |
            TextLengthException $e
        ) {
            $this->fail($e);

            return;
        } catch (RateLimitedException $e) {
            $this->release(60);

            return;
        }
        // phpcs:enable

        $this->commLink->translations()->updateOrCreate(
            [
                'locale_code' => sprintf('%s_%s', Str::lower($targetLocale), $targetLocale),
            ],
            [
                'translation' => trim($translation),
                'proofread' => false,
            ]
        );
    }
}
