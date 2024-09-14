<?php

declare(strict_types=1);

namespace App\Console\Commands\SC\Wiki;

use App\Models\SC\Char\Clothing\Armor;
use App\Models\SC\Char\Clothing\Clothes;
use App\Traits\GetWikiCsrfTokenTrait;
use App\Traits\Jobs\CreateEnglishSubpageTrait;

class CreateCharArmorWikiPages extends AbstractCreateWikiPage
{
    use CreateEnglishSubpageTrait;
    use GetWikiCsrfTokenTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sc:create-char-armor-pages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create char armor as wikipages';

    protected string $template = <<<'TEMPLATE'
Das Item '''<ITEM NAME>''' ist ein <ARMOR CLASS> <ITEM TYPE> hergestellt von [[{{subst:MFURN|<MANUFACTURER CODE>}}]].<VARIANTINFO><ref name="<REFNAME>">{{Cite game|build=[[Star Citizen Alpha <REFVERSION>|Alpha <REFVERSION>]]|accessdate=<CURDATE>}}</ref>
== Beschreibung ==
{{Item description}}
== Itemports ==
{{Item ports}}
== Erwerb ==
{{Item availability}}
== Model ==
=== Varianten ===
{{Item variants}}
{{Quellen}}
{{Navplate manufacturers|<MANUFACTURER CODE>}}
TEMPLATE;

    protected array $typeMapping = [
        'Char_Armor_Arms' => 'Armpanzerung',
        'Char_Armor_Torso' => 'Oberkörperpanzerung',
        'Char_Armor_Legs' => 'Beinpanzerung',
        'Char_Armor_Helmet' => 'Helm',
        'Char_Armor_Backpack' => 'Rucksack',
        'Char_Armor_Undersuit' => 'Unteranzug',
        // Clothing
        'Char_Clothing_Torso_1' => 'Jacke',
        'Char_Clothing_Legs' => 'Hose',
        'Char_Clothing_Torso_0' => 'Shirt',
        'Char_Clothing_Feet' => 'Schuh',
        'Char_Clothing_Hat' => 'Hut',
        'Char_Clothing_Hands' => 'Handschuh',
        'Char_Clothing_Torso_2' => 'Gürtel',
        'Char_Clothing_Backpack' => 'Rucksack',
    ];

    protected array $subTypeMapping = [
        'Heavy' => 'schwere(r)',
        'Medium' => 'mittlere(r)',
        'Light' => 'leichte(r)',
        'Personal' => 'persönliche(r)',
        // Clothing
        'Female' => 'weibliche(r)',
        'Male' => 'männliche(r)',
        'Medical' => 'medizinische(r)',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->withProgressBar(
            Armor::all(),
            function (Armor $armor) {
                $this->uploadWiki($armor, 'Automatische Erstellung von Kleidungs- und Rüstungsseiten');
            }
        );

        return 0;
    }

    protected function prepareTemplate($model): string
    {
        $name = $this->getPageName($model);
        $type = $this->typeMapping[$model->type] ?? $model->type;

        $pageContent = $this->template;

        $pageContent = str_replace(
            '<ARMOR CLASS> ',
            $model->sub_type === 'UNDEFINED'
                ? ''
                : strtolower($this->subTypeMapping[$model->sub_type] ?? $model->sub_type).' ',
            $pageContent
        );

        $pageContent = str_replace(
            '<ITEM TYPE>',
            $type,
            $pageContent
        );

        if (self::getSuffix($model) !== null || (str_contains($model->class_name, '_01_15') && str_contains($name, 'Black/Silver'))) {
            $info = sprintf(" Dieses Item wird im Spiel als '''%s''' angezeigt.", $model->name);

            $pageContent = str_replace(
                '<VARIANTINFO>',
                $info,
                $pageContent
            );
        } else {
            $pageContent = str_replace('<VARIANTINFO>', '', $pageContent);
        }

        $this->fixText($type, $pageContent, ['panzerung']);

        if (str_contains($model->class_name, '_01_15') && str_contains($name, 'Black/Silver')) {
            $pageContent .= "\n[[Category:Gegenstand mit nicht eindeutigem Namen im Spiel]]";
        }

        return $pageContent;
    }

    protected static function getSuffix(Armor|Clothes $armor): ?string
    {
        $suffix = match (true) {
            str_contains($armor->class_name, '_hd_sec') => ' Hurston Security',
            str_contains($armor->class_name, '_irn') => ' Iron',
            str_contains($armor->class_name, '_gld') => ' Gold',
            str_contains($armor->class_name, '_microtech') => ' microTech',
            str_contains($armor->class_name, '_carrack') && ! str_contains($armor->name, 'Carrack') => ' Carrack Edition',
            str_contains($armor->class_name, '_9tails') => ' (Nine Tails)',
            str_contains($armor->class_name, '_xenothreat') => ' (Xenothreat)',
            default => null
        };

        if (! str_contains(strtolower($armor->name), strtolower(trim($suffix ?? '', ' ()')))) {
            return $suffix;
        }

        return null;
    }

    protected function getPageName($model): string
    {
        $suffix = self::getSuffix($model) ?? '';

        $name = $model->name.($suffix);
        if (str_contains($model->class_name, '_01_15') && str_contains($name, 'Black/Silver')) {
            $name = str_replace('Black', 'Tan', $name);
        }

        if ($model->name === 'Venture Helmet White' && $model->class_name === 'rsi_explorer_armor_light_helmet_01_01_10') {
            $name = str_replace('White', 'White/Red', $name);
        }

        $name = str_replace("$suffix$suffix", $suffix, $name);
        $name = preg_replace('/^\s*-\s*/', '', $name);

        return $name;
    }

    protected function getManufacturerCode($model): string
    {
        return $model->manufacturer->code;
    }

    public static function getNameForModel($model): string
    {
        return (new self)->getPageText($model);
    }
}
