<?php

declare(strict_types=1);

use App\Jobs\StarCitizen\Vehicle\ImportMsrp;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Vehicle;
use App\Models\StarCitizen\ShipMatrix\Vehicle\VehicleSku;
use App\Services\RsiDownloadClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

describe('ImportMsrp', function (): void {
    it('updates msrp, pledge_url and skus from the pledge-store upgrade v2 graphql API', function (): void {
        $vehicle = Vehicle::factory()->create([
            'cig_id' => 320,
            'name' => 'Tyilui',
            'msrp' => null,
            'pledge_url' => null,
        ]);

        Http::fake([
            'robertsspaceindustries.com/api/account/v2/setAuthToken' => Http::response(status: 200),
            'robertsspaceindustries.com/api/ship-upgrades/setContextToken' => Http::response(status: 200),
            'robertsspaceindustries.com/pledge-store/api/upgrade/v2/graphql' => Http::response([
                'data' => [
                    'ships' => [
                        [
                            'id' => '320',
                            'name' => 'Tyilui',
                            'msrp' => '42500',
                            'link' => '/pledge/ships/tyilui/Tyilui',
                            'skus' => [
                                [
                                    'id' => '19336',
                                    'title' => 'Tyilui',
                                    'available' => true,
                                    'price' => '42500',
                                ],
                            ],
                        ],
                        // Unknown ship must be ignored, not crash.
                        [
                            'id' => '9999999',
                            'name' => 'Nonexistent',
                            'msrp' => '100',
                            'link' => '/pledge/ships/nope',
                            'skus' => [],
                        ],
                    ],
                ],
            ]),
        ]);

        (new ImportMsrp)->handle(app(RsiDownloadClient::class));

        $vehicle->refresh();

        // msrp/price are stored with the last two (cents) digits stripped.
        expect($vehicle->msrp)->toBe(425)
            ->and($vehicle->pledge_url)->toBe('/pledge/ships/tyilui/Tyilui');

        expect(VehicleSku::where('vehicle_id', $vehicle->id)->count())->toBe(1)
            ->and(VehicleSku::where('cig_id', 19336)->value('price'))->toBe(425);
    });

    it('posts the initShipUpgrade operation to the v2 graphql endpoint', function (): void {
        Vehicle::factory()->create(['cig_id' => 320, 'msrp' => null]);

        $requests = collect();

        Http::fake(function (Request $request) use (&$requests) {
            $requests->push([
                'url' => $request->url(),
                'body' => $request->data(),
            ]);

            if (str_contains($request->url(), 'pledge-store/api/upgrade/v2/graphql')) {
                return Http::response(['data' => ['ships' => []]]);
            }

            return Http::response(status: 200);
        });

        (new ImportMsrp)->handle(app(RsiDownloadClient::class));

        $upgradeCall = $requests->firstWhere('url', 'https://robertsspaceindustries.com/pledge-store/api/upgrade/v2/graphql');

        expect($upgradeCall)->not->toBeNull()
            ->and($requests->pluck('url'))->not->toContain('https://robertsspaceindustries.com/pledge-store/api/upgrade')
            ->and($upgradeCall['body'])->toMatchArray([
                'operationName' => 'initShipUpgrade',
                'query' => trim(<<<'QUERY'
query initShipUpgrade {
  ships {
    id
    name
    msrp
    link
    skus {
      id
      title
      available
      price
    }
  }
}
QUERY),
            ])
            ->and((array) $upgradeCall['body']['variables'])->toBe([]);
    });

    it('skips ships that report no msrp', function (): void {
        $vehicle = Vehicle::factory()->create([
            'cig_id' => 321,
            'msrp' => null,
            'pledge_url' => null,
        ]);

        Http::fake([
            'robertsspaceindustries.com/api/account/v2/setAuthToken' => Http::response(status: 200),
            'robertsspaceindustries.com/api/ship-upgrades/setContextToken' => Http::response(status: 200),
            'robertsspaceindustries.com/pledge-store/api/upgrade/v2/graphql' => Http::response([
                'data' => [
                    'ships' => [
                        [
                            'id' => '321',
                            'name' => 'No Price Ship',
                            'msrp' => null,
                            'link' => null,
                            'skus' => [],
                        ],
                    ],
                ],
            ]),
        ]);

        (new ImportMsrp)->handle(app(RsiDownloadClient::class));

        $vehicle->refresh();

        expect($vehicle->msrp)->toBeNull()
            ->and($vehicle->pledge_url)->toBeNull()
            ->and(VehicleSku::where('vehicle_id', $vehicle->id)->exists())->toBeFalse();
    });
});
