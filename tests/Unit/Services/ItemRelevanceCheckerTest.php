<?php

declare(strict_types=1);

use App\Services\ItemRelevanceChecker;

describe('isPlayerRelevant', function () {
    describe('name-based checks', function () {
        it('excludes null name', function () {
            expect(ItemRelevanceChecker::isPlayerRelevant(null, 'some_class'))->toBeFalse();
        });

        it('excludes empty name', function () {
            expect(ItemRelevanceChecker::isPlayerRelevant('', 'some_class'))->toBeFalse();
        });

        it('excludes placeholder name items', function () {
            expect(ItemRelevanceChecker::isPlayerRelevant('<= PLACEHOLDER =>', 'some_class'))->toBeFalse();
        });

        it('excludes TEST STRING name', function () {
            expect(ItemRelevanceChecker::isPlayerRelevant('TEST STRING', 'some_class'))->toBeFalse();
        });

        it('excludes names containing - name', function () {
            expect(ItemRelevanceChecker::isPlayerRelevant('Weapon - name', 'some_class'))->toBeFalse();
        });

        it('excludes PH - prefix names', function () {
            expect(ItemRelevanceChecker::isPlayerRelevant('PH - Test Item', 'some_class'))->toBeFalse();
        });

        it('excludes [PH] prefix names', function () {
            expect(ItemRelevanceChecker::isPlayerRelevant('[PH] Test Item', 'some_class'))->toBeFalse();
        });

        it('excludes ELD - prefix names', function () {
            expect(ItemRelevanceChecker::isPlayerRelevant('ELD - Shirt', 'some_class'))->toBeFalse();
        });

        it('excludes Treat Injuries prefix names', function () {
            expect(ItemRelevanceChecker::isPlayerRelevant('Treat Injuries Arm', 'some_class'))->toBeFalse();
        });

        it('excludes @item prefix names', function () {
            expect(ItemRelevanceChecker::isPlayerRelevant('@item_FOO', 'some_class'))->toBeFalse();
        });

        it('excludes items where name equals class_name', function () {
            expect(ItemRelevanceChecker::isPlayerRelevant('sc_nvy_bdu_boots_01_01_01', 'sc_nvy_bdu_boots_01_01_01'))->toBeFalse();
        });
    });

    describe('class name prefix checks', function () {
        it('excludes invisible_ prefix', function () {
            expect(ItemRelevanceChecker::isPlayerRelevant('Invisible Medium Arms', 'invisible_medium_arms'))->toBeFalse();
        });

        it('excludes vanduul_ prefix', function () {
            expect(ItemRelevanceChecker::isPlayerRelevant('Vanduul Arms', 'vanduul_pilot_arms_01_01_01'))->toBeFalse();
        });

        it('excludes customizer_ prefix', function () {
            expect(ItemRelevanceChecker::isPlayerRelevant('Customizer Pants', 'customizer_pants'))->toBeFalse();
        });

        it('excludes med_body prefix', function () {
            expect(ItemRelevanceChecker::isPlayerRelevant('Med Body', 'med_body_torso'))->toBeFalse();
        });

        it('excludes med_skeleton prefix', function () {
            expect(ItemRelevanceChecker::isPlayerRelevant('Med Skeleton', 'med_skeleton_armL'))->toBeFalse();
        });

        it('excludes test_ prefix', function () {
            expect(ItemRelevanceChecker::isPlayerRelevant('Test Powerplant', 'test_rn_powerplant_no_fuel'))->toBeFalse();
        });

        it('excludes mannequin_ prefix', function () {
            expect(ItemRelevanceChecker::isPlayerRelevant('Mannequin', 'mannequin_body'))->toBeFalse();
        });

        it('excludes nodraw_ prefix', function () {
            expect(ItemRelevanceChecker::isPlayerRelevant('NoDraw Item', 'nodraw_container'))->toBeFalse();
        });

        it('excludes volume_ prefix', function () {
            expect(ItemRelevanceChecker::isPlayerRelevant('Volume', 'volume_trigger'))->toBeFalse();
        });
    });

    describe('class name template checks', function () {
        it('excludes _TEMPLATE in class_name', function () {
            expect(ItemRelevanceChecker::isPlayerRelevant('Optics Template', 'Optics_TEMPLATE'))->toBeFalse();
        });

        it('excludes _template suffix (lowercase)', function () {
            expect(ItemRelevanceChecker::isPlayerRelevant('Item', 'some_item_template'))->toBeFalse();
        });

        it('excludes _temp suffix', function () {
            expect(ItemRelevanceChecker::isPlayerRelevant('Item', 'some_item_temp'))->toBeFalse();
        });

        it('excludes _templ suffix', function () {
            expect(ItemRelevanceChecker::isPlayerRelevant('Item', 'some_item_templ'))->toBeFalse();
        });
    });

    describe('class name suffix checks', function () {
        it('excludes _vncl suffix', function () {
            expect(ItemRelevanceChecker::isPlayerRelevant('Item', 'weapon_vncl'))->toBeFalse();
        });

        it('excludes _ai suffix', function () {
            expect(ItemRelevanceChecker::isPlayerRelevant('Item', 'weapon_ai'))->toBeFalse();
        });

        it('excludes _ai_exclusive suffix', function () {
            expect(ItemRelevanceChecker::isPlayerRelevant('Item', 'weapon_ai_exclusive'))->toBeFalse();
        });

        it('excludes _dummy suffix', function () {
            expect(ItemRelevanceChecker::isPlayerRelevant('Item', 'item_dummy'))->toBeFalse();
        });

        it('excludes _debug suffix', function () {
            expect(ItemRelevanceChecker::isPlayerRelevant('Item', 'item_debug'))->toBeFalse();
        });

        it('excludes _placeholder suffix', function () {
            expect(ItemRelevanceChecker::isPlayerRelevant('Item', 'item_placeholder'))->toBeFalse();
        });

        it('excludes _test suffix', function () {
            expect(ItemRelevanceChecker::isPlayerRelevant('Item', 'item_test'))->toBeFalse();
        });
    });

    describe('allows regular items', function () {
        it('does not exclude regular items', function () {
            expect(ItemRelevanceChecker::isPlayerRelevant('ORC-mkX Arms', 'cds_armor_medium_arms_01_01_01'))->toBeTrue();
        });

        it('allows items with null class_name', function () {
            expect(ItemRelevanceChecker::isPlayerRelevant('Cool Item', null))->toBeTrue();
        });

        it('allows items with empty class_name', function () {
            expect(ItemRelevanceChecker::isPlayerRelevant('Cool Item', ''))->toBeTrue();
        });
    });
});

