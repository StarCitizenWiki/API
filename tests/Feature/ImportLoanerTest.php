<?php

declare(strict_types=1);

use App\Jobs\StarCitizen\Vehicle\ImportLoaner;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Vehicle;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    // Create a minimal HTML payload matching the RSI article structure.
    // Includes a representative sample of the current loaner matrix rows
    // to exercise modelsMap, modelMap, and raw-name resolution paths.
    $this->html = <<<'HTML'
<p><strong>Last Updated:</strong> April 16th, 2026 | 4.7.1-live.11592622</p>
<table>
<thead><tr><th>YOUR SHIP</th><th>OUR LOANER(S)</th></tr></thead>
<tbody>
<tr><td>600i Explorer and Executive</td><td>Cyclone</td></tr>
<tr><td>890 Jump</td><td>85x</td></tr>
<tr><td>Arrastra</td><td>Prospector, Mole, Arrow</td></tr>
<tr><td>Constellation Andromeda</td><td>P-52 Merlin</td></tr>
<tr><td>Constellation Phoenix</td><td>P-72 Archimedes, Lynx Rover</td></tr>
<tr><td>Cyclone Variants</td><td>Aurora MR</td></tr>
<tr><td>Dragonfly</td><td>Aurora MR</td></tr>
<tr><td>E1 Spirit</td><td>A1 Spirit</td></tr>
<tr><td>G12 Variants</td><td>Lynx</td></tr>
<tr><td>Genesis Starliner</td><td>Hercules C2</td></tr>
<tr><td>Hull D, E</td><td>Hull C, Hercules C2</td></tr>
<tr><td>Idris-M &amp; P</td><td>F7C-M Super Hornet, MPUV Passenger</td></tr>
<tr><td>Ironclad (+ Assault)</td><td>Caterpillar</td></tr>
<tr><td>Javelin</td><td>Idris-P, MPUV Cargo</td></tr>
<tr><td>Kraken (+ Privateer)</td><td>Polaris, Hercules C2, Caterpillar, and Buccaneer</td></tr>
<tr><td>Liberator</td><td>Hercules M2, F7C-M Super Hornet</td></tr>
<tr><td>Mantis</td><td>Aurora LN</td></tr>
<tr><td>Mole</td><td>Prospector</td></tr>
<tr><td>MPUV-Tractor</td><td>Aurora MR</td></tr>
<tr><td>Nox</td><td>Aurora MR</td></tr>
<tr><td>Pulse (+ LX)</td><td>Aurora MR</td></tr>
<tr><td>Storm Variants</td><td>Aurora MR</td></tr>
<tr><td>X1 (+ Velocity, Force)</td><td>Aurora MR</td></tr>
<tr><td>Zeus Mk II MR</td><td>Zeus Mk II ES</td></tr>
</tbody>
</table>
HTML;

    // All vehicle names referenced in the test HTML (both ships and loaners).
    // Names must match what the DB would hold after the modelMap/modelsMap transforms.
    $vehicleNames = [
        // Ships (after modelsMap expansion)
        '600i Explorer', '600i Touring',
        '890 Jump', 'Arrastra',
        'Constellation Andromeda', 'Constellation Phoenix',
        'Cyclone', 'Cyclone TR', 'Cyclone RN', 'Cyclone RC', 'Cyclone AA',
        'Dragonfly Yellowjacket', 'Dragonfly Black',
        'E1 Spirit',
        'G12', 'G12a', 'G12r',
        'Genesis',
        'Hull D', 'Hull E',
        'Idris-P', 'Idris-M',
        'Ironclad', 'Ironclad Assault',
        'Javelin',
        'Kraken', 'Kraken Privateer',
        'Liberator',
        'Mantis',
        'Mole',
        'MPUV Tractor',
        'Nox', 'Nox Kue',
        'Pulse', 'Pulse LX',
        'Storm', 'Storm AA',
        'X1', 'X1 Velocity', 'X1 Force',
        'Zeus Mk II MR',
        // Loaners (after modelMap transform)
        '85X',
        'Aurora Mk I MR', 'Aurora Mk I LN',
        'Prospector',
        'MOLE',
        'Arrow',
        'P-52 Merlin',
        'P-72 Archimedes',
        'Lynx',
        'C2 Hercules',
        'Hull C',
        'MPUV Personnel', 'MPUV Cargo',
        'Caterpillar', 'Buccaneer',
        'M2 Hercules',
        'F7C-M Super Hornet Mk I',
        'Idris-P',
        'Polaris',
        'A1 Spirit',
        'Zeus Mk II ES',
    ];

    $this->vehicles = collect($vehicleNames)
        ->unique()
        ->mapWithKeys(fn (string $name) => [
            $name => Vehicle::factory()->create(['name' => $name]),
        ]);
});

