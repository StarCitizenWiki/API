<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizenUnpacked\Wiki;

use App\Console\Commands\AbstractQueueCommand;
use App\Jobs\Wiki\ApproveRevisions;
use App\Models\SC\Vehicle\VehicleItem;
use App\Traits\GetWikiCsrfTokenTrait;
use App\Traits\Jobs\CreateEnglishSubpageTrait;
use ErrorException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use StarCitizenWiki\MediaWikiApi\Facades\MediaWikiApi;

class CreateShipItemWikiPages extends AbstractQueueCommand
{
    use GetWikiCsrfTokenTrait;
    use CreateEnglishSubpageTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unpacked:create-ship-item-pages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create ship items as wikipages';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $items = VehicleItem::all();

        $items = $items->filter(function (VehicleItem $item) {
            return !str_contains(strtolower($item->item->name), 'placeholder');
        });

        $this->createProgressBar($items->count());

        $items->each(function (VehicleItem $item) {
            $this->uploadWiki($item);

            $this->advanceBar();
        });

        if (config('services.wiki_approve_revs.access_secret') !== null) {
            $this->approvePages($items->pluck('item.name'));
        }

        return 0;
    }

    public function uploadWiki(VehicleItem $item): void
    {
        $template = $this->getTemplateType($item);
        if ($template === null) {
            return;
        }

        // phpcs:disable
        $text = <<<FORMAT
{{{$template}}}
{{LokalisierteBeschreibung}}

{{Handelswarentabelle
|Name={{#invoke:Localized|getMainTitle}}
|Kaufbar=1
|Spalten=Händler,Ort,Preis,Spielversion
|Limit=5
}}

{{Standardausrüstung}}

== Quellen ==
<references />
{{Galerie}}

{{HerstellerNavplate|{{#show:{{#invoke:Localized|getMainTitle}}|?Hersteller#-}}}}
FORMAT;
        // phpcs:enable

        try {
            $token = $this->getCsrfToken('services.wiki_translations');
            $response = MediaWikiApi::edit($item->item->name)
                ->withAuthentication()
                ->text($text)
                ->csrfToken($token)
                ->createOnly()
                ->summary('Creating Ship Item page')
                ->request();
        } catch (ErrorException|GuzzleException $e) {
            $this->error($e->getMessage());

            return;
        }

        $this->createEnglishSubpage($item->item->name, $token);

        if ($response->hasErrors() && $response->getErrors()['code'] !== 'articleexists') {
            $this->error(implode(', ', $response->getErrors()));
        }
    }

    private function getTemplateType(VehicleItem $item): ?string
    {
        if ($item->item !== null && $item->item->name !== '<= PLACEHOLDER =>') {
            switch ($item->item->type) {
                case 'WeaponGun':
                    return 'Fahrzeugwaffe';
                case 'MissileLauncher':
                    return 'Raketenwerfer';
                case 'Missile':
                    return 'Rakete';
                case 'Turret':
                    return 'Waffenturm';
                case 'WeaponMining':
                    return 'Bergbaulaser';
            }
        }

        switch ($item->type) {
            case 'Cooler':
                return 'Kühler';
            case 'Power Plant':
                return 'Generator';
            case 'Shield Generator':
                return 'Schildgenerator';
            case 'Quantum Drive':
                return 'Quantenantrieb';
            default:
                return null;
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