describe('isVehiclePlayerRelevant', function () {
    it('excludes powersuit class name', function () {
        expect(ItemRelevanceChecker::isVehiclePlayerRelevant('powersuit'))->toBeFalse();
    });

    it('excludes powersuit case-insensitively', function () {
        expect(ItemRelevanceChecker::isVehiclePlayerRelevant('PowerSuit'))->toBeFalse();
    });

    it('excludes _teach suffix', function () {
        expect(ItemRelevanceChecker::isVehiclePlayerRelevant('aegs_avenger_teach'))->toBeFalse();
    });

    it('excludes _boarded suffix', function () {
        expect(ItemRelevanceChecker::isVehiclePlayerRelevant('aegs_avenger_boarded'))->toBeFalse();
    });

    it('excludes _dunlevy suffix', function () {
        expect(ItemRelevanceChecker::isVehiclePlayerRelevant('ship_dunlevy'))->toBeFalse();
    });

    it('excludes _tier_1 suffix', function () {
        expect(ItemRelevanceChecker::isVehiclePlayerRelevant('apollo_med_tier_1'))->toBeFalse();
    });

    it('excludes _tier_2 suffix', function () {
        expect(ItemRelevanceChecker::isVehiclePlayerRelevant('apollo_med_tier_2'))->toBeFalse();
    });

    it('excludes _tier_3 suffix', function () {
        expect(ItemRelevanceChecker::isVehiclePlayerRelevant('apollo_med_tier_3'))->toBeFalse();
    });

    it('excludes _low_fuel_temporary suffix', function () {
        expect(ItemRelevanceChecker::isVehiclePlayerRelevant('ship_low_fuel_temporary'))->toBeFalse();
    });

    it('excludes _temporary suffix', function () {
        expect(ItemRelevanceChecker::isVehiclePlayerRelevant('ship_temporary'))->toBeFalse();
    });

    it('excludes _temp_cosa suffix', function () {
        expect(ItemRelevanceChecker::isVehiclePlayerRelevant('ship_temp_cosa'))->toBeFalse();
    });

    it('allows regular vehicle class names', function () {
        expect(ItemRelevanceChecker::isVehiclePlayerRelevant('aegs_avenger_stalker'))->toBeTrue();
    });

    it('allows vehicle class names with underscores', function () {
        expect(ItemRelevanceChecker::isVehiclePlayerRelevant('misc_freelancer'))->toBeTrue();
    });
});
