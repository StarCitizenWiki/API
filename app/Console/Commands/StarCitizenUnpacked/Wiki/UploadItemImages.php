<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizenUnpacked\Wiki;

use App\Console\Commands\AbstractQueueCommand;
use App\Models\StarCitizenUnpacked\CharArmor\CharArmor;
use App\Models\StarCitizenUnpacked\CommodityItem;
use App\Models\StarCitizenUnpacked\ShipItem\ShipItem;
use App\Models\StarCitizenUnpacked\WeaponPersonal\WeaponPersonal;
use App\Services\UploadWikiImage;
use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class UploadItemImages extends AbstractQueueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unpacked:upload-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uploads images of unpacked items';

    private Response $headResponse;
    private PendingRequest $http;
    private UploadWikiImage $upload;

    /**
     * Translates the type to category
     * TODO: Consolidate this somewhere central
     *
     * @var string[]
     */
    private $typeTranslations = [
        'Arms' => 'Armpanzerung',
        'Helmet' => 'Helm',
        'Legs' => 'Beinpanzerung',
        'Core' => 'Oberkörperpanzerung',
        'Undersuit' => 'Unteranzug',
        'Cooler' => 'Kühler',
        'Power Plant' => 'Generator',
        'Quantum Drive' => 'Quantenantrieb',
        'Shield Generator' => 'Schildgenerator',
    ];

    /**
     * Upload images for armor parts, personal weapons and ship items
     *
     * @return int
     */
    public function handle(): int
    {
        $this->http = Http::baseUrl(config('services.item_thumbnail_url'));
        $this->upload = new UploadWikiImage();

        $this->info('Uploading Char Armor Images...');
        CharArmor::chunk(100, function (Collection $items) {
            $this->work($items, true);
        });


        $this->info('Uploading Weapon Personal Images...');
        WeaponPersonal::chunk(100, function (Collection $items) {
            $this->work($items);
        });

        $this->info('Uploading Ship Item Images...');
        ShipItem::whereIn('type', array_keys($this->typeTranslations))->chunk(100, function (Collection $items) {
            $this->work($items);
        });

        $this->info('Done');

        return 0;
    }

    /**
     * @param Collection $entries
     * @param bool $normalizeCategory
     */
    private function work(Collection $entries, bool $normalizeCategory = false): void
    {
        $entries->each(function (CommodityItem $item) use ($normalizeCategory) {
            $url = sprintf('%s.jpg', $item->item->uuid);

            $this->headResponse = $this->http->head($url);
            if (!$this->headResponse->successful()) {
                return;
            }

            $source = sprintf('%s%s', config('services.item_thumbnail_url'), $url);

            $metadata = [
                'filesize' => $this->headResponse->header('Content-Length'),
                'date' => $this->headResponse->header('Last-Modified'),
                'sources' => $source,
            ];

            $categories = [
                $item->item->manufacturer,
            ];

            $name = preg_replace('/[^\w-]/', ' ', $item->item->name);

            if ($normalizeCategory) {
                $this->normalizeCategory($item, $name, $metadata, $categories);
            } else {
                $categories[] = $item->item->name;
            }

            if (!isset($metadata['description'])) {
                $metadata['description'] = sprintf(
                    '[[%s]] vom Hersteller [[%s]]',
                    $item->item->name,
                    $item->item->manufacturer,
                );
            }

            if (isset($this->typeTranslations[$item->type])) {
                $categories[] = $this->typeTranslations[$item->type];

                $metadata['description'] = sprintf(
                    '%s [[%s]] vom Hersteller [[%s]]',
                    $this->typeTranslations[$item->type],
                    $item->item->name,
                    $item->item->manufacturer,
                );
            }

            $name = trim(preg_replace('/\s+/', ' ', $name));

            $categories = collect($categories)->map(function ($category) {
                return sprintf('[[Kategorie:%s]]', $category);
            })->implode("\n");

            try {
                $this->upload->upload(sprintf('%s.jpg', $name), $source, $metadata, $categories);
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }
        });
    }

    /**
     * Removes the color from the items name
     * Adds categories and a description
     *
     * @param CommodityItem $item
     * @param string $name
     * @param array $metadata
     * @param array $categories
     */
    private function normalizeCategory(CommodityItem $item, string $name, array &$metadata, array &$categories): void
    {
        foreach (CharArmor::$splits as $split) {
            if (!Str::contains($name, $split)) {
                continue;
            }

            $splitted = array_filter(explode($split, $name));
            if (count($splitted) === 2) {
                $categories[] = $splitted[0];
            } else {
                $categories[] = $item->item->name;
            }

            if (isset($this->typeTranslations[$split])) {
                $categories[] = $this->typeTranslations[$split];

                $metadata['description'] = sprintf(
                    '%s [[%s]] vom Hersteller [[%s]]',
                    $this->typeTranslations[$split],
                    $item->item->name,
                    $item->item->manufacturer,
                );
            }
        }
    }
}
