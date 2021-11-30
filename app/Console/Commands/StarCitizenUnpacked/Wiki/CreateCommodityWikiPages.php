<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizenUnpacked\Wiki;

use App\Console\Commands\AbstractQueueCommand;
use App\Jobs\Wiki\ApproveRevisions;
use App\Models\StarCitizenUnpacked\Item;
use App\Models\StarCitizenUnpacked\Shop\Shop;
use App\Services\Mapper\SmwSubObjectMapper;
use App\Traits\GetWikiCsrfTokenTrait;
use ErrorException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use NumberFormatter;
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
        $data = Shop::query()->with(['items'])
            ->get()
            ->filter(function (Shop $shop) {
                return strpos($shop->name_raw, 'IAE Expo') === false;
            })
            ->filter(function (Shop $shop) {
                return strpos($shop->name_raw, 'removed') === false;
            })
            ->filter(function (Shop $shop) {
                return strpos($shop->name_raw, 'Teach\'s') === false;
            })
            ->filter(function (Shop $shop) {
                return strpos($shop->name_raw, 'Levski') === false;
            })
            ->filter(function (Shop $shop) {
                return strpos($shop->name_raw, 'Rentals') === false;
            })
            ->filter(function (Shop $shop) {
                return strpos($shop->name_raw, 'New Deal') === false;
            })
            ->filter(function (Shop $shop) {
                return strpos($shop->name_raw, 'Astro Armada') === false;
            });

        $this->createProgressBar($data->count());

        $data->each(function (Shop $shop) {
            $this->uploadWiki($shop);

            $this->advanceBar();
        });

        $this->approvePages($data);

        return 0;
    }

    public function uploadWiki(Shop $shop)
    {
        $items = $shop
            ->items
            ->filter(function (Item $item) {
                return strpos($item->name, '[PLACEHOLDER]') === false;
            })
            ->sortBy('name')
            ->map(function (Item $item) use ($shop) {
                return SmwSubObjectMapper::map(
                    $this->mapItem($item, $shop),
                    ' ',
                    [],
                    str_replace(['.', '[PH]'], '', $item->name)
                );
            })
            ->implode("\n");

        $title = sprintf('Spieldaten/Handelswaren/%s/%s', $shop->position, $shop->name);

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
    }

    private function approvePages(Collection $data): void
    {
        $this->info('Approving Pages');
        $this->createProgressBar($data->count());

        $data->map(function (Shop $shop) {
            return sprintf('Spieldaten/Handelswaren/%s/%s', $shop->position, $shop->name);
        })
            ->each(function ($page) {
                $this->loginWikiBotAccount('services.wiki_approve_revs');

                dispatch(new ApproveRevisions([$page], false));
                $this->advanceBar();
            });
    }

    private function mapItem(Item $item, Shop $shop): array
    {
        $formatter = new NumberFormatter(config('app.locale'), NumberFormatter::TYPE_DEFAULT);

        return [
            'UUID' => $item->uuid,
            'Name' => str_replace('[PH]', '', $item->name),
            'Basispreis' => $formatter->format($item->shop_data->base_price) . 'aUEC',
            'Preis' => $formatter->format($item->shop_data->offsetted_price) . 'aUEC',
            'Minimalpreis' => $formatter->format($item->shop_data->priceRange['min']) . 'aUEC',
            'Maximalpreis' => $formatter->format($item->shop_data->priceRange['max']) . 'aUEC',
            'Preisoffset' => $formatter->format($item->shop_data->base_price_offset),
            'Rabatt' => $formatter->format($item->shop_data->max_discount),
            'Premium' => $formatter->format($item->shop_data->max_premium),
            'Bestand' => $formatter->format($item->shop_data->inventory),
            'Maximalbestand' => $formatter->format($item->shop_data->max_inventory),
            'Wiederauffüllungsrate' => $formatter->format($item->shop_data->refresh_rate),
            'Typ' => $item->type,
            'Kaufbar' => $item->shop_data->buyable,
            'Verkaufbar' => $item->shop_data->sellable,
            'Mietbar' => $item->shop_data->rentable,
            'Händler' => $shop->name,
            'Ort' => $shop->position,
            'Spielversion' => config('api.sc_data_version'),
        ];
    }
}
