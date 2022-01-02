<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizenUnpacked\Wiki;

use App\Console\Commands\AbstractQueueCommand;
use App\Jobs\Wiki\ApproveRevisions;
use App\Models\StarCitizenUnpacked\CharArmor\CharArmor;
use App\Traits\GetWikiCsrfTokenTrait;
use ErrorException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use StarCitizenWiki\MediaWikiApi\Facades\MediaWikiApi;

class CreateCharArmorWikiPages extends AbstractQueueCommand
{
    use GetWikiCsrfTokenTrait;

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
        $charArmor = CharArmor::all();

        $this->createProgressBar($charArmor->count());

        $charArmor->each(function (CharArmor $armor) {
            $this->uploadWiki($armor);

            $this->advanceBar();
        });

        $this->approvePages($charArmor->pluck('item.name'));

        return 0;
    }

    public function uploadWiki(CharArmor $armor)
    {
        // phpcs:disable
        $text = <<<FORMAT
{{Rüstung}}
{{#show:{{PAGENAME}}|?Beschreibung}}

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
            $response = MediaWikiApi::edit($armor->item->name)
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

        if ($response->hasErrors()) {
            $this->error(implode(', ', $response->getErrors()));

            return;
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
