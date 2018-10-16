<?php declare(strict_types = 1);

namespace App\Jobs\Rsi\CommLink\Translate;

use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\CommLinksChanged;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use StarCitizenWiki\DeepLy\Integrations\Laravel\DeepLyFacade;

class TranslateCommLink implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $commLink;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\Rsi\CommLink\CommLink $commLink
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
    public function handle()
    {
        if (null === optional($this->commLink->german())->translation) {
            try {
                $translation = DeepLyFacade::translate($this->commLink->english()->translation, 'DE', 'EN');
            } catch (\Exception $e) {
                $this->fail($e);

                return;
            }

            $this->commLink->translations()->updateOrCreate(
                [
                    'locale_code' => 'de_DE',
                ],
                [
                    'translation' => $translation,
                    'proofread' => false,
                ]
            );
        }

        CommLinksChanged::create(
            [
                'comm_link_id' => $this->commLink->id,
                'had_content' => false,
                'type' => 'translation',
            ]
        );
    }
}
