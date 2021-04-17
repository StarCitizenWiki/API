<?php

namespace App\Console\Commands\StarCitizenUnpacked\Wiki;

use App\Console\Commands\AbstractQueueCommand;
use App\Services\Mapper\SmwSubObjectMapper;
use App\Services\Parser\StarCitizenUnpacked\Shops\Inventory;
use App\Services\Parser\StarCitizenUnpacked\Shops\Shops;
use App\Traits\GetWikiCsrfTokenTrait;
use ErrorException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use StarCitizenWiki\MediaWikiApi\Facades\MediaWikiApi;

class CreateCommodityWikiPages extends AbstractQueueCommand
{
    use GetWikiCsrfTokenTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unpacked:create-commodity-pages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create commodity subobjects as wikipages';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $shops = new Shops();
        $data = $shops->getData()
            ->filter(function ($shop, $name) {
                return strpos($name, 'IAE Expo') === false;
            })
            ->filter(function ($shop, $name) {
                return strpos($name, 'removed') === false;
            });

        $this->createProgressBar($data->count());

        $data->each(function (Collection $shop, $name) {
            $this->uploadWiki(
                $name,
                $shop->filter(function ($inventory) {
                    return strpos($inventory['Name'], '[PH]') === false;
                })
                    ->filter(function ($inventory) {
                        return strpos($inventory['Name'], '[PLACEHOLDER]') === false;
                    })
                    ->map(function ($inventory) {
                        return SmwSubObjectMapper::map($inventory);
                    })->implode("\n")
            );

            $this->advanceBar();
        });

        return 0;
    }

    public function uploadWiki(string $shop, string $items)
    {
        ['name' => $shop, 'position' => $position] = Inventory::parseShopName($shop);

        $title = sprintf('Spieldaten/Handelswaren/%s/%s', $position, $shop);

        // phpcs:disable
        $format = <<<FORMAT
<noinclude>
{{Alert|color=info|title=Information|content=Diese Seite enthält Daten über Kauf- und Mietpreise von Handelswaren in Star Citizen.<br>Diese Daten werden automatisch durch die Star Citizen Wiki API verwaltet.}}<!--
START: Semantic MediaWiki SubObjects -->
%s
<!--
END: Semantic MediaWiki SubObjects -->
[[Kategorie:Instandhaltung]]
[[Kategorie:Alpha %s]]
</noinclude>
FORMAT;
        // phpcs:enable

        try {
            $token = $this->getCsrfToken('services.wiki_translations');
            $response = MediaWikiApi::edit($title)
                ->withAuthentication()
                ->text(sprintf($format, $items, config('api.sc_data_version')))
                ->csrfToken($token)
                ->summary('Updating Commodity Prices')
                ->request();
        } catch (ErrorException | GuzzleException $e) {
            $this->error($e->getMessage());

            return;
        }

        if ($response->hasErrors()) {
            $this->error(implode(', ', $response->getErrors()));

            return;
        }

        #dispatch(new ApproveRevisions([$title], false));
    }
}
