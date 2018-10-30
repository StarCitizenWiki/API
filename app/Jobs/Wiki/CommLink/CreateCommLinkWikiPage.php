<?php declare(strict_types = 1);

namespace App\Jobs\Wiki\CommLink;

use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Normalizer;
use StarCitizenWiki\MediaWikiApi\Exceptions\ApiErrorException;
use StarCitizenWiki\MediaWikiApi\Facades\MediaWikiApi;

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
     * @var string CSRF Token
     */
    private $token;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\Rsi\CommLink\CommLink $commLink
     * @param string                            $token
     * @param string                            $template The Template to include before every translation
     */
    public function __construct(CommLink $commLink, string $token, string $template)
    {
        $this->commLink = $commLink;
        $this->token = $token;
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
            $text = $this->commLink->german()->translation;
            if (!Normalizer::isNormalized($text)) {
                $text = Normalizer::normalize($text);
            }

            $response = MediaWikiApi::edit("Comm-Link:{$this->commLink->cig_id}")->text(
                sprintf(
                    "%s\n%s",
                    $this->template,
                    $text
                )
            )
                ->summary("Importing Comm-Link Translation {$this->commLink->cig_id}")
                ->csrfToken($this->token)
                ->markBotEdit()
                ->createOnly()
                ->request();
        } catch (ApiErrorException $e) {
            app('Log')::error('Could not get an CSRF Token', $e->getResponse()->getErrors());

            $this->fail($e);

            return;
        }

        app('Log')::debug('Wiki Page Response:', $response->getBody());
    }
}
