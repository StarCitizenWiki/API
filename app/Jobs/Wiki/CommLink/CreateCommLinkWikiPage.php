<?php declare(strict_types = 1);

namespace App\Jobs\Wiki\CommLink;

use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use StarCitizenWiki\MediaWikiApi\Exceptions\ApiErrorException;

/**
 * Creates a Comm-Link on a Wiki
 */
class CreateCommLinkWikiPage implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var \App\Models\Rsi\CommLink\CommLink
     */
    private $commLink;

    /**
     * @var string
     */
    private $template;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\Rsi\CommLink\CommLink $commLink
     * @param string                            $template The Template to include before every translation
     */
    public function __construct(CommLink $commLink, string $template)
    {
        $this->commLink = $commLink;
        $this->template = $template;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app('Log')::info("Creating Wiki Page 'Comm-Link:{$this->commLink->cig_id}'");

        try {
            app('mediawikiapi')->edit("Comm-Link:{$this->commLink->cig_id}")->text(
                sprintf(
                    "%s\n%s",
                    $this->template,
                    $this->commLink->german()->translation
                )
            )->summary("Importing Comm-Link Translation {$this->commLink->cig_id}")->createOnly()->request();
        } catch (ApiErrorException $e) {
            app('Log')::error('Could not get an CSRF Token', $e->getResponse()->getErrors());

            $this->fail($e);

            return;
        }
    }
}
