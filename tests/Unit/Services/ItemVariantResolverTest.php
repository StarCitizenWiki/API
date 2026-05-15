<?php

declare(strict_types=1);

use App\Models\Game\ItemData;
use App\Services\ItemVariantResolver;

function resolver(): ItemVariantResolver
{
    return new ItemVariantResolver(1);
}

describe('extractClassNamePrefix', function () {
    it('merges armor items across set numbers', function () {
        $class1 = 'cds_legacy_armor_medium_arms_01_01_01';
        $class2 = 'cds_legacy_armor_medium_arms_01_02_01';
        expect(resolver()->extractClassNamePrefix($class1))->toBe(resolver()->extractClassNamePrefix($class2))
            ->toBe('cds_legacy_armor_medium_arms_01');
    });

    it('stops at non-numeric qualifier after numeric anchor', function () {
        expect(resolver()->extractClassNamePrefix('mym_shirt_01_01_01'))->toBe('mym_shirt_01')
            ->and(resolver()->extractClassNamePrefix('mym_shirt_01_lum02_02'))->toBe('mym_shirt_01');
    });

    it('groups weapon color-word variants by prefix', function () {
        expect(resolver()->extractClassNamePrefix('behr_rifle_ballistic_01'))->toBe('behr_rifle_ballistic_01')
            ->and(resolver()->extractClassNamePrefix('behr_rifle_ballistic_01_black02'))->toBe('behr_rifle_ballistic_01');
    });

    it('groups weapon multi-color-word variants by prefix', function () {
        expect(resolver()->extractClassNamePrefix('gmni_pistol_ballistic_01_blue_white01'))->toBe('gmni_pistol_ballistic_01');
    });

    it('isolates attachments by size', function () {
        expect(resolver()->extractClassNamePrefix('arma_barrel_comp_s1'))->toBe('arma_barrel_comp_s1')
            ->and(resolver()->extractClassNamePrefix('arma_barrel_comp_s2'))->toBe('arma_barrel_comp_s2')
            ->and(resolver()->extractClassNamePrefix('arma_barrel_comp_s1'))->not->toBe(resolver()->extractClassNamePrefix('arma_barrel_comp_s2'));
    });

    it('groups attachment event variants', function () {
        expect(resolver()->extractClassNamePrefix('arma_barrel_comp_s1'))->toBe(resolver()->extractClassNamePrefix('arma_barrel_comp_s1_contestedzonereward'))
            ->toBe('arma_barrel_comp_s1');
    });

    it('isolates optics by zoom level', function () {
        expect(resolver()->extractClassNamePrefix('behr_optics_holo_x1_s1'))->toBe('behr_optics_holo_x1_s1')
            ->and(resolver()->extractClassNamePrefix('behr_optics_holo_x2_s1'))->toBe('behr_optics_holo_x2_s1')
            ->and(resolver()->extractClassNamePrefix('behr_optics_holo_x1_s1'))->not->toBe(resolver()->extractClassNamePrefix('behr_optics_holo_x2_s1'));
    });

    it('groups armor event variants', function () {
        expect(resolver()->extractClassNamePrefix('cds_armor_medium_arms_01_01_01'))->toBe(resolver()->extractClassNamePrefix('cds_armor_medium_arms_01_9tails_01'))
            ->toBe('cds_armor_medium_arms_01');
    });

    it('handles leading numeric manufacturer code', function () {
        expect(resolver()->extractClassNamePrefix('987_jacket_03_01_01'))->toBe('987_jacket_03');
    });

    it('extracts ship component Structure A prefix', function () {
        expect(resolver()->extractClassNamePrefix('COOL_ACOM_S01_IcePlunge_SCItem'))->toBe('COOL_ACOM_S01');
    });

    it('groups ship weapons across sizes by manufacturer and type', function () {
        expect(resolver()->extractClassNamePrefix('AMRS_LaserCannon_S1'))->toBe('AMRS_LaserCannon')
            ->and(resolver()->extractClassNamePrefix('AMRS_LaserCannon_S2'))->toBe('AMRS_LaserCannon')
            ->and(resolver()->extractClassNamePrefix('AMRS_LaserCannon_S6'))->toBe('AMRS_LaserCannon')
            ->and(resolver()->extractClassNamePrefix('AMRS_LaserCannon_S1'))->toBe(resolver()->extractClassNamePrefix('AMRS_LaserCannon_S6'));
    });

    it('strips ship weapon suffixes', function () {
        expect(resolver()->extractClassNamePrefix('BEHR_BallisticGatling_S4_Turret'))->toBe('BEHR_BallisticGatling')
            ->and(resolver()->extractClassNamePrefix('BEHR_BallisticGatling_S4'))->toBe('BEHR_BallisticGatling');
    });

    it('isolates ship components by size', function () {
        expect(resolver()->extractClassNamePrefix('COOL_ACOM_S01_IcePlunge_SCItem'))->not->toBe(resolver()->extractClassNamePrefix('COOL_ACOM_S02_IcePlunge_SCItem'));
    });

    it('strips ship weapon suffixes after size', function () {
        expect(resolver()->extractClassNamePrefix('BEHR_LaserCannon_S2_CleanAir'))->toBe('BEHR_LaserCannon');
    });

    it('groups all sizes of same ship weapon type together', function () {
        expect(resolver()->extractClassNamePrefix('ESPR_BallisticCannon_S1'))->toBe('ESPR_BallisticCannon')
            ->and(resolver()->extractClassNamePrefix('ESPR_BallisticCannon_S3'))->toBe('ESPR_BallisticCannon')
            ->and(resolver()->extractClassNamePrefix('ESPR_BallisticCannon_S6'))->toBe('ESPR_BallisticCannon')
            ->and(resolver()->extractClassNamePrefix('ESPR_BallisticCannon_S1'))->toBe(resolver()->extractClassNamePrefix('ESPR_BallisticCannon_S6'));
    });

    it('strips S## from ship weapons even with intervening segments', function () {
        expect(resolver()->extractClassNamePrefix('GATS_BallisticGatling_Mounted_S1'))->toBe('GATS_BallisticGatling')
            ->and(resolver()->extractClassNamePrefix('GATS_BallisticGatling_S2'))->toBe('GATS_BallisticGatling')
            ->and(resolver()->extractClassNamePrefix('GATS_BallisticGatling_Mounted_S1'))->toBe(resolver()->extractClassNamePrefix('GATS_BallisticGatling_S2'));
    });

    it('separates different weapon types from same manufacturer', function () {
        expect(resolver()->extractClassNamePrefix('BEHR_BallisticGatling_S4'))->not->toBe(resolver()->extractClassNamePrefix('BEHR_LaserCannon_S1'))
            ->and(resolver()->extractClassNamePrefix('BEHR_LaserCannon_S1'))->not->toBe(resolver()->extractClassNamePrefix('BEHR_BallisticRepeater_S1'));
    });

    it('preserves S## in prefix for missiles where S## is at index 1', function () {
        expect(resolver()->extractClassNamePrefix('MISL_S01_CS_FSKI_Spark'))->toBe('MISL_S01')
            ->and(resolver()->extractClassNamePrefix('GMISL_S02_CS_FSKI_Tempest'))->toBe('GMISL_S02')
            ->and(resolver()->extractClassNamePrefix('MISL_S01_CS_FSKI_Spark'))->not->toBe(resolver()->extractClassNamePrefix('MISL_S02_CS_FSKI_Tempest'));
    });

    it('extracts ship variant armor prefix', function () {
        expect(resolver()->extractClassNamePrefix('ARMR_AEGS_Avenger_Stalker'))->toBe('ARMR_AEGS_Avenger')
            ->and(resolver()->extractClassNamePrefix('ARMR_AEGS_Avenger_Titan'))->toBe('ARMR_AEGS_Avenger');
    });

    it('extracts ship variant fuel tank prefix', function () {
        expect(resolver()->extractClassNamePrefix('HTNK_AEGS_Vanguard_Harbinger'))->toBe('HTNK_AEGS_Vanguard')
            ->and(resolver()->extractClassNamePrefix('HTNK_AEGS_Vanguard_Sentinel'))->toBe('HTNK_AEGS_Vanguard');
    });

    it('extracts ship variant quantum tank prefix', function () {
        expect(resolver()->extractClassNamePrefix('QTNK_AEGS_Vanguard_Harbinger'))->toBe('QTNK_AEGS_Vanguard')
            ->and(resolver()->extractClassNamePrefix('QTNK_AEGS_Vanguard_Sentinel'))->toBe('QTNK_AEGS_Vanguard');
    });

    it('extracts countermeasure prefix stripping suffixes', function () {
        expect(resolver()->extractClassNamePrefix('AEGS_Avenger_CML_Chaff'))->toBe('AEGS_Avenger_CML')
            ->and(resolver()->extractClassNamePrefix('AEGS_Avenger_CML_Flare'))->toBe('AEGS_Avenger_CML')
            ->and(resolver()->extractClassNamePrefix('AEGS_Avenger_CML_Noise_Small'))->toBe('AEGS_Avenger_CML')
            ->and(resolver()->extractClassNamePrefix('AEGS_Avenger_CML_Decoy_Small_GS'))->toBe('AEGS_Avenger_CML')
            ->and(resolver()->extractClassNamePrefix('AEGS_Avenger_CML_Chaff_Rear_Right'))->toBe('AEGS_Avenger_CML')
            ->and(resolver()->extractClassNamePrefix('CRUS_Starlifter_CML_Noise_Talon'))->toBe('CRUS_Starlifter_CML');
    });

    it('does not apply countermeasure stripping to non-CML items', function () {
        expect(resolver()->extractClassNamePrefix('AEGS_Avenger_Chaff'))->not->toBe('AEGS_Avenger');
    });

    it('separates medical items by version number', function () {
        expect(resolver()->extractClassNamePrefix('crlf_consumable_adrenaline_01'))->toBe('crlf_consumable_adrenaline_01')
            ->and(resolver()->extractClassNamePrefix('crlf_consumable_adrenaline_02'))->toBe('crlf_consumable_adrenaline_02')
            ->and(resolver()->extractClassNamePrefix('crlf_consumable_adrenaline_01'))->not->toBe(resolver()->extractClassNamePrefix('crlf_consumable_adrenaline_02'));
    });

    it('groups multi-tool functional variants by prefix', function () {
        expect(resolver()->extractClassNamePrefix('grin_multitool_01_default_cutter'))->toBe('grin_multitool_01')
            ->and(resolver()->extractClassNamePrefix('grin_multitool_01_default_mining'))->toBe('grin_multitool_01');
    });

    it('groups food products by prefix', function () {
        expect(resolver()->extractClassNamePrefix('food_bar_snaggle_01_pepper_a'))->toBe(resolver()->extractClassNamePrefix('food_bar_snaggle_01_tikoro_a'))
            ->toBe('food_bar_snaggle_01');
    });

    it('extracts rocket pod prefix', function () {
        expect(resolver()->extractClassNamePrefix('RPOD_S1_FSKI_3x_S3'))->toBe('RPOD_S1_FSKI')
            ->and(resolver()->extractClassNamePrefix('RPOD_S2_FSKI_4x_S3'))->toBe('RPOD_S2_FSKI');
    });

    it('groups mass drivers across sizes ignoring S##', function () {
        expect(resolver()->extractClassNamePrefix('KLWE_MassDriver_S1'))->toBe('KLWE_MassDriver')
            ->and(resolver()->extractClassNamePrefix('KLWE_MassDriver_S10'))->toBe('KLWE_MassDriver')
            ->and(resolver()->extractClassNamePrefix('KLWE_MassDriver_S1'))->toBe(resolver()->extractClassNamePrefix('KLWE_MassDriver_S10'));
    });

    it('extracts MRCK missile rack prefix including manufacturer and product', function () {
        expect(resolver()->extractClassNamePrefix('MRCK_S02_BEHR_Single_S02'))->toBe('MRCK_S02_BEHR_Single')
            ->and(resolver()->extractClassNamePrefix('MRCK_S02_BEHR_Dual_S01'))->toBe('MRCK_S02_BEHR_Dual')
            ->and(resolver()->extractClassNamePrefix('MRCK_S01_Krig_quad'))->toBe('MRCK_S01_Krig_quad')
            ->and(resolver()->extractClassNamePrefix('MRCK_S02_MISC_Fury'))->toBe('MRCK_S02_MISC_Fury')
            ->and(resolver()->extractClassNamePrefix('MRCK_S04_AEGS_Redeemer'))->toBe('MRCK_S04_AEGS_Redeemer');
    });

    it('groups MRCK variants of the same product', function () {
        expect(resolver()->extractClassNamePrefix('MRCK_S01_Krig_quad'))->toBe(resolver()->extractClassNamePrefix('MRCK_S01_Krig_quad_right'))
            ->toBe('MRCK_S01_Krig_quad')
            ->and(resolver()->extractClassNamePrefix('MRCK_S02_MISC_Fury'))->toBe(resolver()->extractClassNamePrefix('MRCK_S02_MISC_Fury_Dual'))
            ->toBe('MRCK_S02_MISC_Fury');
    });

    it('separates MRCK items from different manufacturers', function () {
        expect(resolver()->extractClassNamePrefix('MRCK_S02_BEHR_Single_S02'))->not->toBe(resolver()->extractClassNamePrefix('MRCK_S02_Krig_Triple'));
    });

    it('separates MRCK items from same manufacturer different product', function () {
        expect(resolver()->extractClassNamePrefix('MRCK_S02_BEHR_Single_S02'))->not->toBe(resolver()->extractClassNamePrefix('MRCK_S02_BEHR_Dual_S01'));
    });

    it('handles MRCK ship-specific naming without S## at index 1', function () {
        expect(resolver()->extractClassNamePrefix('MRCK_ANVL_Ballista_Quad_S05'))->toBe('MRCK_ANVL_Ballista_Quad_S05');
    });

    it('does not apply MRCK rule to non-MRCK items with S## at index 1', function () {
        expect(resolver()->extractClassNamePrefix('COOL_ACOM_S01_IcePlunge_SCItem'))->toBe('COOL_ACOM_S01');
    });

    it('refines blocked prefix with alphanumeric set identifier', function () {
        $resolver = resolver();
        $prefix = $resolver->extractClassNamePrefix('eld_shirt_04_crus07_01');
        expect($prefix)->toBe('eld_shirt_04')
            ->and($resolver->refineClassNamePrefix('eld_shirt_04_crus07_01', $prefix))->toBe('eld_shirt_04_crus07')
            ->and($resolver->refineClassNamePrefix('eld_shirt_04_crus07_12', $prefix))->toBe('eld_shirt_04_crus07')
            ->and($resolver->refineClassNamePrefix('eld_shirt_04_iae2021_01', $prefix))->toBe('eld_shirt_04_iae2021')
            ->and($resolver->refineClassNamePrefix('cbd_hat_03_iae2021_01', 'cbd_hat_03'))->toBe('cbd_hat_03_iae2021')
            ->and($resolver->refineClassNamePrefix('eld_shirt_04_drake_03', 'eld_shirt_04'))->toBe('eld_shirt_04_drake');
    });

    it('refines blocked prefix with pure-alpha set identifier', function () {
        expect(resolver()->refineClassNamePrefix('eld_shirt_04_fleetweek_01_dec', 'eld_shirt_04'))->toBe('eld_shirt_04_fleetweek');
    });

    it('refines blocked prefix with numeric sub-design', function () {
        $resolver = resolver();
        expect($resolver->refineClassNamePrefix('fio_jacket_01_01_01', 'fio_jacket_01'))->toBe('fio_jacket_01_01')
            ->and($resolver->refineClassNamePrefix('fio_jacket_01_01_02', 'fio_jacket_01'))->toBe('fio_jacket_01_01')
            ->and($resolver->refineClassNamePrefix('fio_jacket_01_01_12', 'fio_jacket_01'))->toBe('fio_jacket_01_01');
    });

    it('refines blocked prefix for clothing color variants', function () {
        expect(resolver()->refineClassNamePrefix('nrs_shoes_03_01_01', 'nrs_shoes_03'))->toBe('nrs_shoes_03_01')
            ->and(resolver()->refineClassNamePrefix('nrs_shoes_03_01_02', 'nrs_shoes_03'))->toBe('nrs_shoes_03_01');
    });

    it('separates different clothing designs within same manufacturer', function () {
        expect(resolver()->refineClassNamePrefix('alb_pants_01_01_01', 'alb_pants_01'))->toBe('alb_pants_01_01')
            ->and(resolver()->refineClassNamePrefix('alb_pants_01_02_01', 'alb_pants_01'))->toBe('alb_pants_01_02');
    });

    it('returns null when no segment exists after prefix', function () {
        expect(resolver()->refineClassNamePrefix('eld_shirt_04', 'eld_shirt_04'))->toBeNull()
            ->and(resolver()->refineClassNamePrefix('eld_shirt_04_01', 'eld_shirt_04'))->toBeNull();
    });
});

