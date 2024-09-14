<?php

declare(strict_types=1);

namespace App\Console\Commands\SC\Wiki;

use App\Console\Commands\AbstractQueueCommand;
use App\Models\SC\Char\Clothing\Armor;
use App\Models\SC\Char\Clothing\Clothes;
use App\Models\SC\Char\Clothing\Clothing;
use App\Models\SC\Char\PersonalWeapon\Attachment;
use App\Models\SC\Char\PersonalWeapon\PersonalWeapon;
use App\Models\SC\CommodityItem;
use App\Models\SC\Food\Food;
use App\Models\SC\Vehicle\VehicleItem;
use App\Services\UploadWikiImage;
use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class UploadItemImages extends AbstractQueueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sc:upload-images';

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
        'Char_Armor_Arms' => 'Armpanzerung',
        'Char_Armor_Torso' => 'Oberkörperpanzerung',
        'Char_Armor_Legs' => 'Beinpanzerung',
        'Char_Armor_Helmet' => 'Helm',
        'Char_Armor_Backpack' => 'Rucksack',
        'Char_Armor_Undersuit' => 'Unteranzug',
        'Char_Clothing_Torso_1' => 'Jacke',
        'Char_Clothing_Legs' => 'Hose',
        'Char_Clothing_Torso_0' => 'Shirt',
        'Char_Clothing_Feet' => 'Schuh',
        'Char_Clothing_Hat' => 'Hut',
        'Char_Clothing_Hands' => 'Handschuh',
        'Char_Clothing_Torso_2' => 'Gürtel',
        'Char_Clothing_Backpack' => 'Rucksack',

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
     */
    public function handle(): int
    {
        $this->http = Http::baseUrl(config('services.item_thumbnail_url'));
        $this->upload = new UploadWikiImage(true);

        $this->info('Uploading Char Armor Images...');
        $this->withProgressBar(Armor::all(), function (Armor $armor) {
            $this->work($armor, true);
        });

        $this->info('Uploading Clothing Images...');
        $this->withProgressBar(Clothes::all(), function (Clothes $armor) {
            $this->work($armor, true);
        });

        $this->info('Uploading Weapon Personal Images...');
        $this->withProgressBar(PersonalWeapon::all(), function (PersonalWeapon $armor) {
            $this->work($armor, true);
        });

        $this->info('Uploading Weapon Attachment Images...');
        $this->withProgressBar(Attachment::all(), function (Attachment $armor) {
            $this->work($armor, true);
        });

        $this->info('Uploading Food Images...');
        $this->withProgressBar(Food::all(), function (Food $armor) {
            $this->work($armor, true);
        });

        $this->info('Uploading Ship Item Images...');
        $this->withProgressBar(VehicleItem::all(), function (VehicleItem $armor) {
            $this->work($armor, true);
        });

        $this->info('Done');

        return 0;
    }

    private function work($item, bool $normalizeCategory = false): void
    {
        if ($item instanceof CommodityItem) {
            $item = $item->item;
        }

        $url = sprintf('%s.jpg', $item->uuid);

        $this->headResponse = $this->http->head($url);
        if (! $this->headResponse->successful()) {
            return;
        }

        $name = preg_replace('/[^\w-]/', ' ', $item->name);
        $name = trim(preg_replace('/\s+/', ' ', $name));

        if ($item instanceof Clothing) {
            $name = CreateCharArmorWikiPages::getNameForModel($item);
        }

        if (str_contains($name, '+')) {
            $name = str_replace('+', ' (Plus)', $name);
        }

        $source = sprintf('%s%s', config('services.item_thumbnail_url'), $url);

        $metadata = [
            'filesize' => $this->headResponse->header('Content-Length'),
            'date' => $this->headResponse->header('Last-Modified'),
            'sources' => $source,
        ];

        $categories = [
            sprintf('{{subst:MFURN|%s}}', $item->manufacturer->code),
        ];

        if ($normalizeCategory) {
            $this->normalizeCategory($item, $name, $metadata, $categories);
        } else {
            $categories[] = $name;
        }

        if (! isset($metadata['description'])) {
            $metadata['description'] = sprintf(
                '[[%s]] vom Hersteller [[{{subst:MFURN|%s}}]]',
                $name,
                $item->manufacturer->code,
            );
        }

        if (isset($this->typeTranslations[$item->type])) {
            $categories[] = $this->typeTranslations[$item->type];

            $type = $this->typeTranslations[$item->type];
            if ($item->type === 'WeaponGun') {
                $type = 'Fahrzeugwaffe';
            }

            $metadata['description'] = sprintf(
                '%s [[%s]] vom Hersteller [[{{subst:MFURN|%s}}]]',
                $type,
                $name,
                $item->manufacturer->code,
            );
        }

        $categories = collect($categories)->map(function ($category) {
            return sprintf('[[Kategorie:%s]]', $category);
        })->implode("\n");

        try {
            $this->upload->upload(sprintf('%s.jpg', $name), $source, $metadata, $categories);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * Removes the color from the items name
     * Adds categories and a description
     *
     * @param  CommodityItem  $item
     */
    private function normalizeCategory($item, string $name, array &$metadata, array &$categories): void
    {
        if (isset($this->typeTranslations[$item->type])) {
            $categories[] = $this->typeTranslations[$item->type];

            $metadata['description'] = sprintf(
                '%s [[%s]] vom Hersteller [[{{subst:MFURN|%s}}]]',
                $this->typeTranslations[$item->type],
                $name,
                $item->manufacturer->code,
            );
        }
    }
}
