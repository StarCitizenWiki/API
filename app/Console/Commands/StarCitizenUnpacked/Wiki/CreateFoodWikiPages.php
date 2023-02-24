<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizenUnpacked\Wiki;

use App\Console\Commands\AbstractQueueCommand;
use App\Jobs\Wiki\ApproveRevisions;
use App\Models\StarCitizenUnpacked\Food\Food;
use App\Models\StarCitizenUnpacked\WeaponPersonal\Attachment;
use App\Traits\GetWikiCsrfTokenTrait;
use App\Traits\Jobs\CreateEnglishSubpageTrait;
use ErrorException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use StarCitizenWiki\MediaWikiApi\Facades\MediaWikiApi;

class CreateFoodWikiPages extends AbstractQueueCommand
{
    use GetWikiCsrfTokenTrait;
    use CreateEnglishSubpageTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unpacked:create-food-pages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create food wikipages';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $foods = Food::all();

        $this->createProgressBar($foods->count());

        $foods->each(function (Food $food) {
            if (str_contains($food->item->name, 'PLACEHOLDER') || str_contains($food->item->name, '[PH]')) {
                return;
            }

            $this->uploadWiki($food);

            $this->advanceBar();
        });

        if (config('services.wiki_approve_revs.access_secret') !== null) {
            $this->approvePages($foods->pluck('item.name'));
        }

        return 0;
    }

    public function uploadWiki(Food $food)
    {
        // phpcs:disable
        $text = <<<FORMAT
{{Lebensmittel}}
{{LokalisierteBeschreibung}}

{{Handelswarentabelle
|Name={{#invoke:Localized|getMainTitle}}
|Kaufbar=1
|Spalten=Händler,Ort,Preis,Spielversion
|Limit=5
}}

== Quellen ==
<references />
{{Galerie}}

{{HerstellerNavplate|{{#show:{{#invoke:Localized|getMainTitle}}|?Hersteller#-}}}}
FORMAT;
        // phpcs:enable

        try {
            $token = $this->getCsrfToken('services.wiki_translations');
            $response = MediaWikiApi::edit($food->item->name)
                ->withAuthentication()
                ->text($text)
                ->csrfToken($token)
                ->createOnly()
                ->summary('Creating Food Page')
                ->request();
        } catch (ErrorException | GuzzleException $e) {
            $this->error($e->getMessage());

            return;
        }

        $this->createEnglishSubpage($food->item->name, $token);

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