describe('extractPaintPrefix', function () {
    it('extracts Paint_ tag as prefix', function () {
        $item = ItemData::factory()->make([
            'class_name' => 'Paint_Cutter_Gloss_White_Red',
            'data' => ['stdItem' => ['Tags' => ['Paint_Cutter', '@Paint_Cutter_Gloss_White_Red']]],
        ]);
        expect(resolver()->extractPaintPrefix($item))->toBe('Paint_Cutter');
    });

    it('handles mixed-alphanumeric ship models', function () {
        $item = ItemData::factory()->make([
            'class_name' => 'Paint_100i_Blue_Gold',
            'data' => ['stdItem' => ['Tags' => ['Paint_100i', '@Paint_100i_Blue_Gold']]],
        ]);
        expect(resolver()->extractPaintPrefix($item))->toBe('Paint_100i');
    });

    it('falls back to class_name segment when no tag', function () {
        $item = ItemData::factory()->make([
            'class_name' => 'Paint_Cutter_Template',
            'data' => [],
        ]);
        expect(resolver()->extractPaintPrefix($item))->toBe('Paint_Cutter');
    });

    it('returns null for non-paint items', function () {
        $item = ItemData::factory()->make([
            'class_name' => 'COOL_ACOM_S01_IcePlunge_SCItem',
            'data' => [],
        ]);
        expect(resolver()->extractPaintPrefix($item))->toBeNull();
    });

    it('returns null for Skin_ items', function () {
        $item = ItemData::factory()->make([
            'class_name' => 'Skin_Gold',
            'data' => [],
        ]);
        expect(resolver()->extractPaintPrefix($item))->toBeNull();
    });
});

