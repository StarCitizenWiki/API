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
 * Creates a Comm Link on a Wiki
 */
class CreateCommLinkPage implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private const TEMPLATE_CLEANUP = '{{cleanup}}';
    private const TEMPLATE_COMM_LINK = '{{Comm-Link}}';

    /**
     * @var \App\Models\Rsi\CommLink\CommLink
     */
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
        try {
            app('mediawikiapi')->edit("Comm-Link:{$this->commLink->cig_id}")->text(
                sprintf(
                    "%s\n<noinclude>%s</noinclude>\n%s",
                    self::TEMPLATE_CLEANUP,
                    self::TEMPLATE_COMM_LINK,
                    $this->commLink->german()->translation
                )
            )->summary("Importing Comm-Link Translation {$this->commLink->cig_id}")->request();
        } catch (ApiErrorException $e) {
            $this->fail($e);

            return;
        }
    }
}