it('resolves all loaner relationships without missing vehicles', function (): void {
    Http::fake([
        'support.robertsspaceindustries.com/*' => Http::response([
            'article' => ['body' => $this->html],
        ]),
    ]);

    (new ImportLoaner)->handle();

    // 600i Explorer and Executive -> expanded to both variants, loaner = Cyclone
    expect($this->vehicles['600i Explorer']->fresh()->loaner)->toHaveCount(1)
        ->and($this->vehicles['600i Explorer']->fresh()->loaner->first()->name)->toBe('Cyclone')
        ->and($this->vehicles['600i Touring']->fresh()->loaner)->toHaveCount(1)
        ->and($this->vehicles['600i Touring']->fresh()->loaner->first()->name)->toBe('Cyclone');

    // 890 Jump -> 85X (modelMap: 85x -> 85X)
    expect($this->vehicles['890 Jump']->fresh()->loaner)->toHaveCount(1)
        ->and($this->vehicles['890 Jump']->fresh()->loaner->first()->name)->toBe('85X');

    // Cyclone Variants -> expanded to 5 variants, loaner = Aurora MR (modelMap)
    foreach (['Cyclone', 'Cyclone TR', 'Cyclone RN', 'Cyclone RC', 'Cyclone AA'] as $variant) {
        expect($this->vehicles[$variant]->fresh()->loaner)->toHaveCount(1)
            ->and($this->vehicles[$variant]->fresh()->loaner->first()->name)->toBe('Aurora Mk I MR');
    }

    // Ironclad (+ Assault) -> expanded to both, loaner = Caterpillar
    expect($this->vehicles['Ironclad']->fresh()->loaner)->toHaveCount(1)
        ->and($this->vehicles['Ironclad Assault']->fresh()->loaner)->toHaveCount(1);

    // Pulse (+ LX) -> expanded to both, loaner = Aurora MR
    expect($this->vehicles['Pulse']->fresh()->loaner)->toHaveCount(1)
        ->and($this->vehicles['Pulse LX']->fresh()->loaner)->toHaveCount(1);

    // MPUV-Tractor -> modelMap to MPUV Tractor
    expect($this->vehicles['MPUV Tractor']->fresh()->loaner)->toHaveCount(1)
        ->and($this->vehicles['MPUV Tractor']->fresh()->loaner->first()->name)->toBe('Aurora Mk I MR');

    // Constellation Phoenix -> P-72 Archimedes + Lynx Rover (modelMap: Lynx Rover -> Lynx)
    expect($this->vehicles['Constellation Phoenix']->fresh()->loaner)->toHaveCount(2);
    $loanerNames = $this->vehicles['Constellation Phoenix']->fresh()->loaner->pluck('name')->sort()->values();
    expect($loanerNames)->toContain('Lynx', 'P-72 Archimedes');

    // Kraken (+ Privateer) -> expanded to Kraken + Kraken Privateer, 4 loaners
    expect($this->vehicles['Kraken']->fresh()->loaner)->toHaveCount(4);
    expect($this->vehicles['Kraken Privateer']->fresh()->loaner)->toHaveCount(4);

    // Genesis Starliner -> Hercules C2 (modelMap: Hercules C2 -> C2 Hercules)
    expect($this->vehicles['Genesis']->fresh()->loaner->first()->name)->toBe('C2 Hercules');

    // Liberator -> Hercules M2 (modelMap) + F7C-M Super Hornet (LIKE match)
    expect($this->vehicles['Liberator']->fresh()->loaner)->toHaveCount(2);

    // Mantis -> Aurora LN (modelMap)
    expect($this->vehicles['Mantis']->fresh()->loaner->first()->name)->toBe('Aurora Mk I LN');

    // Idris-M & P -> MPUV Passenger (modelMap -> MPUV Personnel) + F7C-M Super Hornet
    expect($this->vehicles['Idris-P']->fresh()->loaner)->toHaveCount(2);
    expect($this->vehicles['Idris-M']->fresh()->loaner)->toHaveCount(2);
    $loanerNames = $this->vehicles['Idris-P']->fresh()->loaner->pluck('name')->sort()->values();
    expect($loanerNames)->toContain('MPUV Personnel');

    // Zeus Mk II MR -> Zeus Mk II ES (exact match)
    expect($this->vehicles['Zeus Mk II MR']->fresh()->loaner)->toHaveCount(1)
        ->and($this->vehicles['Zeus Mk II MR']->fresh()->loaner->first()->name)->toBe('Zeus Mk II ES');
});

it('stores the version on the pivot table', function (): void {
    Http::fake([
        'support.robertsspaceindustries.com/*' => Http::response([
            'article' => ['body' => $this->html],
        ]),
    ]);

    (new ImportLoaner)->handle();

    $pivot = $this->vehicles['890 Jump']->fresh()->loaner->first()->pivot;
    expect($pivot->version)->toBe('4.7.1-LIVE.11592622');
});