describe('isExcludedItem', function () {
    it('excludes placeholder name items', function () {
        $item = ItemData::factory()->make(['name' => '<= PLACEHOLDER =>']);
        expect(resolver()->isExcludedItem($item))->toBeTrue();
    });

    it('excludes invisible_ prefix', function () {
        $item = ItemData::factory()->make(['class_name' => 'invisible_medium_arms']);
        expect(resolver()->isExcludedItem($item))->toBeTrue();
    });

    it('excludes vanduul_ prefix', function () {
        $item = ItemData::factory()->make([
            'class_name' => 'vanduul_pilot_arms_01_01_01',
            'name' => 'Vanduul Arms',
        ]);
        expect(resolver()->isExcludedItem($item))->toBeTrue();
    });

    it('excludes customizer_ prefix', function () {
        $item = ItemData::factory()->make([
            'class_name' => 'customizer_pants',
            'name' => 'Customizer Pants',
        ]);
        expect(resolver()->isExcludedItem($item))->toBeTrue();
    });

    it('excludes med_body prefix', function () {
        $item = ItemData::factory()->make([
            'class_name' => 'med_body_torso',
            'name' => 'Med Body',
        ]);
        expect(resolver()->isExcludedItem($item))->toBeTrue();
    });

    it('excludes med_skeleton prefix', function () {
        $item = ItemData::factory()->make([
            'class_name' => 'med_skeleton_armL',
            'name' => 'Med Skeleton',
        ]);
        expect(resolver()->isExcludedItem($item))->toBeTrue();
    });

    it('excludes test_ prefix', function () {
        $item = ItemData::factory()->make([
            'class_name' => 'test_rn_powerplant_no_fuel',
            'name' => 'Test Powerplant',
        ]);
        expect(resolver()->isExcludedItem($item))->toBeTrue();
    });

    it('excludes _TEMPLATE in class_name', function () {
        $item = ItemData::factory()->make([
            'class_name' => 'Optics_TEMPLATE',
            'name' => 'Optics Template',
        ]);
        expect(resolver()->isExcludedItem($item))->toBeTrue();
    });

    it('excludes items where name equals class_name', function () {
        $item = ItemData::factory()->make([
            'class_name' => 'sc_nvy_bdu_boots_01_01_01',
            'name' => 'sc_nvy_bdu_boots_01_01_01',
        ]);
        expect(resolver()->isExcludedItem($item))->toBeTrue();
    });

    it('excludes TEST STRING name', function () {
        $item = ItemData::factory()->make([
            'class_name' => 'some_item',
            'name' => 'TEST STRING',
        ]);
        expect(resolver()->isExcludedItem($item))->toBeTrue();
    });

    it('does not exclude regular items', function () {
        $item = ItemData::factory()->make([
            'class_name' => 'cds_armor_medium_arms_01_01_01',
            'name' => 'ORC-mkX Arms',
        ]);
        expect(resolver()->isExcludedItem($item))->toBeFalse();
    });
});

