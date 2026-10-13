<?php

declare(strict_types=1);

use App\Models\Game\ItemData;
use App\Services\ItemVariantResolver;
use App\Services\SetTagTaxonomy;
use Illuminate\Support\Facades\Log;

/**
 * @param  array<string, mixed>  $attributes
 */
function itemData(array $attributes = []): ItemData
{
    return new ItemData(array_merge([
        'item_id' => 1,
        'game_version_id' => 1,
        'manufacturer_id' => 1,
        'name' => 'Test Item',
        'class_name' => 'test_item',
        'type' => 'Armor',
        'sub_type' => 'Helmet',
        'classification' => 'FPS.Armor.Heavy',
        'size' => 1,
        'grade' => 1,
        'class' => 'Civilian',
        'base_id' => null,
        'data' => [],
    ], $attributes));
}

/**
 * In-memory game_entity_tags parent map. 'tag-set' is the unique row named 'Set'.
 *
 * @return array<string, string|null>
 */
function setTagParentMap(): array
{
    return [
        // Armor taxonomy root: FPS below Armor, 'Set' below FPS
        'tag-armor' => null,
        'tag-fps' => 'tag-armor',
        'tag-set' => 'tag-fps',

        // Set subtree: product line tags under parents that live below 'Set'
        'tag-adp-mk4' => 'tag-clarke-defense',
        'tag-orc-mkx' => 'tag-clarke-defense',
        'tag-clarke-defense' => 'tag-set',
        'tag-venture' => 'tag-set',
        'tag-inquisitor' => 'tag-set',
        'tag-lynx' => 'tag-kastak-arms',
        'tag-kastak-arms' => 'tag-set',
        'tag-truedef' => 'tag-virgil',
        'tag-virgil' => 'tag-set',

        // Manufacturer subtree: never passes 'Set'
        'tag-rrs' => 'tag-manufacturer',
        'tag-manufacturer' => null,

        // Category subtree
        'tag-sneakers' => 'tag-feet',
        'tag-feet' => 'tag-type',
        'tag-type' => null,

        // Weapon attachment side chain: crosses FPS but never passes 'Set'
        'tag-supx' => 'tag-stabilizer',
        'tag-stabilizer' => 'tag-barrel',
        'tag-barrel' => 'tag-attachment',
        'tag-attachment' => 'tag-fps',
    ];
}

/**
 * @param  array<string, string|null>  $parentOf  uuid -> parent uuid, null at root
 * @param  array<int, string>  $setUuids  uuids of rows named 'Set'
 */
function resolverWithTaxonomy(?array $parentOf = null, array $setUuids = ['tag-set']): ItemVariantResolver
{
    $parentOf ??= setTagParentMap();

    return new ItemVariantResolver(1, new SetTagTaxonomy(
        fn (): array => $setUuids,
        fn (string $uuid): ?string => $parentOf[$uuid] ?? null,
    ));
}

