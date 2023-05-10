<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizenUnpacked\Wiki;

use App\Console\Commands\AbstractQueueCommand;
use App\Models\SC\Char\Clothing\Armor;
use App\Models\SC\Char\Clothing\Clothes;
use App\Models\SC\Char\Clothing\Clothing;
use App\Models\SC\Char\PersonalWeapon\PersonalWeapon;
use App\Models\SC\CommodityItem;
use App\Models\SC\Food\Food;
use App\Models\SC\Vehicle\VehicleItem;
use App\Services\UploadWikiImage;
use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class  UploadItemImages extends AbstractQueueCommand
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
        'WeaponGun' => 'Fahrzeugwaffe',

        'Magazine' => 'Magazin',
        'Ballistic Compensator' => 'Ballistischer Kompensator',
        'Flash Hider' => 'Mündungsfeuerdämpfer',
        'Energy Stabilizer' => 'Energie-Stabilisator',
        'Suppressor' => 'Schalldämpfer',
        'Scope' => 'Zielfernrohr',
        'MedGel Refill' => 'MedGel-Nachfüllpackung',
        'Multi-Tool Attachment' => 'Multi-Tool-Aufsatz',
        'Battery' => 'Batterie',
        'Flashlight' => 'Taschenlampe',
        'Laser Pointer' => 'Laserpointer',

        'Light Backpack' => 'Leichter Rucksack',
        'Medium Backpack' => 'Mittlerer Rucksack',
        'Heavy Backpack' => 'Schwerer Rucksack',

        'Backpack' => 'Rucksack',
        'Bandana' => 'Bandana',
        'Beanie' => 'Mütze',
        'Boots' => 'Stiefel',
        'Gloves' => 'Handschuh',
        'Gown' => 'Kittel',
        'Hat' => 'Hut',
        'Head Cover' => 'Kopfbedeckung',
        'Jacket' => 'Jacke',
        'Pants' => 'Hose',
        'Shirt' => 'Hemd',
        'Shoes' => 'Schuh',
        'Slippers' => 'Hausschuhe',
        'Sweater' => 'Pullover',
        'T-Shirt' => 'T-Shirt',
        'Unknown Type' => 'Unbekannter Typ',

        'Food' => 'Lebensmittel',
        'Drink' => 'Getränk',
    ];

    /**
     * Upload images for armor parts, personal weapons and ship items
     *
     * @return int
     */
    public function handle(): int
    {
        $this->http = Http::baseUrl(config('services.item_thumbnail_url'));
        $this->upload = new UploadWikiImage(true);

        $this->info('Uploading Char Armor Images...');
        Armor::chunk(100, function (Collection $items) {
            $this->work($items, true);
        });

        $this->info('Uploading Clothing Images...');
        Clothes::chunk(100, function (Collection $items) {
            $this->work($items, true);
        });

        $this->info('Uploading Weapon Personal Images...');
        PersonalWeapon::chunk(100, function (Collection $items) {
            $this->work($items);
        });

//        $this->info('Uploading Weapon Attachment Images...');
//        Attachment::chunk(100, function (Collection $items) {
//            $this->work($items);
//        });

        $this->info('Uploading Food Images...');
        Food::chunk(100, function (Collection $items) {
            $this->work($items);
        });

        $this->info('Uploading Ship Item Images...');
        VehicleItem::query()->whereIn('type', array_keys($this->typeTranslations))
            ->orWhereRelation('item', 'type', 'WeaponGun')
            ->orWhereRelation('item', 'type', 'Missile')
            ->orWhereRelation('item', 'type', 'Torpedo')
            ->orWhereRelation('item', 'type', 'WeaponMining')
            ->orWhereRelation('item', 'type', 'MissileLauncher')
            ->chunk(100, function (Collection $items) {
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

            if ($item->item->manufacturer->name === '@LOC_PLACEHOLDER') {
                $item->Item->manufacturer->name = 'Unbekannter Hersteller';
            }

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
                str_replace('[PH] ', '', $item->item->manufacturer),
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
                    str_replace('[PH] ', '', $item->item->manufacturer),
                );
            }

            if (isset($this->typeTranslations[$item->type])) {
                $categories[] = $this->typeTranslations[$item->type];

                $type = $this->typeTranslations[$item->type];
                if ($item->item->type === 'WeaponGun') {
                    $type = 'Fahrzeugwaffe';
                }

                $metadata['description'] = sprintf(
                    '%s [[%s]] vom Hersteller [[%s]]',
                    $type,
                    $item->item->name,
                    str_replace('[PH] ', '', $item->item->manufacturer),
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
        foreach (Clothing::$splits as $split) {
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
