<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizenUnpacked\Wiki;

use App\Console\Commands\AbstractQueueCommand;
use App\Jobs\Wiki\ApproveRevisions;
use App\Models\StarCitizenUnpacked\WeaponPersonal\WeaponPersonal;
use App\Traits\GetWikiCsrfTokenTrait;
use ErrorException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use StarCitizenWiki\MediaWikiApi\Facades\MediaWikiApi;

class CreateWeaponPersonalWikiPages extends AbstractQueueCommand
{
    use GetWikiCsrfTokenTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unpacked:create-weapon-personal-pages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create personal weapon as wikipages';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $weaponPersonals = WeaponPersonal::all();

        $this->createProgressBar($weaponPersonals->count());

        $weaponPersonals->each(function (WeaponPersonal $weaponPersonal) {
            $this->uploadWiki($weaponPersonal);

            $this->advanceBar();
        });

        if (config('services.wiki_approve_revs.access_secret') !== null) {
            $this->approvePages($weaponPersonals->pluck('item.name'));
        }

        return 0;
    }

    public function uploadWiki(WeaponPersonal $armor)
    {
        // phpcs:disable
        $text = <<<FORMAT
{{Persönlichewaffe}}
{{LokalisierteBeschreibung}}

{{Handelswarentabelle
|Name={{#invoke:Localized|getMainTitle}}
|Kaufbar=1
|Spalten=Händler,Ort,Preis,Spielversion
|Limit=5
}}

{{Itemvarianten}}

{{Quellen}}

{{HerstellerNavplate|{{#show:{{#invoke:Localized|getMainTitle}}|?Hersteller#-}}}}
FORMAT;
        // phpcs:enable

        try {
            $token = $this->getCsrfToken('services.wiki_translations');
            $response = MediaWikiApi::edit($armor->item->name)
                ->withAuthentication()
                ->text($text)
                ->csrfToken($token)
                ->createOnly()
                ->summary('Creating Weapon Personal Page')
                ->request();
        } catch (ErrorException | GuzzleException $e) {
            $this->error($e->getMessage());

            return;
        }

        if ($response->hasErrors()) {
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