describe('extractClassNamePrefix', function () {
    it('merges armor items across set numbers', function () {
        $class1 = 'cds_legacy_armor_medium_arms_01_01_01';
        $class2 = 'cds_legacy_armor_medium_arms_01_02_01';
        expect(new ItemVariantResolver(1)->extractClassNamePrefix($class1))->toBe(new ItemVariantResolver(1)->extractClassNamePrefix($class2))
            ->toBe('cds_legacy_armor_medium_arms_01');
    });

    it('stops at non-numeric qualifier after numeric anchor', function () {
        expect(new ItemVariantResolver(1)->extractClassNamePrefix('mym_shirt_01_01_01'))->toBe('mym_shirt_01')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('mym_shirt_01_lum02_02'))->toBe('mym_shirt_01');
    });

    it('groups weapon color-word variants by prefix', function () {
        expect(new ItemVariantResolver(1)->extractClassNamePrefix('behr_rifle_ballistic_01'))->toBe('behr_rifle_ballistic_01')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('behr_rifle_ballistic_01_black02'))->toBe('behr_rifle_ballistic_01');
    });

    it('groups weapon multi-color-word variants by prefix', function () {
        expect(new ItemVariantResolver(1)->extractClassNamePrefix('gmni_pistol_ballistic_01_blue_white01'))->toBe('gmni_pistol_ballistic_01');
    });

    it('isolates attachments by size', function () {
        expect(new ItemVariantResolver(1)->extractClassNamePrefix('arma_barrel_comp_s1'))->toBe('arma_barrel_comp_s1')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('arma_barrel_comp_s2'))->toBe('arma_barrel_comp_s2')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('arma_barrel_comp_s1'))->not->toBe(new ItemVariantResolver(1)->extractClassNamePrefix('arma_barrel_comp_s2'));
    });

    it('groups attachment event variants', function () {
        expect(new ItemVariantResolver(1)->extractClassNamePrefix('arma_barrel_comp_s1'))->toBe(new ItemVariantResolver(1)->extractClassNamePrefix('arma_barrel_comp_s1_contestedzonereward'))
            ->toBe('arma_barrel_comp_s1');
    });

    it('isolates optics by zoom level', function () {
        expect(new ItemVariantResolver(1)->extractClassNamePrefix('behr_optics_holo_x1_s1'))->toBe('behr_optics_holo_x1_s1')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('behr_optics_holo_x2_s1'))->toBe('behr_optics_holo_x2_s1')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('behr_optics_holo_x1_s1'))->not->toBe(new ItemVariantResolver(1)->extractClassNamePrefix('behr_optics_holo_x2_s1'));
    });

    it('groups armor event variants', function () {
        expect(new ItemVariantResolver(1)->extractClassNamePrefix('cds_armor_medium_arms_01_01_01'))->toBe(new ItemVariantResolver(1)->extractClassNamePrefix('cds_armor_medium_arms_01_9tails_01'))
            ->toBe('cds_armor_medium_arms_01');
    });

    it('handles leading numeric manufacturer code', function () {
        expect(new ItemVariantResolver(1)->extractClassNamePrefix('987_jacket_03_01_01'))->toBe('987_jacket_03');
    });

    it('extracts ship component Structure A prefix', function () {
        expect(new ItemVariantResolver(1)->extractClassNamePrefix('COOL_ACOM_S01_IcePlunge_SCItem'))->toBe('COOL_ACOM_S01');
    });

    it('groups ship weapons across sizes by manufacturer and type', function () {
        expect(new ItemVariantResolver(1)->extractClassNamePrefix('AMRS_LaserCannon_S1'))->toBe('AMRS_LaserCannon')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('AMRS_LaserCannon_S2'))->toBe('AMRS_LaserCannon')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('AMRS_LaserCannon_S6'))->toBe('AMRS_LaserCannon')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('AMRS_LaserCannon_S1'))->toBe(new ItemVariantResolver(1)->extractClassNamePrefix('AMRS_LaserCannon_S6'));
    });

    it('strips ship weapon suffixes', function () {
        expect(new ItemVariantResolver(1)->extractClassNamePrefix('BEHR_BallisticGatling_S4_Turret'))->toBe('BEHR_BallisticGatling')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('BEHR_BallisticGatling_S4'))->toBe('BEHR_BallisticGatling');
    });

    it('isolates ship components by size', function () {
        expect(new ItemVariantResolver(1)->extractClassNamePrefix('COOL_ACOM_S01_IcePlunge_SCItem'))->not->toBe(new ItemVariantResolver(1)->extractClassNamePrefix('COOL_ACOM_S02_IcePlunge_SCItem'));
    });

    it('strips ship weapon suffixes after size', function () {
        expect(new ItemVariantResolver(1)->extractClassNamePrefix('BEHR_LaserCannon_S2_CleanAir'))->toBe('BEHR_LaserCannon');
    });

    it('groups all sizes of same ship weapon type together', function () {
        expect(new ItemVariantResolver(1)->extractClassNamePrefix('ESPR_BallisticCannon_S1'))->toBe('ESPR_BallisticCannon')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('ESPR_BallisticCannon_S3'))->toBe('ESPR_BallisticCannon')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('ESPR_BallisticCannon_S6'))->toBe('ESPR_BallisticCannon')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('ESPR_BallisticCannon_S1'))->toBe(new ItemVariantResolver(1)->extractClassNamePrefix('ESPR_BallisticCannon_S6'));
    });

    it('strips S## from ship weapons even with intervening segments', function () {
        expect(new ItemVariantResolver(1)->extractClassNamePrefix('GATS_BallisticGatling_Mounted_S1'))->toBe('GATS_BallisticGatling')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('GATS_BallisticGatling_S2'))->toBe('GATS_BallisticGatling')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('GATS_BallisticGatling_Mounted_S1'))->toBe(new ItemVariantResolver(1)->extractClassNamePrefix('GATS_BallisticGatling_S2'));
    });

    it('separates different weapon types from same manufacturer', function () {
        expect(new ItemVariantResolver(1)->extractClassNamePrefix('BEHR_BallisticGatling_S4'))->not->toBe(new ItemVariantResolver(1)->extractClassNamePrefix('BEHR_LaserCannon_S1'))
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('BEHR_LaserCannon_S1'))->not->toBe(new ItemVariantResolver(1)->extractClassNamePrefix('BEHR_BallisticRepeater_S1'));
    });

    it('preserves S## in prefix for missiles where S## is at index 1', function () {
        expect(new ItemVariantResolver(1)->extractClassNamePrefix('MISL_S01_CS_FSKI_Spark'))->toBe('MISL_S01')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('GMISL_S02_CS_FSKI_Tempest'))->toBe('GMISL_S02')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('MISL_S01_CS_FSKI_Spark'))->not->toBe(new ItemVariantResolver(1)->extractClassNamePrefix('MISL_S02_CS_FSKI_Tempest'));
    });

    // Ship variant-part shapes carry no size segment: nothing keys them.
    it('yields no prefix for ship variant-part class names', function () {
        expect(new ItemVariantResolver(1)->extractClassNamePrefix('ARMR_AEGS_Avenger_Stalker'))->toBeNull()
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('HTNK_AEGS_Vanguard_Harbinger'))->toBeNull()
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('QTNK_AEGS_Vanguard_Sentinel'))->toBeNull();
    });

    it('yields no prefix for countermeasure class names', function () {
        expect(new ItemVariantResolver(1)->extractClassNamePrefix('AEGS_Avenger_CML_Chaff'))->toBeNull()
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('CRUS_Starlifter_CML_Noise_Talon'))->toBeNull()
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('AEGS_Avenger_Chaff'))->toBeNull();
    });

    it('separates medical items by version number', function () {
        expect(new ItemVariantResolver(1)->extractClassNamePrefix('crlf_consumable_adrenaline_01'))->toBe('crlf_consumable_adrenaline_01')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('crlf_consumable_adrenaline_02'))->toBe('crlf_consumable_adrenaline_02')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('crlf_consumable_adrenaline_01'))->not->toBe(new ItemVariantResolver(1)->extractClassNamePrefix('crlf_consumable_adrenaline_02'));
    });

    it('groups multi-tool functional variants by prefix', function () {
        expect(new ItemVariantResolver(1)->extractClassNamePrefix('grin_multitool_01_default_cutter'))->toBe('grin_multitool_01')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('grin_multitool_01_default_mining'))->toBe('grin_multitool_01');
    });

    it('groups food products by prefix', function () {
        expect(new ItemVariantResolver(1)->extractClassNamePrefix('food_bar_snaggle_01_pepper_a'))->toBe(new ItemVariantResolver(1)->extractClassNamePrefix('food_bar_snaggle_01_tikoro_a'))
            ->toBe('food_bar_snaggle_01');
    });

    it('keeps rocket pod sizes apart through the general size rule', function () {
        expect(new ItemVariantResolver(1)->extractClassNamePrefix('RPOD_S1_FSKI_3x_S3'))->toBe('RPOD_S1')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('RPOD_S2_FSKI_4x_S3'))->toBe('RPOD_S2')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('RPOD_S1_FSKI_3x_S3'))->not->toBe(new ItemVariantResolver(1)->extractClassNamePrefix('RPOD_S2_FSKI_4x_S3'));
    });

    it('groups mass drivers across sizes ignoring S##', function () {
        expect(new ItemVariantResolver(1)->extractClassNamePrefix('KLWE_MassDriver_S1'))->toBe('KLWE_MassDriver')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('KLWE_MassDriver_S10'))->toBe('KLWE_MassDriver')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('KLWE_MassDriver_S1'))->toBe(new ItemVariantResolver(1)->extractClassNamePrefix('KLWE_MassDriver_S10'));
    });

    it('extracts MRCK missile rack prefix including manufacturer and product', function () {
        expect(new ItemVariantResolver(1)->extractClassNamePrefix('MRCK_S02_BEHR_Single_S02'))->toBe('MRCK_S02_BEHR_Single')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('MRCK_S02_BEHR_Dual_S01'))->toBe('MRCK_S02_BEHR_Dual')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('MRCK_S01_Krig_quad'))->toBe('MRCK_S01_Krig_quad')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('MRCK_S02_MISC_Fury'))->toBe('MRCK_S02_MISC_Fury')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('MRCK_S04_AEGS_Redeemer'))->toBe('MRCK_S04_AEGS_Redeemer');
    });

    it('groups MRCK variants of the same product', function () {
        expect(new ItemVariantResolver(1)->extractClassNamePrefix('MRCK_S01_Krig_quad'))->toBe(new ItemVariantResolver(1)->extractClassNamePrefix('MRCK_S01_Krig_quad_right'))
            ->toBe('MRCK_S01_Krig_quad')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('MRCK_S02_MISC_Fury'))->toBe(new ItemVariantResolver(1)->extractClassNamePrefix('MRCK_S02_MISC_Fury_Dual'))
            ->toBe('MRCK_S02_MISC_Fury');
    });

    it('separates MRCK items from different manufacturers', function () {
        expect(new ItemVariantResolver(1)->extractClassNamePrefix('MRCK_S02_BEHR_Single_S02'))->not->toBe(new ItemVariantResolver(1)->extractClassNamePrefix('MRCK_S02_Krig_Triple'));
    });

    it('separates MRCK items from same manufacturer different product', function () {
        expect(new ItemVariantResolver(1)->extractClassNamePrefix('MRCK_S02_BEHR_Single_S02'))->not->toBe(new ItemVariantResolver(1)->extractClassNamePrefix('MRCK_S02_BEHR_Dual_S01'));
    });

    it('handles MRCK ship-specific naming without S## at index 1', function () {
        expect(new ItemVariantResolver(1)->extractClassNamePrefix('MRCK_ANVL_Ballista_Quad_S05'))->toBe('MRCK_ANVL_Ballista_Quad_S05');
    });

    it('does not apply MRCK rule to non-MRCK items with S## at index 1', function () {
        expect(new ItemVariantResolver(1)->extractClassNamePrefix('COOL_ACOM_S01_IcePlunge_SCItem'))->toBe('COOL_ACOM_S01');
    });
});

