<?php

declare(strict_types=1);

it('renders no prices available message when empty', function (): void {
    $view = $this->blade('<x-items.uex-prices-card :prices="[]" />');

    $view->assertSeeText('UEX Prices')
        ->assertSeeText('No prices available.');
});

it('renders prices with game_version column', function (): void {
    $view = $this->blade('<x-items.uex-prices-card :prices="$prices" />', [
        'prices' => [
            [
                'terminal_code' => 'LOR_HAB',
                'terminal_name' => 'Lorville Hangars',
                'web_url' => '/items/test',
                'price_buy' => 1520000,
                'price_sell' => 1480000,
                'game_version' => '4.7.1',
                'starmap_location' => [
                    'star_system_name' => 'Stanton',
                    'parent_name' => 'Hurston',
                ],
                'date_updated' => '2026-04-20T12:00:00Z',
            ],
        ],
    ]);

    $view->assertSeeText('Lorville Hangars')
        ->assertSeeText("1\u{00A0}520\u{00A0}000 aUEC")
        ->assertSeeText("1\u{00A0}480\u{00A0}000 aUEC")
        ->assertSeeText('4.7.1')
        ->assertSeeText('Stanton')
        ->assertSeeText('Hurston');
});

it('shows em-dash when game_version is missing', function (): void {
    $view = $this->blade('<x-items.uex-prices-card :prices="$prices" />', [
        'prices' => [
            [
                'terminal_name' => 'No Version Terminal',
                'price_buy' => 500,
                'price_sell' => 0,
                'starmap_location' => ['star_system_name' => 'Stanton'],
                'date_updated' => '2026-04-20T12:00:00Z',
            ],
        ],
    ]);

    $view->assertSeeText('No Version Terminal');
});