describe('isKnownFalseMergePrefix', function () {
    it('blocks exact armor variant prefixes that merge different products', function () {
        expect(resolver()->isKnownFalseMergePrefix('qrt_combat_heavy_arms_02'))->toBeTrue()
            ->and(resolver()->isKnownFalseMergePrefix('srvl_combat_heavy_core_03'))->toBeTrue()
            ->and(resolver()->isKnownFalseMergePrefix('cds_combat_medium_arms_04'))->toBeTrue();
    });

    it('does not block more specific sub-prefixes', function () {
        expect(resolver()->isKnownFalseMergePrefix('qrt_combat_heavy_arms_02_01'))->toBeFalse()
            ->and(resolver()->isKnownFalseMergePrefix('cds_combat_medium_arms_04_01'))->toBeFalse();
    });

    it('does not block legitimate prefixes', function () {
        expect(resolver()->isKnownFalseMergePrefix('cds_armor_medium_arms_01'))->toBeFalse()
            ->and(resolver()->isKnownFalseMergePrefix('kap_light_helmet'))->toBeFalse()
            ->and(resolver()->isKnownFalseMergePrefix('behr_rifle_ballistic_01'))->toBeFalse()
            ->and(resolver()->isKnownFalseMergePrefix('eld_shirt_04_crus07'))->toBeFalse()
            ->and(resolver()->isKnownFalseMergePrefix('fio_jacket_01_01'))->toBeFalse()
            ->and(resolver()->isKnownFalseMergePrefix('nrs_shoes_03_01'))->toBeFalse();
    });

    it('blocks remaining clothing prefixes that still merge different products', function () {
        expect(resolver()->isKnownFalseMergePrefix('cbd_shirt_01'))->toBeTrue()
            ->and(resolver()->isKnownFalseMergePrefix('cbd_shirt_02'))->toBeTrue()
            ->and(resolver()->isKnownFalseMergePrefix('dmc_jacket_04'))->toBeTrue();
    });
});