describe('extractPaintPrefix', function () {
    it('extracts Paint_ tag as prefix', function () {
        $item = itemData([
            'class_name' => 'Paint_Cutter_Gloss_White_Red',
            'data' => ['stdItem' => ['Tags' => ['Paint_Cutter', '@Paint_Cutter_Gloss_White_Red']]],
        ]);
        expect(new ItemVariantResolver(1)->extractPaintPrefix($item))->toBe('Paint_Cutter');
    });

    it('handles mixed-alphanumeric ship models', function () {
        $item = itemData([
            'class_name' => 'Paint_100i_Blue_Gold',
            'data' => ['stdItem' => ['Tags' => ['Paint_100i', '@Paint_100i_Blue_Gold']]],
        ]);
        expect(new ItemVariantResolver(1)->extractPaintPrefix($item))->toBe('Paint_100i');
    });

    it('falls back to class_name segment when no tag', function () {
        $item = itemData([
            'class_name' => 'Paint_Cutter_Template',
            'data' => [],
        ]);
        expect(new ItemVariantResolver(1)->extractPaintPrefix($item))->toBe('Paint_Cutter');
    });

    it('returns null for non-paint items', function () {
        $item = itemData([
            'class_name' => 'COOL_ACOM_S01_IcePlunge_SCItem',
            'data' => [],
        ]);
        expect(new ItemVariantResolver(1)->extractPaintPrefix($item))->toBeNull();
    });

    it('returns null for Skin_ items', function () {
        $item = itemData([
            'class_name' => 'Skin_Gold',
            'data' => [],
        ]);
        expect(new ItemVariantResolver(1)->extractPaintPrefix($item))->toBeNull();
    });
});

describe('classification routing', function () {
    it('routes every family classification to an existing handler method', function () {
        $routes = new ReflectionClassConstant(ItemVariantResolver::class, 'CLASSIFICATION_ROUTES')->getValue();

        expect($routes)->not->toBeEmpty();

        foreach ($routes as $classification => $handler) {
            expect($classification)->toBeString()
                ->and(method_exists(ItemVariantResolver::class, $handler))->toBeTrue();
        }
    });

    it('pins the pattern route kinds and terminator', function () {
        $routes = new ReflectionClassConstant(ItemVariantResolver::class, 'PATTERN_ROUTES')->getValue();
        $kinds = array_map(static fn (array $route): string => $route[0], $routes);

        foreach ($routes as [$kind, $pattern, $handler]) {
            expect($kind)->toBeIn(['classification-prefix', 'type-prefix', 'any'])
                ->and(method_exists(ItemVariantResolver::class, $handler))->toBeTrue();
        }

        expect($kinds)->not->toBeEmpty()
            ->and(end($kinds))->toBe('any');
    });

    it('keeps unrouted ship stock ungrouped', function () {
        $item = itemData([
            'classification' => 'Ship.FuelTank.Fuel',
            'type' => 'FuelTank',
            'class_name' => 'HTNK_AEGS_Vanguard_Harbinger',
        ]);

        expect((new ItemVariantResolver(1))->resolveGroup($item))->toBe([$item]);
    });
});

