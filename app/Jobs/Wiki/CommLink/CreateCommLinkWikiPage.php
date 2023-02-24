<?php

declare(strict_types=1);

namespace App\Jobs\Wiki\CommLink;

use App\Jobs\Wiki\ApproveRevisions;
use App\Models\Rsi\CommLink\CommLink;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
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
        $this->createCommLinkPage(config('services.wiki_translations.locale'), "Comm-Link:{$this->commLink->cig_id}");

        if (config('services.wiki_translations.create_english_subpage') === true) {
            $this->createCommLinkPage('en_EN', "Comm-Link:{$this->commLink->cig_id}/en");
        }
    }

    /**
     * Handle the actual creation
     *
     * @param string $language Text language
     * @param string $title MediaWiki Page Title
     * @return void
     */
    private function createCommLinkPage(string $language, string $title): void
    {
        app('Log')::info("Creating Wiki Page '{$title}'");

        try {
            if ($language === 'de_DE') {
                $text = optional($this->commLink->german())->translation;
            } else {
                $text = optional($this->commLink->english())->translation;
            }

            if ($text !== null && !Normalizer::isNormalized($text)) {
                $text = Normalizer::normalize($text);
            }

            if ($text !== null && config('language.translate_wrap_commlinks')) {
                $text = sprintf("<translate>\n%s\n</translate>", $text);
                $this->template = str_replace('{{Comm-Link}}', '<languages/>{{Comm-Link}}', $this->template);
            }

            $response = MediaWikiApi::edit($title)->text(
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
        } catch (ConnectException $e) {
            $this->release(60);

            return;
        } catch (GuzzleException | RuntimeException $e) {
            app('Log')::error('Could not get an CSRF Token', $e->getResponse()->getErrors());

            $this->fail($e);

            return;
        }

        if (config('services.wiki_approve_revs.access_secret', null) !== null) {
            dispatch(new ApproveRevisions(["Comm-Link:{$this->commLink->cig_id}"]));
        }

        app('Log')::debug('Wiki Page Response:', $response->getBody());
    }
}