describe('extractEntityTagNames', function () {
    it('parses object format entity_tag_map', function () {
        $data = ['entity_tag_map' => [
            ['tag' => 'abc-123', 'name' => 'Scourge'],
            ['tag' => 'def-456', 'name' => 'ORC-mkV'],
            ['tag' => 'ghi-789', 'name' => 'PAB-1'],
        ]];
        $item = ItemData::factory()->make(['data' => $data]);
        $names = resolver()->extractEntityTagNames($item);
        expect($names)->toContain('Scourge', 'ORC-mkV', 'PAB-1');
    });

    it('handles null UUID entries gracefully', function () {
        $data = ['entity_tag_map' => [
            ['tag' => '00000000-0000-0000-0000-000000000000'],
            ['tag' => 'def-456', 'name' => 'ORC-mkV'],
        ]];
        $item = ItemData::factory()->make(['data' => $data]);
        $names = resolver()->extractEntityTagNames($item);
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
        $item = ItemData::factory()->make(['data' => $data]);
        $names = resolver()->extractEntityTagNames($item);
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
        $item = ItemData::factory()->make(['data' => $data]);
        $names = resolver()->extractEntityTagNames($item);
        expect($names)->toBeEmpty();
    });

    it('filters case-insensitively', function () {
        $data = ['entity_tag_map' => [
            ['tag' => 'a', 'name' => 'unlootable'],
            ['tag' => 'b', 'name' => 'fps'],
            ['tag' => 'c', 'name' => 'Scourge'],
        ]];
        $item = ItemData::factory()->make(['data' => $data]);
        $names = resolver()->extractEntityTagNames($item);
        expect($names)->toContain('Scourge')->toHaveCount(1);
    });

    it('handles null entity_tag_map', function () {
        $item = ItemData::factory()->make(['data' => null]);
        expect(resolver()->extractEntityTagNames($item))->toBeEmpty();
    });

    it('filters out lifestyle tags', function () {
        $data = ['entity_tag_map' => [
            ['tag' => 'a', 'name' => 'Casual'],
            ['tag' => 'b', 'name' => 'Outdoors'],
            ['tag' => 'c', 'name' => 'Work'],
            ['tag' => 'd', 'name' => 'Rugged'],
            ['tag' => 'e', 'name' => 'ORC-mkV'],
        ]];
        $item = ItemData::factory()->make(['data' => $data]);
        $names = resolver()->extractEntityTagNames($item);
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
        $item = ItemData::factory()->make(['data' => $data]);
        $names = resolver()->extractEntityTagNames($item);
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
        $item = ItemData::factory()->make(['data' => $data]);
        $names = resolver()->extractEntityTagNames($item);
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
        $item = ItemData::factory()->make(['data' => $data]);
        $names = resolver()->extractEntityTagNames($item);
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
        $item = ItemData::factory()->make(['data' => $data]);
        $names = resolver()->extractEntityTagNames($item);
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
        $item = ItemData::factory()->make(['data' => $data]);
        $names = resolver()->extractEntityTagNames($item);
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
        $item = ItemData::factory()->make(['data' => $data]);
        $names = resolver()->extractEntityTagNames($item);
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
        $item = ItemData::factory()->make(['data' => $data]);
        $names = resolver()->extractEntityTagNames($item);
        expect($names)->toContain('P4-AR')
            ->and($names)->not->toContain('987', 'KilgoreAndPoole', 'Gemini', 'Octagon', 'Ninetails', 'XenoThreat', 'Overlord');
    });
});