describe('shipLineKey', function () {
    it('merges one component line across sizes via name-corroborated markers', function () {
        expect(ItemVariantResolver::shipLineKey('COOL_JSPN_S00_CryoStarSL_SCItem', 'Cryo-Star SL'))
            ->toBe(['COOL|JSPN', 'cryostar'])
            ->and(ItemVariantResolver::shipLineKey('COOL_JSPN_S03_CryoStarXL_SCItem', 'Cryo-Star XL'))
            ->toBe(['COOL|JSPN', 'cryostar']);
    });

    it('keeps grade families and sibling lines apart', function () {
        expect(ItemVariantResolver::shipLineKey('COOL_JSPN_S00_FrostStarSL_SCItem', 'Frost-Star SL'))
            ->toBe(['COOL|JSPN', 'froststar'])
            ->and(ItemVariantResolver::shipLineKey('COOL_JSPN_S00_WinterStarSL_SCItem', 'Winter-Star SL'))
            ->toBe(['COOL|JSPN', 'winterstar']);
    });

    it('keys gun ladders by maker and type, loadout spellings on their own line', function () {
        expect(ItemVariantResolver::shipLineKey('BEHR_LaserCannon_S2', 'M4A Cannon'))
            ->toBe(['BEHR', 'lasercannon'])
            ->and(ItemVariantResolver::shipLineKey('BEHR_LaserCannon_S7', 'M9A Cannon'))
            ->toBe(['BEHR', 'lasercannon'])
            // The Vanguard loadout spelling carries its own identity token.
            ->and(ItemVariantResolver::shipLineKey('BEHR_LaserCannon_VNG_S2', 'CVSA Cannon'))
            ->toBe(['BEHR|VNG', 'lasercannon']);
    });

    it('drops size segments and pod counts, keeps mount positions in the key', function () {
        expect(ItemVariantResolver::shipLineKey('MRCK_S10_RSI_Polaris_Torpedo_lb', 'RSI Polaris Torpedo Rack'))
            ->toBe(['MRCK|RSI|LB', 'polaristorpedo'])
            // Per-mount factory spellings are their own lines, not mirrors.
            ->and(ItemVariantResolver::shipLineKey('MRCK_S10_RSI_Polaris_Torpedo_Left', 'ST-205 Missile Rack'))
            ->toBe(['MRCK|RSI', 'polaristorpedoleft'])
            ->and(ItemVariantResolver::shipLineKey('MRCK_S10_RSI_Polaris_Torpedo', 'ST-205 Missile Rack'))
            ->toBe(['MRCK|RSI', 'polaristorpedo'])
            ->and(ItemVariantResolver::shipLineKey('RPOD_S1_HRST_6x_S1', 'Jericho'))
            ->toBe(ItemVariantResolver::shipLineKey('RPOD_S3_HRST_18x_S1', 'Jericho XL'))
            ->toBe(['RPOD|HRST', '']);
    });

    it('matches maker codes case-insensitively', function () {
        // The _2 spelling is the name=class data bug row; its class shape
        // still keys the same line as the display-named twin.
        expect(ItemVariantResolver::shipLineKey('MISL_S09_CS_TALN_Argos_2', 'misl_s09_cs_taln_argos_2'))
            ->toBe(['MISL|TALN', 'argos']);
    });

    it('drops seeker codes and mark numbers as axes', function () {
        expect(ItemVariantResolver::shipLineKey('MISL_S12_CS_TALN_Calamity', 'Calamity XII-CS Torpedo'))
            ->toBe(ItemVariantResolver::shipLineKey('MISL_S12_EM_TALN_Calamity', 'Calamity XII-EM Torpedo'))
            ->toBe(ItemVariantResolver::shipLineKey('MISL_S12_IR_TALN_Calamity', 'Calamity XII-IR Torpedo'))
            ->toBe(['MISL|TALN', 'calamity'])
            ->and(ItemVariantResolver::shipLineKey('MISL_S10_EM_BEHR_Executor', 'EX-T10-EM "Executor" Torpedo'))
            ->toBe(['MISL|BEHR', 'executor'])
            // Ground spellings keep their own family token and stay apart.
            ->and(ItemVariantResolver::shipLineKey('GMISL_S05_EM_TALN_Reaper', 'Reaper V-G Missile'))
            ->toBe(['GMISL|TALN', 'reaper'])
            ->and(ItemVariantResolver::shipLineKey('Mining_Modules_Passive_FLTR_MK1', 'FLTR Module'))
            ->toBe(ItemVariantResolver::shipLineKey('Mining_Modules_Passive_FLTR_MK3', 'FLTR-XL Module'))
            ->toBe(['FLTR', 'miningmodulespassive'])
            // Active modules are distinct products, not one edition ladder.
            ->and(ItemVariantResolver::shipLineKey('Mining_Modules_Active_Surge', 'Surge Module'))
            ->not->toBe(ItemVariantResolver::shipLineKey('Mining_Modules_Active_Brandt', 'Brandt Module'));
    });

    it('folds blade grammar words and tuning editions into one line', function () {
        expect(ItemVariantResolver::shipLineKey('Controller_Flight_AEGS_Gladius_Blade_HND', 'Gladius PHB Flight Blade'))
            ->toBe(ItemVariantResolver::shipLineKey('Controller_Flight_AEGS_Gladius', 'Gladius Standard Flight Blade'))
            ->toBe(['AEGS', 'controllergladius']);
    });

    it('rejects tweak spellings and debug placeholders', function () {
        expect(ItemVariantResolver::shipLineKey('SHLD_GODI_S01_AllStop_SCItem_ResistGasclouds', 'AllStop'))->toBeNull()
            ->and(ItemVariantResolver::shipLineKey('SHLD_BANU_S01_Placeholder_SCItem', 'Suldrath'))->toBeNull();
    });

    it('never strips a marker without display-name corroboration', function () {
        // A one-word name cannot corroborate the SL marker.
        expect(ItemVariantResolver::shipLineKey('COOL_JSPN_S00_CryoStarSL_SCItem', 'HEX'))
            ->toBe(['COOL|JSPN', 'cryostarsl']);
    });
});

describe('isCrossLaneArmorSpelling', function () {
    it('flags the misclassified Justified spellings and nothing else', function () {
        expect(ItemVariantResolver::isCrossLaneArmorSpelling('FPS.Armor.Helmet', 'ADP-mk4 Core Justified'))->toBeTrue()
            ->and(ItemVariantResolver::isCrossLaneArmorSpelling('FPS.Armor.Legs', 'ADP-mk4 Core Justified'))->toBeTrue()
            ->and(ItemVariantResolver::isCrossLaneArmorSpelling('FPS.Armor.Torso', 'ADP-mk4 Core Justified'))->toBeFalse()
            // Stale DCP names and sibling models carry the lane slot.
            ->and(ItemVariantResolver::isCrossLaneArmorSpelling('FPS.Armor.Helmet', 'DCP Armor Helmet Hunter Camo'))->toBeFalse()
            ->and(ItemVariantResolver::isCrossLaneArmorSpelling('FPS.Armor.Arms', 'Ana Arms Endro'))->toBeFalse()
            // Slot words must match on word boundaries.
            ->and(ItemVariantResolver::isCrossLaneArmorSpelling('FPS.Armor.Arms', 'CoreLock Arms'))->toBeFalse()
            // Non-armor lanes and missing names stay untouched.
            ->and(ItemVariantResolver::isCrossLaneArmorSpelling('FPS.Weapon.Rifle', 'Core Arms'))->toBeFalse()
            ->and(ItemVariantResolver::isCrossLaneArmorSpelling('FPS.Armor.Helmet', null))->toBeFalse();
    });
});

