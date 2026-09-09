<?php

declare(strict_types=1);

use App\Services\Wiki\VehicleInfoboxParser;

function wikiFixture(string $name): string
{
    return file_get_contents(__DIR__.'/../../../Fixtures/Wiki/'.$name) ?: '';
}

it('parses the 300i infobox with padded params and key lowercasing', function (): void {
    $params = (new VehicleInfoboxParser)->parse(wikiFixture('300i.wikitext'));

    expect($params)->toBeArray()
        ->and($params['uuid'])->toBe('ce937681-caa2-4cfa-ab80-f5bf2a5b9a6c')
        ->and($params['name'])->toBe('300i')
        ->and($params['trailerurl'])->toBe('https://www.youtube.com/watch?v=qDQ8dbE8qSo')
        ->and($params['originalpledgecost'])->toBe('55')
        ->and($params['conceptdate'])->toBe('2013-06-21')
        ->and($params)->toHaveKey('manufacturer');
});

it('maps an empty value to null (300i stowage, perseus role)', function (string $fixture, string $key): void {
    $params = (new VehicleInfoboxParser)->parse(wikiFixture($fixture));

    expect($params[$key])->toBeNull();
})->with([
    '300i stowage' => ['300i.wikitext', 'stowage'],
    'perseus role' => ['perseus.wikitext', 'role'],
]);

it('strips html comments prefixing urls (600i executive)', function (): void {
    $params = (new VehicleInfoboxParser)->parse(wikiFixture('600i-executive.wikitext'));

    expect($params['trailerurl'])->toBe('https://youtu.be/gbCSQDhPOlk')
        ->and($params['galactapediaurl'])->toBe('https://robertsspaceindustries.com/galactapedia/article/0QxYA8xYLj-origin-600i-explorer')
        ->and($params['brochureurl'])->toBe('https://robertsspaceindustries.com/media/awfctcstfkykur/source/Origin600i-Brochure-Optimised.pdf');
});

it('maps a comment-only value to null (600i executive saledate)', function (): void {
    $params = (new VehicleInfoboxParser)->parse(wikiFixture('600i-executive.wikitext'));

    expect($params['saledate'])->toBeNull()
        ->and($params['pledgeurl'])->toBeNull();
});

it('strips an html comment embedded after the value (perseus queryimage trailing-comment shape)', function (): void {
    $wikitext = "{{Vehicle\n| storage = 2.244<!--actual total storage space note-->\n}}";

    $params = (new VehicleInfoboxParser)->parse($wikitext);

    expect($params['storage'])->toBe('2.244');
});

it('keeps a multi-url qaurl with semicolons intact (perseus)', function (): void {
    $params = (new VehicleInfoboxParser)->parse(wikiFixture('perseus.wikitext'));

    expect($params['qaurl'])->toBe(
        'https://robertsspaceindustries.com/comm-link/engineering/20880-Q-A-RSI-Perseus; '
        .'https://robertsspaceindustries.com/comm-link/engineering/17915-Q-A-RSI-Perseus'
    );
});

it('keeps nested template values verbatim (315p releasedate)', function (): void {
    $params = (new VehicleInfoboxParser)->parse(wikiFixture('315p.wikitext'));

    expect($params['releasedate'])->toBe('{{Start date and age|2930|sctime=yes|paren=yes}}')
        ->and($params['conceptdate'])->toBe('2013-06-21');
});

it('keeps wikilink suffixes and pads, lowercases keys, splits on first equals only', function (): void {
    $wikitext = <<<'WIKI'
        {{Vehicle
        |    TrailerURL   =  https://example.test/watch?v=aB3_dEf
        | pledgeavailability = Limited edition[[C8 Pisces#Acquisition|*]]
        }}
        WIKI;

    $params = (new VehicleInfoboxParser)->parse($wikitext);

    expect($params)->toHaveKeys(['trailerurl', 'pledgeavailability'])
        ->and($params['trailerurl'])->toBe('https://example.test/watch?v=aB3_dEf')
        ->and($params['pledgeavailability'])->toBe('Limited edition[[C8 Pisces#Acquisition|*]]');
});

it('returns an empty array when no vehicle template is present', function (): void {
    $wikitext = "'''A page''' without an infobox.\n\n{{InfoboxNeue}}\n{{Ship introductions|300i}}\n";

    expect((new VehicleInfoboxParser)->parse($wikitext))->toBe([]);
});

it('does not mistake other vehicle-named templates for the infobox', function (): void {
    $wikitext = "{{Vehicle variants}}\nsome text\n";

    expect((new VehicleInfoboxParser)->parse($wikitext))->toBe([]);
});
