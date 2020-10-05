<?php

declare(strict_types=1);

namespace App\Jobs\Wiki\Transcript;

use App\Models\Transcript\Transcript;
use App\Traits\Jobs\GetCommLinkWikiPageInfoTrait as GetCommLinkWikiPageInfo;
use App\Traits\Jobs\LoginWikiBotAccountTrait as LoginWikiBotAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;
use StarCitizenWiki\MediaWikiApi\Facades\MediaWikiApi;

/**
 * Create Comm-Link Wiki Pages.
 */
class CreateTranscriptWikiPages implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use GetCommLinkWikiPageInfo;
    use LoginWikiBotAccount;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        app('Log')::info('Starting creation of Transcript Wiki Pages');

        $this->loginWikiBotAccount();

        $token = MediaWikiApi::query()->meta('tokens')->request();
        if ($token->hasErrors()) {
            $this->fail(
                new RuntimeException(
                    sprintf('%s: %s', 'Token retrieval failed', collect($token->getErrors())->implode('code', ', '))
                )
            );

            return;
        }

        $token = $token->getQuery()['tokens']['csrftoken'];

        $config = $this->getCommLinkConfig();
        $config['token'] = $token;

        app('Log')::debug('Current config:', $config);

        Transcript::query()->whereHas(
            'translations',
            static function (Builder $query) {
                $query->where('locale_code', 'de_DE')->whereRaw("translation <> ''");
            }
        )->chunk(
            100,
            function (Collection $transcripts) use ($config) {
                try {
                    $pageInfoCollection = $this->getPageInfoForCommLinks($transcripts, true);
                } catch (RuntimeException $e) {
                    app('Log')::error($e->getMessage());

                    $this->fail($e);

                    return;
                }

                $transcripts->each(
                    static function (Transcript $transcript) use ($pageInfoCollection, $config) {
                        $wikiPage = $pageInfoCollection->get($transcript->cig_id, []);

                        if (isset($wikiPage['missing'])) {
                            dispatch(new CreateTranscriptWikiPage($transcript, $config['token'], $config['template']));
                        }
                    }
                );
            }
        );
    }
}