describe('resolveSetNameFromEntityTags', function () {
    it('accepts a Set-subtree tag whose name appears in a member name', function () {
        $group = [
            itemData(['name' => 'ADP-mk4 Arms Grey', 'data' => ['entity_tag_map' => [
                ['tag' => 'tag-adp-mk4', 'name' => 'ADP-mk4'],
            ]]]),
            itemData(['name' => 'ADP-mk4 Legs Tan', 'data' => ['entity_tag_map' => [
                ['tag' => 'tag-adp-mk4', 'name' => 'ADP-mk4'],
            ]]]),
        ];

        expect(resolverWithTaxonomy()->resolveSetNameFromEntityTags($group))->toBe('ADP-mk4');
    });

    it('matches the tag name case-insensitively against member names', function () {
        $resolver = resolverWithTaxonomy();

        $tagUpperNameLower = [itemData(['name' => 'venture arms grey', 'data' => ['entity_tag_map' => [
            ['tag' => 'tag-venture', 'name' => 'Venture'],
        ]]])];
        expect($resolver->resolveSetNameFromEntityTags($tagUpperNameLower))->toBe('Venture');

        $tagLowerNameUpper = [itemData(['name' => 'Inquisitor Legs Black', 'data' => ['entity_tag_map' => [
            ['tag' => 'tag-inquisitor', 'name' => 'inquisitor'],
        ]]])];
        expect($resolver->resolveSetNameFromEntityTags($tagLowerNameUpper))->toBe('inquisitor');
    });

    it('rejects a manufacturer-subtree tag', function () {
        $group = [
            itemData(['name' => 'RRS Helmet', 'data' => ['entity_tag_map' => [
                ['tag' => 'tag-rrs', 'name' => 'RRS'],
            ]]]),
            itemData(['name' => 'RRS Arms', 'data' => ['entity_tag_map' => [
                ['tag' => 'tag-rrs', 'name' => 'RRS'],
            ]]]),
        ];

        expect(resolverWithTaxonomy()->resolveSetNameFromEntityTags($group))->toBeNull();
    });

    it('rejects a category-subtree tag', function () {
        $group = [
            itemData(['name' => 'Sneakers Blue', 'data' => ['entity_tag_map' => [
                ['tag' => 'tag-sneakers', 'name' => 'Sneakers'],
            ]]]),
            itemData(['name' => 'Sneakers Red', 'data' => ['entity_tag_map' => [
                ['tag' => 'tag-sneakers', 'name' => 'Sneakers'],
            ]]]),
        ];

        expect(resolverWithTaxonomy()->resolveSetNameFromEntityTags($group))->toBeNull();
    });

    it('rejects a weapon attachment chain tag', function () {
        $group = [
            itemData(['name' => 'SupX Suppressor', 'data' => ['entity_tag_map' => [
                ['tag' => 'tag-supx', 'name' => 'SupX'],
            ]]]),
            itemData(['name' => 'SupX Stabilizer', 'data' => ['entity_tag_map' => [
                ['tag' => 'tag-supx', 'name' => 'SupX'],
            ]]]),
        ];

        expect(resolverWithTaxonomy()->resolveSetNameFromEntityTags($group))->toBeNull();
    });

    it('rejects a Set-subtree tag whose name appears in no member name', function () {
        $group = [
            itemData(['name' => 'GCD-Army Core', 'data' => ['entity_tag_map' => [
                ['tag' => 'tag-orc-mkx', 'name' => 'ORC-mkX'],
            ]]]),
            itemData(['name' => 'GCD-Army Helmet', 'data' => ['entity_tag_map' => [
                ['tag' => 'tag-orc-mkx', 'name' => 'ORC-mkX'],
            ]]]),
        ];

        expect(resolverWithTaxonomy()->resolveSetNameFromEntityTags($group))->toBeNull();
    });

    it('returns null when no Set row exists', function () {
        Log::shouldReceive('warning')->once();

        $group = [
            itemData(['name' => 'ADP-mk4 Arms Grey', 'data' => ['entity_tag_map' => [
                ['tag' => 'tag-adp-mk4', 'name' => 'ADP-mk4'],
            ]]]),
            itemData(['name' => 'ADP-mk4 Legs Tan', 'data' => ['entity_tag_map' => [
                ['tag' => 'tag-adp-mk4', 'name' => 'ADP-mk4'],
            ]]]),
        ];

        expect(resolverWithTaxonomy(setUuids: [])->resolveSetNameFromEntityTags($group))->toBeNull();
    });

    it('returns null when multiple Set rows exist', function () {
        Log::shouldReceive('warning')->once();

        $group = [
            itemData(['name' => 'ADP-mk4 Arms Grey', 'data' => ['entity_tag_map' => [
                ['tag' => 'tag-adp-mk4', 'name' => 'ADP-mk4'],
            ]]]),
            itemData(['name' => 'ADP-mk4 Legs Tan', 'data' => ['entity_tag_map' => [
                ['tag' => 'tag-adp-mk4', 'name' => 'ADP-mk4'],
            ]]]),
        ];

        expect(resolverWithTaxonomy(setUuids: ['tag-set', 'tag-set-dupe'])->resolveSetNameFromEntityTags($group))->toBeNull();
    });

    it('resolves the set tag by majority when CIG leaves a member untagged', function () {
        // cds_armor_heavy_helmet_03_01_01 carries a bare Set row where its
        // 15 siblings carry the named ADP-mk4 tag; the group must not lose
        // its set name to one gap.
        $group = [
            itemData(['name' => 'ADP-mk4 Helmet Woodland', 'data' => ['entity_tag_map' => [
                ['tag' => 'tag-adp-mk4', 'name' => 'ADP-mk4'],
            ]]]),
            itemData(['name' => 'ADP-mk4 Helmet Red Alert', 'data' => ['entity_tag_map' => [
                ['tag' => 'tag-adp-mk4', 'name' => 'ADP-mk4'],
            ]]]),
            itemData(['name' => 'ADP-mk4 Helmet Woodland', 'data' => ['entity_tag_map' => [
                ['tag' => 'tag-set', 'name' => 'Set'],
            ]]]),
        ];

        expect(resolverWithTaxonomy()->resolveSetNameFromEntityTags($group))->toBe('ADP-mk4');
    });

    it('returns null when a member has an empty entity_tag_map', function () {
        $group = [
            itemData(['name' => 'ADP-mk4 Arms Grey', 'data' => ['entity_tag_map' => [
                ['tag' => 'tag-adp-mk4', 'name' => 'ADP-mk4'],
            ]]]),
            itemData(['name' => 'ADP-mk4 Legs Tan', 'data' => []]),
        ];

        expect(resolverWithTaxonomy()->resolveSetNameFromEntityTags($group))->toBeNull();
    });

    it('intersects on tag uuids rather than names', function () {
        // Same tag name under two uuids; only one chain passes the 'Set' row.
        // A name-keyed intersection would wrongly yield 'Venture'.
        $group = [
            itemData(['name' => 'Venture Arms Grey', 'data' => ['entity_tag_map' => [
                ['tag' => 'tag-venture', 'name' => 'Venture'],
            ]]]),
            itemData(['name' => 'Venture Legs Tan', 'data' => ['entity_tag_map' => [
                ['tag' => 'tag-venture-mfr', 'name' => 'Venture'],
            ]]]),
        ];

        $parentOf = ['tag-venture-mfr' => 'tag-manufacturer'] + setTagParentMap();

        expect(resolverWithTaxonomy($parentOf)->resolveSetNameFromEntityTags($group))->toBeNull();
    });

    it('terminates on cyclic parent chains', function () {
        // Left side wins on duplicate keys, so the overrides must come first
        $parentOf = [
            'tag-venture' => 'tag-loop-b',
            'tag-loop-a' => 'tag-venture',
            'tag-loop-b' => 'tag-loop-a',
        ] + setTagParentMap();

        $group = [
            itemData(['name' => 'Venture Arms Grey', 'data' => ['entity_tag_map' => [
                ['tag' => 'tag-venture', 'name' => 'Venture'],
            ]]]),
            itemData(['name' => 'Venture Legs Tan', 'data' => ['entity_tag_map' => [
                ['tag' => 'tag-venture', 'name' => 'Venture'],
            ]]]),
        ];

        expect(resolverWithTaxonomy($parentOf)->resolveSetNameFromEntityTags($group))->toBeNull();
    });

    it('locates the Set row once per resolver instance', function () {
        $lookups = 0;
        $resolver = new ItemVariantResolver(1, new SetTagTaxonomy(
            function () use (&$lookups): array {
                $lookups++;

                return ['tag-set'];
            },
            fn (string $uuid): ?string => setTagParentMap()[$uuid] ?? null,
        ));

        $group = [
            itemData(['name' => 'ADP-mk4 Arms Grey', 'data' => ['entity_tag_map' => [
                ['tag' => 'tag-adp-mk4', 'name' => 'ADP-mk4'],
            ]]]),
            itemData(['name' => 'ADP-mk4 Legs Tan', 'data' => ['entity_tag_map' => [
                ['tag' => 'tag-adp-mk4', 'name' => 'ADP-mk4'],
            ]]]),
        ];

        expect($resolver->resolveSetNameFromEntityTags($group))->toBe('ADP-mk4')
            ->and($resolver->resolveSetNameFromEntityTags($group))->toBe('ADP-mk4')
            ->and($lookups)->toBe(1);
    });

    it('returns null when no common tag exists', function () {
        $group = [
            itemData(['name' => 'ProductA Core', 'data' => ['entity_tag_map' => [
                ['tag' => 'tag-product-a', 'name' => 'ProductA'],
            ]]]),
            itemData(['name' => 'ProductB Helmet', 'data' => ['entity_tag_map' => [
                ['tag' => 'tag-product-b', 'name' => 'ProductB'],
            ]]]),
        ];

        $parentOf = [
            'tag-product-a' => 'tag-set',
            'tag-product-b' => 'tag-set',
        ] + setTagParentMap();

        expect(resolverWithTaxonomy($parentOf)->resolveSetNameFromEntityTags($group))->toBeNull();
    });

    it('resolves set name for armor set misclassified as manufacturer (Lynx)', function () {
        // Regression: "Lynx" sits under the Manufacturer subtree in the tag
        // data but is NOT a manufacturer - it's an armor set name. The actual
        // manufacturer is KastakArms. KastakArms also qualifies via the taxonomy
        // but its name never appears in a member name, so only "Lynx" survives.
        $group = [
            itemData(['name' => 'Lynx Legs', 'data' => ['entity_tag_map' => [
                ['tag' => 'tag-light', 'name' => 'Light'],
                ['tag' => 'tag-common', 'name' => 'Common'],
                ['tag' => 'tag-fps', 'name' => 'FPS'],
                ['tag' => 'tag-legs', 'name' => 'Legs'],
                ['tag' => 'tag-human', 'name' => 'Human'],
                ['tag' => 'tag-kastak-arms', 'name' => 'KastakArms'],
                ['tag' => 'tag-lynx', 'name' => 'Lynx'],
                ['tag' => 'tag-grey', 'name' => 'Grey'],
            ]]]),
            itemData(['name' => 'Lynx Legs Red', 'data' => ['entity_tag_map' => [
                ['tag' => 'tag-light', 'name' => 'Light'],
                ['tag' => 'tag-common', 'name' => 'Common'],
                ['tag' => 'tag-fps', 'name' => 'FPS'],
                ['tag' => 'tag-legs', 'name' => 'Legs'],
                ['tag' => 'tag-human', 'name' => 'Human'],
                ['tag' => 'tag-kastak-arms', 'name' => 'KastakArms'],
                ['tag' => 'tag-lynx', 'name' => 'Lynx'],
                ['tag' => 'tag-red', 'name' => 'Red'],
            ]]]),
        ];

        expect(resolverWithTaxonomy()->resolveSetNameFromEntityTags($group))->toBe('Lynx');
    });

    it('resolves set name for TrueDef-Pro armor (TrueDef not a manufacturer)', function () {
        // Regression: "TrueDef" sits under the Manufacturer subtree in the tag
        // data but is NOT a manufacturer - it's Virgil's armor product line.
        $group = [
            itemData(['name' => 'TrueDef Legs Grey', 'data' => ['entity_tag_map' => [
                ['tag' => 'tag-light', 'name' => 'Light'],
                ['tag' => 'tag-rare', 'name' => 'Rare'],
                ['tag' => 'tag-fps', 'name' => 'FPS'],
                ['tag' => 'tag-legs', 'name' => 'Legs'],
                ['tag' => 'tag-human', 'name' => 'Human'],
                ['tag' => 'tag-virgil', 'name' => 'Virgil'],
                ['tag' => 'tag-truedef', 'name' => 'TrueDef'],
                ['tag' => 'tag-grey', 'name' => 'Grey'],
            ]]]),
            itemData(['name' => 'TrueDef Legs Red Silver', 'data' => ['entity_tag_map' => [
                ['tag' => 'tag-light', 'name' => 'Light'],
                ['tag' => 'tag-rare', 'name' => 'Rare'],
                ['tag' => 'tag-fps', 'name' => 'FPS'],
                ['tag' => 'tag-legs', 'name' => 'Legs'],
                ['tag' => 'tag-human', 'name' => 'Human'],
                ['tag' => 'tag-virgil', 'name' => 'Virgil'],
                ['tag' => 'tag-truedef', 'name' => 'TrueDef'],
                ['tag' => 'tag-red-silver', 'name' => 'RedSilver'],
            ]]]),
        ];

        expect(resolverWithTaxonomy()->resolveSetNameFromEntityTags($group))->toBe('TrueDef');
    });
});

