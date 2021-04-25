<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizenUnpacked\Import;

use App\Models\StarCitizenUnpacked\Item;
use App\Services\Parser\StarCitizenUnpacked\Labels;
use App\Services\Parser\StarCitizenUnpacked\Manufacturers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class Items implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private array $ignoredNames = [
        '<= PLACEHOLDER =>',
        'TRGT. STATUS',
        'TEST STRING NAME',
        'Remote Turret',
    ];

    private array $ignoredTypes = [
        'SelfDestruct',
        'WeaponDefensive',
        'Container',
        'TurretBase',
        'WeaponDefensive',
        'TargetSelector',
        'FuelIntake',
        'FuelTank',
        'Door',
        'Ping',
        'FlightController',
        'CommsController',
        'CoolerController',
        'DoorController',
        'EnergyController',
        'LightController',
        'ShieldController',
        'TargetSelector',
        'WeaponController',
        'WheeledController',
    ];

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $labels = (new Labels())->getData();
        $manufacturers = (new Manufacturers())->getData();

        $files = Storage::allFiles('api/scunpacked-data/items');

        collect($files)
            ->filter(function (string $file) {
                return strpos($file, '-raw.json') === false;
            })
            ->map(function (string $file) use ($labels, $manufacturers) {
                return (new \App\Services\Parser\StarCitizenUnpacked\Item($file, $labels, $manufacturers))->getData();
            })
            ->filter(function ($item) {
                return $item !== null;
            })
            ->filter(function ($item) {
                return isset($item['name']) && !in_array($item['name'], $this->ignoredNames, true);
            })
            ->filter(function ($item) {
                return isset($item['type']) && !in_array($item['type'], $this->ignoredTypes, true);
            })
            ->filter(function ($item) {
                return !empty($item['name']) && strpos($item['name'], '[PH]') === false;
            })
            ->each(function ($item) {
                Item::updateOrCreate([
                    'uuid' => $item['uuid'],
                ], [
                    'name' => $item['name'],
                    'type' => $item['type'],
                    'sub_type' => $item['sub_type'],
                    'manufacturer' => $item['manufacturer'],
                    'size' => $item['size'],
                ]);
            });
    }
}