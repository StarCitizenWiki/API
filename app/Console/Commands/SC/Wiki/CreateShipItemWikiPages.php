<?php

declare(strict_types=1);

namespace App\Console\Commands\SC\Wiki;

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
    protected $signature = 'sc:create-ship-item-pages';

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
            return !str_contains(strtolower($item->name), 'placeholder');
        });

        $this->createProgressBar($items->count());

        $items->each(function (VehicleItem $item) {
            $this->uploadWiki($item);

            $this->advanceBar();
        });

        if (config('services.wiki_approve_revs.access_secret') !== null) {
            $this->approvePages($items->pluck('name'));
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
            $response = MediaWikiApi::edit($item->name)
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

        $this->createEnglishSubpage($item->name, $token);

        if ($response->hasErrors() && $response->getErrors()['code'] !== 'articleexists') {
            $this->error(implode(', ', $response->getErrors()));
        }
    }

    private function getTemplateType(VehicleItem $item): ?string
    {
        if ($item->name !== '<= PLACEHOLDER =>') {
            switch ($item->type) {
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

        return match ($item->type) {
            'Cooler' => 'Kühler',
            'PowerPlant' => 'Generator',
            'ShieldGenerator' => 'Schildgenerator',
            'QuantumDrive' => 'Quantenantrieb',
            default => null,
        };
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