describe('naming helpers', function () {
    it('finds longest common prefix', function () {
        expect(ItemVariantResolver::longestCommonPrefix([
            'Gemini A03 Sniper Rifle',
            'Gemini A03 Sniper Rifle Eclipse',
            'Gemini A03 Sniper Rifle Pathfinder',
        ]))->toBe('Gemini A03 Sniper Rifle');
    });

    it('returns null when no common prefix exists', function () {
        expect(ItemVariantResolver::longestCommonPrefix(['Alpha', 'Beta']))->toBeNull();
    });

    it('extracts quoted variant names', function () {
        expect(ItemVariantResolver::normalizeVariantName('"Scorched"'))->toBe('Scorched');
    });

    it('strips prefix and trims', function () {
        expect(ItemVariantResolver::stripPrefix('Gemini A03 Sniper Rifle Eclipse', 'Gemini A03 Sniper Rifle'))->toBe('Eclipse');
    });

    it('rejects single-character set names from LCP', function () {
        expect(ItemVariantResolver::deriveSetNameFromNames(['Seat', 'Station']))->toBeNull();
    });

    it('rejects two-character set names from LCP unless exact match', function () {
        expect(ItemVariantResolver::deriveSetNameFromNames(['Au Example', 'Au Other']))->toBeNull();
    });

    it('accepts short set name when it exactly matches a name', function () {
        expect(ItemVariantResolver::deriveSetNameFromNames(['RSI', 'RSI Constellation']))->toBe('RSI');
    });

    it('labels mixed-model pools via the tag-anchored subfamily', function () {
        // Antium shape: sibling models share one set tag; only names
        // carrying the tag prefix shed it, and the slot word strips by
        // majority inside that subfamily only.
        [, $map] = ItemVariantResolver::computeSetNameAndVariantNames(
            ['Bokto Arms', 'Ana Arms Endro', 'Antium Arms Sand', 'Antium Arms Storm', 'Antium Arms'],
            ['uuid' => 'base', 'name' => 'Bokto Arms'],
            [
                ['uuid' => 'base', 'name' => 'Bokto Arms'],
                ['uuid' => 'ana', 'name' => 'Ana Arms Endro'],
                ['uuid' => 'sand', 'name' => 'Antium Arms Sand'],
                ['uuid' => 'storm', 'name' => 'Antium Arms Storm'],
                ['uuid' => 'bare', 'name' => 'Antium Arms'],
            ],
            'Antium'
        );
        expect($map['base'])->toBe('Base')
            ->and($map['ana'])->toBe('Ana Arms Endro')
            ->and($map['sand'])->toBe('Sand')
            ->and($map['storm'])->toBe('Storm')
            ->and($map['bare'])->toBe('"Antium Arms"');
    });

    it('labels members from their own line parse and quoted editions', function (): void {
    [, $map] = ItemVariantResolver::computeSetNameAndVariantNames(
        ['Zeus Exploration Suit Helmet', 'Zeus Exploration Helmet Starscape'],
        ['uuid' => 'suit', 'name' => 'Zeus Exploration Suit Helmet', 'classification' => 'FPS.Armor.Helmet'],
        [
            ['uuid' => 'suit', 'name' => 'Zeus Exploration Suit Helmet', 'classification' => 'FPS.Armor.Helmet'],
            ['uuid' => 'starscape', 'name' => 'Zeus Exploration Helmet Starscape', 'classification' => 'FPS.Armor.Helmet'],
        ],
        'Zeus Exploration Suit'
    );

    expect($map['suit'])->toBe('Base')
        ->and($map['starscape'])->toBe('Starscape');

    [, $map] = ItemVariantResolver::computeSetNameAndVariantNames(
        ['Novikov Backpack', 'Novikov "Expo" Backpack'],
        ['uuid' => 'base', 'name' => 'Novikov Backpack', 'classification' => 'FPS.Armor.Backpack'],
        [
            ['uuid' => 'base', 'name' => 'Novikov Backpack', 'classification' => 'FPS.Armor.Backpack'],
            ['uuid' => 'expo', 'name' => 'Novikov "Expo" Backpack', 'classification' => 'FPS.Armor.Backpack'],
        ],
        'Novikov'
    );

    expect($map['base'])->toBe('Base')
        ->and($map['expo'])->toBe('Expo');
});

it('strips only slot words from the majority pass', function () {
        // Venture Arms shape: after the anchor and LCP strips the labels are
        // single words except a paired modifier (Dark Red, Dark Green) --
        // "Dark" must not become a fake majority and nuke the clean labels.
        [, $map] = ItemVariantResolver::computeSetNameAndVariantNames(
            ['Venture Arms', 'Venture Arms Red', 'Venture Arms Tan', 'Venture Arms Dark Red', 'Venture Arms Dark Green'],
            ['uuid' => 'base', 'name' => 'Venture Arms'],
            [
                ['uuid' => 'base', 'name' => 'Venture Arms'],
                ['uuid' => 'red', 'name' => 'Venture Arms Red'],
                ['uuid' => 'tan', 'name' => 'Venture Arms Tan'],
                ['uuid' => 'darkred', 'name' => 'Venture Arms Dark Red'],
                ['uuid' => 'darkgreen', 'name' => 'Venture Arms Dark Green'],
            ],
            'Venture'
        );
        expect($map['base'])->toBe('Base')
            ->and($map['red'])->toBe('Red')
            ->and($map['tan'])->toBe('Tan')
            ->and($map['darkred'])->toBe('Dark Red')
            ->and($map['darkgreen'])->toBe('Dark Green');
    });

    it('lets one data-bug spelling keep its full label', function () {
        // ADP-mk4 helmet shape: a Core-named member and stale DCP names
        // ride along; the Helmet word still strips for the majority.
        [, $map] = ItemVariantResolver::computeSetNameAndVariantNames(
            ['ADP-mk4 Helmet Red Alert', 'ADP-mk4 Helmet Righteous', 'ADP-mk4 Helmet Woodland', 'ADP-mk4 Core Justified', 'DCP Armor Helmet Hunter Camo'],
            ['uuid' => 'base', 'name' => 'ADP-mk4 Helmet Red Alert'],
            [
                ['uuid' => 'base', 'name' => 'ADP-mk4 Helmet Red Alert'],
                ['uuid' => 'right', 'name' => 'ADP-mk4 Helmet Righteous'],
                ['uuid' => 'wood', 'name' => 'ADP-mk4 Helmet Woodland'],
                ['uuid' => 'core', 'name' => 'ADP-mk4 Core Justified'],
                ['uuid' => 'dcp', 'name' => 'DCP Armor Helmet Hunter Camo'],
            ],
            'ADP-mk4'
        );
        expect($map['base'])->toBe('Base')
            ->and($map['right'])->toBe('Righteous')
            ->and($map['core'])->toBe('"ADP-mk4 Core Justified"')
            ->and($map['dcp'])->toBe('DCP Armor Helmet Hunter Camo');
    });

    it('strips common suffix from variant names', function () {
        [$setName, $map] = ItemVariantResolver::computeSetNameAndVariantNames(
            ['Keldur Hat and Indigo Goggles', 'Keldur Hat and Walnut Goggles', 'Keldur Hat and Hickory Goggles'],
            ['uuid' => 'base', 'name' => 'Keldur Hat and Indigo Goggles'],
            [
                ['uuid' => 'base', 'name' => 'Keldur Hat and Indigo Goggles'],
                ['uuid' => 'v1', 'name' => 'Keldur Hat and Walnut Goggles'],
                ['uuid' => 'v2', 'name' => 'Keldur Hat and Hickory Goggles'],
            ]
        );
        expect($map['base'])->toBe('Indigo')
            ->and($map['v1'])->toBe('Walnut')
            ->and($map['v2'])->toBe('Hickory');
    });

    it('extracts variant names without common suffix', function () {
        [$setName, $map] = ItemVariantResolver::computeSetNameAndVariantNames(
            ['Gale Head Cover Maroon', 'Gale Head Cover Brown', 'Gale Head Cover Green'],
            ['uuid' => 'base', 'name' => 'Gale Head Cover Maroon'],
            [
                ['uuid' => 'base', 'name' => 'Gale Head Cover Maroon'],
                ['uuid' => 'v1', 'name' => 'Gale Head Cover Brown'],
                ['uuid' => 'v2', 'name' => 'Gale Head Cover Green'],
            ]
        );
        expect($map['base'])->toBe('Maroon')
            ->and($map['v1'])->toBe('Brown')
            ->and($map['v2'])->toBe('Green');
    });

    it('preserves variant names with no common suffix', function () {
        [$setName, $map] = ItemVariantResolver::computeSetNameAndVariantNames(
            ['Defiance Legs Tactical', 'Defiance Legs Sunchaser', 'Defiance Legs Hailstorm'],
            ['uuid' => 'base', 'name' => 'Defiance Legs Tactical'],
            [
                ['uuid' => 'base', 'name' => 'Defiance Legs Tactical'],
                ['uuid' => 'v1', 'name' => 'Defiance Legs Sunchaser'],
                ['uuid' => 'v2', 'name' => 'Defiance Legs Hailstorm'],
            ]
        );
        expect($map['base'])->toBe('Tactical')
            ->and($map['v1'])->toBe('Sunchaser')
            ->and($map['v2'])->toBe('Hailstorm');
    });

    it('resolves hyphenated product line names as set name (Attrition)', function () {
        // Regression: "Attrition-" was rejected because '-' wasn't treated as
        // a word boundary. The LCP is "Attrition-" which should yield "Attrition".
        expect(ItemVariantResolver::deriveSetNameFromNames([
            'Attrition-1 Repeater',
            'Attrition-2 Repeater',
            'Attrition-3 Repeater',
            'Attrition-4 Repeater',
            'Attrition-5 Repeater',
            'Attrition-6 Repeater',
        ]))->toBe('Attrition');
    });

    it('resolves other hyphenated set names', function () {
        expect(ItemVariantResolver::deriveSetNameFromNames([
            'Dominance-1 Scattergun',
            'Dominance-2 Scattergun',
            'Dominance-3 Scattergun',
        ]))->toBe('Dominance');

        expect(ItemVariantResolver::deriveSetNameFromNames([
            'Ardor-1 Salvaged Repeater',
            'Ardor-2 Salvaged Repeater',
            'Ardor-3 Salvaged Repeater',
        ]))->toBe('Ardor');

        expect(ItemVariantResolver::deriveSetNameFromNames([
            'NDB-26 Repeater',
            'NDB-28 Repeater',
            'NDB-30 Repeater',
        ]))->toBe('NDB');
    });

    it('rejects two-char hyphenated prefixes', function () {
        // CF- and FL- are too short for a meaningful set name
        expect(ItemVariantResolver::deriveSetNameFromNames([
            'CF-117 Bulldog Repeater',
            'CF-227 Badger Repeater',
            'CF-337 Panther Repeater',
        ]))->toBeNull();

        expect(ItemVariantResolver::deriveSetNameFromNames([
            'FL-11 Cannon',
            'FL-22 Cannon',
            'FL-33 Cannon',
        ]))->toBeNull();
    });

    it('extracts variant names from hyphenated series', function () {
        [$setName, $map] = ItemVariantResolver::computeSetNameAndVariantNames(
            ['Attrition-1 Repeater', 'Attrition-2 Repeater', 'Attrition-6 Repeater'],
            ['uuid' => 'base', 'name' => 'Attrition-1 Repeater'],
            [
                ['uuid' => 'base', 'name' => 'Attrition-1 Repeater'],
                ['uuid' => 'v2', 'name' => 'Attrition-2 Repeater'],
                ['uuid' => 'v6', 'name' => 'Attrition-6 Repeater'],
            ]
        );
        expect($setName)->toBe('Attrition')
            ->and($map['base'])->toBe('1')
            ->and($map['v2'])->toBe('2')
            ->and($map['v6'])->toBe('6');
    });
});