describe('resolveSetNameFromEntityTags', function () {
    it('finds common tag as set name', function () {
        $group = [
            ItemData::factory()->make(['data' => ['entity_tag_map' => [
                ['tag' => 'a', 'name' => 'ORC-mkV'],
                ['tag' => 'b', 'name' => 'Grey'],
            ]]]),
            ItemData::factory()->make(['data' => ['entity_tag_map' => [
                ['tag' => 'a', 'name' => 'ORC-mkV'],
                ['tag' => 'c', 'name' => 'DarkGreen'],
            ]]]),
            ItemData::factory()->make(['data' => ['entity_tag_map' => [
                ['tag' => 'a', 'name' => 'ORC-mkV'],
                ['tag' => 'd', 'name' => 'Blue'],
            ]]]),
        ];
        expect(resolver()->resolveSetNameFromEntityTags($group))->toBe('ORC-mkV');
    });

    it('returns null when no common tag exists', function () {
        $group = [
            ItemData::factory()->make(['data' => ['entity_tag_map' => [
                ['tag' => 'a', 'name' => 'ProductA'],
            ]]]),
            ItemData::factory()->make(['data' => ['entity_tag_map' => [
                ['tag' => 'b', 'name' => 'ProductB'],
            ]]]),
        ];
        expect(resolver()->resolveSetNameFromEntityTags($group))->toBeNull();
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
});
