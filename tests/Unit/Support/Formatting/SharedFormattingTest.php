<?php

declare(strict_types=1);

use App\Enums\Game\ItemGrade;
use App\Models\Game\ItemData;
use App\Support\Format;
use App\Support\Formatting\FormatDuration;
use Carbon\CarbonInterval;

it('preserves ship grade types and limits', function () {
    foreach ([1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D', 5 => 5, 7 => 7] as $grade => $expected) {
        expect(ItemGrade::shipLabel($grade))->toBe($expected)
            ->and(ItemData::formatGrade($grade, 'Ship.Component'))->toBe($expected)
            ->and(ItemData::formatGrade($grade, 'FPS.Weapon'))->toBe($grade);
    }
    expect(ItemGrade::shipLabel('1'))->toBe('1')->and(ItemGrade::shipLabel(null))->toBeNull();
});

it('preserves duration modes', function () {
    foreach ([-61, 0, 1, 60, 61, 3661] as $value) {
        expect(FormatDuration::interval($value, options: ['short' => true], locale: 'en'))
            ->toBe(CarbonInterval::seconds($value)->locale('en')->cascade()->forHumans(short: true))
            ->and(FormatDuration::interval($value, unit: 'minutes', options: ['short' => true]))
            ->toBe(CarbonInterval::minutes($value)->cascade()->forHumans(short: true));
    }
    expect(FormatDuration::fromSeconds(null))->toBeNull()
        ->and(FormatDuration::fromSeconds(-1))->toBe('0 seconds')
        ->and(FormatDuration::fromSeconds(60))->toBe('60 seconds')
        ->and(FormatDuration::fromSeconds(3661))->toBe(CarbonInterval::seconds(3661)->cascade()->forHumans(['parts' => 2]));
});

it('strips formatted decimal zeros', function () {
    expect(Format::stripTrailingZeros('1 234.50'))->toBe('1 234.5')
        ->and(Format::stripTrailingZeros('10.00'))->toBe('10');
});
