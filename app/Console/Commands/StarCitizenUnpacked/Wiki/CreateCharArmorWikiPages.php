<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizenUnpacked\Wiki;

use App\Console\Commands\AbstractQueueCommand;
use App\Jobs\Wiki\ApproveRevisions;
use App\Models\SC\Char\Clothing\Armor;
use App\Traits\GetWikiCsrfTokenTrait;
use App\Traits\Jobs\CreateEnglishSubpageTrait;
use ErrorException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use StarCitizenWiki\MediaWikiApi\Facades\MediaWikiApi;

class CreateCharArmorWikiPages extends AbstractQueueCommand
{
    use GetWikiCsrfTokenTrait;
    use CreateEnglishSubpageTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unpacked:create-char-armor-pages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create char armor as wikipages';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $charArmor = Armor::all();

        $this->createProgressBar($charArmor->count());

        $charArmor->each(function (Armor $armor) {
            if (str_contains($armor->name, 'PLACEHOLDER') || str_contains($armor->name, '[PH]')) {
                return;
            }

            $this->uploadWiki($armor);

            $this->advanceBar();
        });

        if (config('services.wiki_approve_revs.access_secret') !== null) {
            $this->approvePages($charArmor->pluck('item.name'));
        }

        return 0;
    }

    public function uploadWiki(Armor $armor)
    {
        // phpcs:disable
        $text = <<<FORMAT
{{Rüstung}}
{{LokalisierteBeschreibung}}

{{Handelswarentabelle
|Name={{#invoke:Localized|getMainTitle}}
|Kaufbar=1
|Spalten=Händler,Ort,Preis,Spielversion
|Limit=5
}}
{{Rüstungskomponenten}}

== Quellen ==
<references />
{{Galerie}}

{{HerstellerNavplate|{{#show:{{#invoke:Localized|getMainTitle}}|?Hersteller#-}}}}
FORMAT;
        // phpcs:enable

        try {
            $token = $this->getCsrfToken('services.wiki_translations');
            $response = MediaWikiApi::edit($armor->name)
                ->withAuthentication()
                ->text($text)
                ->csrfToken($token)
                ->createOnly()
                ->summary('Creating Armor Page')
                ->request();
        } catch (ErrorException | GuzzleException $e) {
            $this->error($e->getMessage());

            return;
        }

        $this->createEnglishSubpage($armor->name, $token);

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
