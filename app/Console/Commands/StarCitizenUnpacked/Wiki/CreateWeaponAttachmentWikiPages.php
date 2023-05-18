<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizenUnpacked\Wiki;

use App\Console\Commands\AbstractQueueCommand;
use App\Jobs\Wiki\ApproveRevisions;
use App\Models\SC\Char\PersonalWeapon\Attachment;
use App\Traits\GetWikiCsrfTokenTrait;
use App\Traits\Jobs\CreateEnglishSubpageTrait;
use ErrorException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use StarCitizenWiki\MediaWikiApi\Facades\MediaWikiApi;

class CreateWeaponAttachmentWikiPages extends AbstractQueueCommand
{
    use GetWikiCsrfTokenTrait;
    use CreateEnglishSubpageTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unpacked:create-weapon-attachment-pages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create weapon attachments as wikipages';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $attachments = Attachment::all();

        $this->createProgressBar($attachments->count());

        $attachments->each(function (Attachment $attachment) {
            $this->uploadWiki($attachment);

            $this->advanceBar();
        });

        if (config('services.wiki_approve_revs.access_secret') !== null) {
            $this->approvePages($attachments->pluck('name'));
        }

        return 0;
    }

    public function uploadWiki(Attachment $attachment): void
    {
        // phpcs:disable
        $text = <<<FORMAT
{{Waffenbefestigung}}
{{LokalisierteBeschreibung}}

{{Handelswarentabelle
|Name={{#invoke:Localized|getMainTitle}}
|Kaufbar=1
|Spalten=Händler,Ort,Preis,Spielversion
|Limit=5
}}

{{Standardausrüstung|Kategorie=Waffe|Query=[[Hat Unterobjekt.Position::+]]|Text=Waffen}}

== Quellen ==
<references />
{{Galerie}}

{{HerstellerNavplate|{{#show:{{#invoke:Localized|getMainTitle}}|?Hersteller#-}}}}
FORMAT;
        // phpcs:enable

        try {
            $token = $this->getCsrfToken('services.wiki_translations');
            $response = MediaWikiApi::edit($attachment->name)
                ->withAuthentication()
                ->text($text)
                ->csrfToken($token)
                ->createOnly()
                ->summary('Creating Weapon Attachment Page')
                ->request();
        } catch (ErrorException | GuzzleException $e) {
            $this->error($e->getMessage());

            return;
        }

        $this->createEnglishSubpage($attachment->name, $token);

        if ($response->hasErrors() && $response->getErrors()['code'] !== 'articleexists') {
            $this->error(implode(', ', $response->getErrors()));
        }
    }

    private function approvePages(Collection $data): void
    {
        $this->info('Approving Pages');
        $this->createProgressBar($data->count());

        $data
            ->each(function ($page) {
                $this->loginWikiBotAccount('services.wiki_approve_revs');

                dispatch(new ApproveRevisions([$page], false));
                $this->advanceBar();
            });
    }
}
