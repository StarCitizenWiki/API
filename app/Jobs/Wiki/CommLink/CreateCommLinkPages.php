<?php declare(strict_types = 1);

namespace App\Jobs\Wiki\CommLink;

use App\Models\Rsi\CommLink\CommLinksChanged;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateCommLinkPages implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $manager = app('mediawikiapi.manager');

        $manager->setConsumerFromCredentials(
            (string) config('services.wiki_translations.access_token'),
            (string) config('services.wiki_translations.access_secret')
        );
        $manager->setTokenFromCredentials(
            (string) config('services.wiki_translations.consumer_token'),
            (string) config('services.wiki_translations.consumer_secret')
        );

        CommLinksChanged::query()->where('type', 'translation')->chunk(
            100,
            function (Collection $commLinks) {
                $commLinks->each(
                    function (CommLinksChanged $commLinksChanged) {
                        dispatch(new CreateCommLinkPage($commLinksChanged->commLink));
                    }
                );
            }
        );

        CommLinksChanged::query()->where('type', 'translation')->delete();
    }
}
