<?php

declare(strict_types=1);

use App\Models\Game\ItemData;
use App\Services\ItemVariantResolver;

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

    it('extracts ship variant armor prefix', function () {
        expect(new ItemVariantResolver(1)->extractClassNamePrefix('ARMR_AEGS_Avenger_Stalker'))->toBe('ARMR_AEGS_Avenger')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('ARMR_AEGS_Avenger_Titan'))->toBe('ARMR_AEGS_Avenger');
    });

    it('extracts ship variant fuel tank prefix', function () {
        expect(new ItemVariantResolver(1)->extractClassNamePrefix('HTNK_AEGS_Vanguard_Harbinger'))->toBe('HTNK_AEGS_Vanguard')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('HTNK_AEGS_Vanguard_Sentinel'))->toBe('HTNK_AEGS_Vanguard');
    });

    it('extracts ship variant quantum tank prefix', function () {
        expect(new ItemVariantResolver(1)->extractClassNamePrefix('QTNK_AEGS_Vanguard_Harbinger'))->toBe('QTNK_AEGS_Vanguard')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('QTNK_AEGS_Vanguard_Sentinel'))->toBe('QTNK_AEGS_Vanguard');
    });

    it('extracts countermeasure prefix stripping suffixes', function () {
        expect(new ItemVariantResolver(1)->extractClassNamePrefix('AEGS_Avenger_CML_Chaff'))->toBe('AEGS_Avenger_CML')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('AEGS_Avenger_CML_Flare'))->toBe('AEGS_Avenger_CML')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('AEGS_Avenger_CML_Noise_Small'))->toBe('AEGS_Avenger_CML')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('AEGS_Avenger_CML_Decoy_Small_GS'))->toBe('AEGS_Avenger_CML')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('AEGS_Avenger_CML_Chaff_Rear_Right'))->toBe('AEGS_Avenger_CML')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('CRUS_Starlifter_CML_Noise_Talon'))->toBe('CRUS_Starlifter_CML');
    });

    it('does not apply countermeasure stripping to non-CML items', function () {
        expect(new ItemVariantResolver(1)->extractClassNamePrefix('AEGS_Avenger_Chaff'))->not->toBe('AEGS_Avenger');
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

    it('extracts rocket pod prefix', function () {
        expect(new ItemVariantResolver(1)->extractClassNamePrefix('RPOD_S1_FSKI_3x_S3'))->toBe('RPOD_S1_FSKI')
            ->and(new ItemVariantResolver(1)->extractClassNamePrefix('RPOD_S2_FSKI_4x_S3'))->toBe('RPOD_S2_FSKI');
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

    it('refines blocked prefix with alphanumeric set identifier', function () {
        $resolver = new ItemVariantResolver(1);
        $prefix = $resolver->extractClassNamePrefix('eld_shirt_04_crus07_01');
        expect($prefix)->toBe('eld_shirt_04')
            ->and($resolver->refineClassNamePrefix('eld_shirt_04_crus07_01', $prefix))->toBe('eld_shirt_04_crus07')
            ->and($resolver->refineClassNamePrefix('eld_shirt_04_crus07_12', $prefix))->toBe('eld_shirt_04_crus07')
            ->and($resolver->refineClassNamePrefix('eld_shirt_04_iae2021_01', $prefix))->toBe('eld_shirt_04_iae2021')
            ->and($resolver->refineClassNamePrefix('cbd_hat_03_iae2021_01', 'cbd_hat_03'))->toBe('cbd_hat_03_iae2021')
            ->and($resolver->refineClassNamePrefix('eld_shirt_04_drake_03', 'eld_shirt_04'))->toBe('eld_shirt_04_drake');
    });

    it('refines blocked prefix with pure-alpha set identifier', function () {
        expect(new ItemVariantResolver(1)->refineClassNamePrefix('eld_shirt_04_fleetweek_01_dec', 'eld_shirt_04'))->toBe('eld_shirt_04_fleetweek');
    });

    it('refines blocked prefix with numeric sub-design', function () {
        $resolver = new ItemVariantResolver(1);
        expect($resolver->refineClassNamePrefix('fio_jacket_01_01_01', 'fio_jacket_01'))->toBe('fio_jacket_01_01')
            ->and($resolver->refineClassNamePrefix('fio_jacket_01_01_02', 'fio_jacket_01'))->toBe('fio_jacket_01_01')
            ->and($resolver->refineClassNamePrefix('fio_jacket_01_01_12', 'fio_jacket_01'))->toBe('fio_jacket_01_01');
    });

    it('refines blocked prefix for clothing color variants', function () {
        expect(new ItemVariantResolver(1)->refineClassNamePrefix('nrs_shoes_03_01_01', 'nrs_shoes_03'))->toBe('nrs_shoes_03_01')
            ->and(new ItemVariantResolver(1)->refineClassNamePrefix('nrs_shoes_03_01_02', 'nrs_shoes_03'))->toBe('nrs_shoes_03_01');
    });

    it('separates different clothing designs within same manufacturer', function () {
        expect(new ItemVariantResolver(1)->refineClassNamePrefix('alb_pants_01_01_01', 'alb_pants_01'))->toBe('alb_pants_01_01')
            ->and(new ItemVariantResolver(1)->refineClassNamePrefix('alb_pants_01_02_01', 'alb_pants_01'))->toBe('alb_pants_01_02');
    });

    it('returns null when no segment exists after prefix', function () {
        expect(new ItemVariantResolver(1)->refineClassNamePrefix('eld_shirt_04', 'eld_shirt_04'))->toBeNull()
            ->and(new ItemVariantResolver(1)->refineClassNamePrefix('eld_shirt_04_01', 'eld_shirt_04'))->toBeNull();
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

describe('isExcludedItem', function () {
    it('delegates to ItemRelevanceChecker for exclusion semantics', function () {
        $excluded = itemData([
            'class_name' => 'invisible_medium_arms',
            'name' => 'Invisible Medium Arms',
        ]);
        $included = itemData([
            'class_name' => 'cds_armor_medium_arms_01_01_01',
            'name' => 'ORC-mkX Arms',
        ]);

        expect(new ItemVariantResolver(1)->isExcludedItem($excluded))->toBeTrue()
            ->and(new ItemVariantResolver(1)->isExcludedItem($included))->toBeFalse();
    });

    it('excludes placeholder name items', function () {
        $item = itemData(['name' => '<= PLACEHOLDER =>']);
        expect(new ItemVariantResolver(1)->isExcludedItem($item))->toBeTrue();
    });
});

describe('isKnownFalseMergePrefix', function () {
    it('blocks exact armor variant prefixes that merge different products', function () {
        expect(new ItemVariantResolver(1)->isKnownFalseMergePrefix('qrt_combat_heavy_arms_02'))->toBeTrue()
            ->and(new ItemVariantResolver(1)->isKnownFalseMergePrefix('srvl_combat_heavy_core_03'))->toBeTrue()
            ->and(new ItemVariantResolver(1)->isKnownFalseMergePrefix('cds_combat_medium_arms_04'))->toBeTrue();
    });

    it('does not block more specific sub-prefixes', function () {
        expect(new ItemVariantResolver(1)->isKnownFalseMergePrefix('qrt_combat_heavy_arms_02_01'))->toBeFalse()
            ->and(new ItemVariantResolver(1)->isKnownFalseMergePrefix('cds_combat_medium_arms_04_01'))->toBeFalse();
    });

    it('does not block legitimate prefixes', function () {
        expect(new ItemVariantResolver(1)->isKnownFalseMergePrefix('cds_armor_medium_arms_01'))->toBeFalse()
            ->and(new ItemVariantResolver(1)->isKnownFalseMergePrefix('kap_light_helmet'))->toBeFalse()
            ->and(new ItemVariantResolver(1)->isKnownFalseMergePrefix('behr_rifle_ballistic_01'))->toBeFalse()
            ->and(new ItemVariantResolver(1)->isKnownFalseMergePrefix('eld_shirt_04_crus07'))->toBeFalse()
            ->and(new ItemVariantResolver(1)->isKnownFalseMergePrefix('fio_jacket_01_01'))->toBeFalse()
            ->and(new ItemVariantResolver(1)->isKnownFalseMergePrefix('nrs_shoes_03_01'))->toBeFalse();
    });

    it('blocks remaining clothing prefixes that still merge different products', function () {
        expect(new ItemVariantResolver(1)->isKnownFalseMergePrefix('cbd_shirt_01'))->toBeTrue()
            ->and(new ItemVariantResolver(1)->isKnownFalseMergePrefix('cbd_shirt_02'))->toBeTrue()
            ->and(new ItemVariantResolver(1)->isKnownFalseMergePrefix('dmc_jacket_04'))->toBeTrue();
    });
});

describe('extractEntityTagNames', function () {
    it('parses object format entity_tag_map', function () {
        $data = ['entity_tag_map' => [
            ['tag' => 'abc-123', 'name' => 'Scourge'],
            ['tag' => 'def-456', 'name' => 'ORC-mkV'],
            ['tag' => 'ghi-789', 'name' => 'PAB-1'],
        ]];
        $item = itemData(['data' => $data]);
        $names = new ItemVariantResolver(1)->extractEntityTagNames($item);
        expect($names)->toContain('Scourge', 'ORC-mkV', 'PAB-1');
    });

    it('handles null UUID entries gracefully', function () {
        $data = ['entity_tag_map' => [
            ['tag' => '00000000-0000-0000-0000-000000000000'],
            ['tag' => 'def-456', 'name' => 'ORC-mkV'],
        ]];
        $item = itemData(['data' => $data]);
        $names = new ItemVariantResolver(1)->extractEntityTagNames($item);
        expect($names)->toContain('ORC-mkV')->toHaveCount(1);
    });

    it('filters out meta slot weight rarity tags', function () {
        $data = ['entity_tag_map' => [
            ['tag' => 'a', 'name' => 'Medium'],
            ['tag' => 'b', 'name' => 'Common'],
            ['tag' => 'c', 'name' => 'FPS'],
            ['tag' => 'd', 'name' => 'Arms'],
            ['tag' => 'e', 'name' => 'Human'],
            ['tag' => 'f', 'name' => 'ClarkeDefense'],
            ['tag' => 'g', 'name' => 'ORC-mkV'],
        ]];
        $item = itemData(['data' => $data]);
        $names = new ItemVariantResolver(1)->extractEntityTagNames($item);
        expect($names)->toContain('ORC-mkV')
            ->and($names)->not->toContain('Medium', 'Common', 'FPS', 'Arms', 'Human', 'ClarkeDefense');
    });

    it('handles Format B literal tag names', function () {
        $data = ['entity_tag_map' => [
            ['tag' => 'a', 'name' => 'Manufacturer'],
            ['tag' => 'b', 'name' => 'Set'],
            ['tag' => 'c', 'name' => 'Color'],
            ['tag' => 'd', 'name' => 'Medium'],
        ]];
        $item = itemData(['data' => $data]);
        $names = new ItemVariantResolver(1)->extractEntityTagNames($item);
        expect($names)->toBeEmpty();
    });

    it('filters case-insensitively', function () {
        $data = ['entity_tag_map' => [
            ['tag' => 'a', 'name' => 'unlootable'],
            ['tag' => 'b', 'name' => 'fps'],
            ['tag' => 'c', 'name' => 'Scourge'],
        ]];
        $item = itemData(['data' => $data]);
        $names = new ItemVariantResolver(1)->extractEntityTagNames($item);
        expect($names)->toContain('Scourge')->toHaveCount(1);
    });

    it('handles null entity_tag_map', function () {
        $item = itemData(['data' => null]);
        expect(new ItemVariantResolver(1)->extractEntityTagNames($item))->toBeEmpty();
    });

    it('filters out lifestyle tags', function () {
        $data = ['entity_tag_map' => [
            ['tag' => 'a', 'name' => 'Casual'],
            ['tag' => 'b', 'name' => 'Outdoors'],
            ['tag' => 'c', 'name' => 'Work'],
            ['tag' => 'd', 'name' => 'Rugged'],
            ['tag' => 'e', 'name' => 'ORC-mkV'],
        ]];
        $item = itemData(['data' => $data]);
        $names = new ItemVariantResolver(1)->extractEntityTagNames($item);
        expect($names)->toContain('ORC-mkV')
            ->and($names)->not->toContain('Casual', 'Outdoors', 'Work', 'Rugged');
    });

    it('filters out garment descriptor tags', function () {
        $data = ['entity_tag_map' => [
            ['tag' => 'a', 'name' => 'Coat'],
            ['tag' => 'b', 'name' => 'Pants'],
            ['tag' => 'c', 'name' => 'Boots'],
            ['tag' => 'd', 'name' => 'JacketLong'],
            ['tag' => 'e', 'name' => 'ADP-mk4'],
        ]];
        $item = itemData(['data' => $data]);
        $names = new ItemVariantResolver(1)->extractEntityTagNames($item);
        expect($names)->toContain('ADP-mk4')
            ->and($names)->not->toContain('Coat', 'Pants', 'Boots', 'JacketLong');
    });

    it('filters out manufacturer names', function () {
        $data = ['entity_tag_map' => [
            ['tag' => 'a', 'name' => 'ClarkeDefense'],
            ['tag' => 'b', 'name' => 'KastakArms'],
            ['tag' => 'c', 'name' => 'Fiore'],
            ['tag' => 'd', 'name' => 'RSI'],
            ['tag' => 'e', 'name' => 'P4-AR'],
        ]];
        $item = itemData(['data' => $data]);
        $names = new ItemVariantResolver(1)->extractEntityTagNames($item);
        expect($names)->toContain('P4-AR')
            ->and($names)->not->toContain('ClarkeDefense', 'KastakArms', 'Fiore', 'RSI');
    });

    it('filters out ship component and cargo tags', function () {
        $data = ['entity_tag_map' => [
            ['tag' => 'a', 'name' => 'Seat'],
            ['tag' => 'b', 'name' => 'Turret'],
            ['tag' => 'c', 'name' => 'Cargo'],
            ['tag' => 'd', 'name' => '1SCU'],
            ['tag' => 'e', 'name' => 'Cooler'],
            ['tag' => 'f', 'name' => 'ORC-mkV'],
        ]];
        $item = itemData(['data' => $data]);
        $names = new ItemVariantResolver(1)->extractEntityTagNames($item);
        expect($names)->toContain('ORC-mkV')
            ->and($names)->not->toContain('Seat', 'Turret', 'Cargo', '1SCU', 'Cooler');
    });

    it('filters out color name tags', function () {
        $data = ['entity_tag_map' => [
            ['tag' => 'a', 'name' => 'Blue'],
            ['tag' => 'b', 'name' => 'Grey'],
            ['tag' => 'c', 'name' => 'DarkRed'],
            ['tag' => 'd', 'name' => 'Seagreen'],
            ['tag' => 'e', 'name' => 'PAB-1'],
        ]];
        $item = itemData(['data' => $data]);
        $names = new ItemVariantResolver(1)->extractEntityTagNames($item);
        expect($names)->toContain('PAB-1')
            ->and($names)->not->toContain('Blue', 'Grey', 'DarkRed', 'Seagreen');
    });

    it('filters out new weapon type tags', function () {
        $data = ['entity_tag_map' => [
            ['tag' => 'a', 'name' => 'LMG'],
            ['tag' => 'b', 'name' => 'SniperRifle'],
            ['tag' => 'c', 'name' => 'Laser'],
            ['tag' => 'd', 'name' => 'Mining'],
            ['tag' => 'e', 'name' => 'ADP'],
        ]];
        $item = itemData(['data' => $data]);
        $names = new ItemVariantResolver(1)->extractEntityTagNames($item);
        expect($names)->toContain('ADP')
            ->and($names)->not->toContain('LMG', 'SniperRifle', 'Laser', 'Mining');
    });

    it('filters out additional meta and gameplay tags', function () {
        $data = ['entity_tag_map' => [
            ['tag' => 'a', 'name' => 'ReceiveParentActorInteractions'],
            ['tag' => 'b', 'name' => 'CanGenerateAsLoot'],
            ['tag' => 'c', 'name' => 'Human'],
            ['tag' => 'd', 'name' => 'PU'],
            ['tag' => 'e', 'name' => 'Epic'],
            ['tag' => 'f', 'name' => 'InGameReward'],
            ['tag' => 'g', 'name' => 'SubscriberFlair'],
            ['tag' => 'h', 'name' => 'RRS'],
        ]];
        $item = itemData(['data' => $data]);
        $names = new ItemVariantResolver(1)->extractEntityTagNames($item);
        expect($names)->toContain('RRS')
            ->and($names)->not->toContain('ReceiveParentActorInteractions', 'CanGenerateAsLoot', 'Human', 'PU', 'Epic', 'InGameReward', 'SubscriberFlair');
    });

    it('filters out newly added manufacturer tags', function () {
        $data = ['entity_tag_map' => [
            ['tag' => 'a', 'name' => '987'],
            ['tag' => 'b', 'name' => 'KilgoreAndPoole'],
            ['tag' => 'c', 'name' => 'Gemini'],
            ['tag' => 'd', 'name' => 'Octagon'],
            ['tag' => 'e', 'name' => 'Ninetails'],
            ['tag' => 'f', 'name' => 'XenoThreat'],
            ['tag' => 'g', 'name' => 'Overlord'],
            ['tag' => 'h', 'name' => 'P4-AR'],
        ]];
        $item = itemData(['data' => $data]);
        $names = new ItemVariantResolver(1)->extractEntityTagNames($item);
        expect($names)->toContain('P4-AR')
            ->and($names)->not->toContain('987', 'KilgoreAndPoole', 'Gemini', 'Octagon', 'Ninetails', 'XenoThreat', 'Overlord');
    });
});

describe('resolveSetNameFromEntityTags', function () {
    it('finds common tag as set name', function () {
        $group = [
            itemData(['data' => ['entity_tag_map' => [
                ['tag' => 'a', 'name' => 'ORC-mkV'],
                ['tag' => 'b', 'name' => 'Grey'],
            ]]]),
            itemData(['data' => ['entity_tag_map' => [
                ['tag' => 'a', 'name' => 'ORC-mkV'],
                ['tag' => 'c', 'name' => 'DarkGreen'],
            ]]]),
            itemData(['data' => ['entity_tag_map' => [
                ['tag' => 'a', 'name' => 'ORC-mkV'],
                ['tag' => 'd', 'name' => 'Blue'],
            ]]]),
        ];
        expect(new ItemVariantResolver(1)->resolveSetNameFromEntityTags($group))->toBe('ORC-mkV');
    });

    it('returns null when no common tag exists', function () {
        $group = [
            itemData(['data' => ['entity_tag_map' => [
                ['tag' => 'a', 'name' => 'ProductA'],
            ]]]),
            itemData(['data' => ['entity_tag_map' => [
                ['tag' => 'b', 'name' => 'ProductB'],
            ]]]),
        ];
        expect(new ItemVariantResolver(1)->resolveSetNameFromEntityTags($group))->toBeNull();
    });

    it('resolves set name for armor set misclassified as manufacturer (Lynx)', function () {
        // Regression: "Lynx" was in ENTITY_TAG_MANUFACTURER_NAMES but is NOT a
        // manufacturer - it's an armor set name. The actual manufacturer is
        // KastakArms. Entity tags: Light, Common, FPS, Legs, Human, KastakArms,
        // Lynx, Grey - only "Lynx" survives (KastakArms is a real manufacturer).
        $group = [
            itemData(['data' => ['entity_tag_map' => [
                ['tag' => 'f1c9b063', 'name' => 'Light'],
                ['tag' => '59ca5e36', 'name' => 'Common'],
                ['tag' => 'ba75cc73', 'name' => 'FPS'],
                ['tag' => '4cd7531a', 'name' => 'Legs'],
                ['tag' => 'ba81fd42', 'name' => 'Human'],
                ['tag' => 'b9e5b0da', 'name' => 'KastakArms'],
                ['tag' => '9c28b7ae', 'name' => 'Lynx'],
                ['tag' => 'dd7bfcdd', 'name' => 'Grey'],
            ]]]),
            itemData(['data' => ['entity_tag_map' => [
                ['tag' => 'f1c9b063', 'name' => 'Light'],
                ['tag' => '59ca5e36', 'name' => 'Common'],
                ['tag' => 'ba75cc73', 'name' => 'FPS'],
                ['tag' => '4cd7531a', 'name' => 'Legs'],
                ['tag' => 'ba81fd42', 'name' => 'Human'],
                ['tag' => 'b9e5b0da', 'name' => 'KastakArms'],
                ['tag' => '9c28b7ae', 'name' => 'Lynx'],
                ['tag' => 'a1d4429d', 'name' => 'DarkGrey'],
            ]]]),
        ];
        expect(new ItemVariantResolver(1)->resolveSetNameFromEntityTags($group))->toBe('Lynx');
    });

    it('resolves set name for TrueDef-Pro armor (TrueDef not a manufacturer)', function () {
        // Regression: "TrueDef" was in ENTITY_TAG_MANUFACTURER_NAMES but is
        // NOT a manufacturer - it's Virgil's armor product line.
        $group = [
            itemData(['data' => ['entity_tag_map' => [
                ['tag' => 'f1c9b063', 'name' => 'Light'],
                ['tag' => 'dfbc6af5', 'name' => 'Rare'],
                ['tag' => 'ba75cc73', 'name' => 'FPS'],
                ['tag' => '4cd7531a', 'name' => 'Legs'],
                ['tag' => 'ba81fd42', 'name' => 'Human'],
                ['tag' => '51ce29f8', 'name' => 'Virgil'],
                ['tag' => '8a874213', 'name' => 'TrueDef'],
                ['tag' => 'dd7bfcdd', 'name' => 'Grey'],
            ]]]),
            itemData(['data' => ['entity_tag_map' => [
                ['tag' => 'f1c9b063', 'name' => 'Light'],
                ['tag' => 'dfbc6af5', 'name' => 'Rare'],
                ['tag' => 'ba75cc73', 'name' => 'FPS'],
                ['tag' => '4cd7531a', 'name' => 'Legs'],
                ['tag' => 'ba81fd42', 'name' => 'Human'],
                ['tag' => '51ce29f8', 'name' => 'Virgil'],
                ['tag' => '8a874213', 'name' => 'TrueDef'],
                ['tag' => '64f4e1f7', 'name' => 'RedSilver'],
            ]]]),
        ];
        expect(new ItemVariantResolver(1)->resolveSetNameFromEntityTags($group))->toBe('TrueDef');
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
