<?php

declare(strict_types=1);

namespace App\Jobs\Wiki\CommLink;

use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Normalizer;
use RuntimeException;
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
     * @var CommLink
     */
    private CommLink $commLink;

    /**
     * @var string
     */
    private string $template;

    /**
     * @var string CSRF Token
     */
    private string $token;

    /**
     * Create a new job instance.
     *
     * @param CommLink $commLink
     * @param string   $token
     * @param string   $template The Template to include before every translation
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
    public function handle(): void
    {
        app('Log')::info("Creating Wiki Page 'Comm-Link:{$this->commLink->cig_id}'");

        try {
            if (config('services.wiki_translations.locale') === 'de_DE') {
                $text = optional($this->commLink->german())->translation;
            } else {
                $text = optional($this->commLink->english())->translation;
            }

            if ($text !== null && !Normalizer::isNormalized($text)) {
                $text = Normalizer::normalize($text);
            }

            $response = MediaWikiApi::edit("Comm-Link:{$this->commLink->cig_id}")->text(
                sprintf(
                    "%s\n%s",
                    $this->template,
                    $text ?? ''
                )
            )
                ->summary("Importing Comm-Link Translation {$this->commLink->cig_id}")
                ->csrfToken($this->token)
                ->markBotEdit()
                ->createOnly()
                ->request();
        } catch (RuntimeException $e) {
            app('Log')::error('Could not get an CSRF Token', $e->getResponse()->getErrors());

            $this->fail($e);

            return;
        }

        app('Log')::debug('Wiki Page Response:', $response->getBody());
    }
}
