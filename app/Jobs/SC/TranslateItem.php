<?php

declare(strict_types=1);

namespace App\Jobs\SC;

use App\Models\SC\Item\Item;
use App\Services\TranslateText;
use Exception;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Octfx\DeepLy\Exceptions\RateLimitedException;

class TranslateItem implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private Item $item;

    /**
     * Create a new job instance.
     *
     * @param Item $item
     */
    public function __construct(Item $item)
    {
        $this->item = $item;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        app('Log')::info("Translating Item {$this->item->name}");
        $targetLocale = config('services.deepl.target_locale');

        $english = $this->item->english()->translation;
        $german = optional($this->item->german())->translation;

        // Delete job german and english translation length don't differ in length by <= 20%
        if (empty($english) || (null !== $german && ((strlen($german) / strlen($english)) > 0.80))) {
            $this->delete();
            return;
        }

        $translator = new TranslateText($english);

        try {
            $translation = $translator->translate(config('services.deepl.target_locale'));
        } catch (ConnectException | RateLimitedException $e) {
            $this->release(60);

            return;
        } catch (Exception $e) {
            $this->fail($e);

            return;
        }

        $this->item->translations()->updateOrCreate(
            [
                'locale_code' => sprintf('%s_%s', Str::lower($targetLocale), $targetLocale),
            ],
            [
                'translation' => trim(TranslateText::runTextReplacements($translation)),
            ]
        );
    }
}