it('extracts the armor line from lane names', function (string $classification, string $name, ?string $expected) {
    expect(ItemVariantResolver::armorLine($classification, $name))->toBe($expected);
})->with([
    'slot second' => ['FPS.Armor.Arms', 'Venture Arms Tan', 'Venture'],
    'multi-word line' => ['FPS.Armor.Helmet', 'DCP Armor Helmet Hunter Camo', 'DCP Armor'],
    'bare member' => ['FPS.Armor.Arms', 'TrueDef-Pro Arms', 'TrueDef-Pro'],
    'torso core word' => ['FPS.Armor.Torso', 'Carnifex Armor Core Shutdown', 'Carnifex Armor'],
    'case-insensitive slot' => ['FPS.Armor.Torso', 'Carrion Core stalker', 'Carrion'],
    'nbsp separator' => ['FPS.Armor.Helmet', "Oracle\u{00A0}Helmet Red", 'Oracle'],
    'no lane slot word' => ['FPS.Armor.Helmet', 'RSI Ivory Edition', null],
    'other lane slot word' => ['FPS.Armor.Helmet', 'ADP-mk4 Core Justified', null],
    'slot word alone' => ['FPS.Armor.Helmet', 'Helmet', null],
    'non-armor lane' => ['Ship.Cooler', 'CryoStar Cooler', null],
]);
