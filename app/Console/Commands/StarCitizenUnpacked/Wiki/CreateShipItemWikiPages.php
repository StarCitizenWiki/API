<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizenUnpacked\Wiki;

use App\Console\Commands\AbstractQueueCommand;
use App\Jobs\Wiki\ApproveRevisions;
use App\Models\StarCitizenUnpacked\ShipItem\ShipItem;
use App\Traits\GetWikiCsrfTokenTrait;
use ErrorException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use StarCitizenWiki\MediaWikiApi\Facades\MediaWikiApi;

class CreateShipItemWikiPages extends AbstractQueueCommand
{
    use GetWikiCsrfTokenTrait;

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
    public function handle()
    {
        $items = ShipItem::all();

        $this->createProgressBar($items->count());

        $items->each(function (ShipItem $item) {
            $this->uploadWiki($item);

            $this->advanceBar();
        });

        $this->approvePages($items->pluck('item.name'));

        return 0;
    }

    public function uploadWiki(ShipItem $item)
    {
        $template = $this->getTemplateType($item);
        if ($template === null) {
            return;
        }

        // phpcs:disable
        $text = <<<FORMAT
{{$template}}
{{#show:{{SUBPAGENAME}}|?Beschreibung}}

{{Handelswarentabelle
|Name={{SUBPAGENAME}}
|Kaufbar=1
|Spalten=Händler,Ort,Preis,Spielversion
|Limit=5
}}

{{Quellen}}
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
        } catch (ErrorException | GuzzleException $e) {
            $this->error($e->getMessage());

            return;
        }

        if ($response->hasErrors()) {
            $this->error(implode(', ', $response->getErrors()));

            return;
        }
    }

    private function getTemplateType(ShipItem $item): ?string
    {
        switch ($item->type) {
            case 'Cooler':
                return 'Kühler';
            case 'Power Plant':
                return 'Generator';
            case 'Shield Generator':
                return 'Schild';
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
