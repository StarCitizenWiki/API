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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class Items implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private array $ignoredNames = [
        'TRGT. STATUS',
        'TEST STRING NAME',
    ];

    private array $ignoredTypes = [
        'TargetSelector',
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
        $chunkSize = 25;
        $i = 0;
        $labels = (new Labels())->getData();
        $manufacturers = (new Manufacturers())->getData();

        $files = Storage::allFiles('api/scunpacked-data/items');

        collect($files)
            ->filter(function (string $file) {
                return strpos($file, '-raw.json') === false;
            })
            ->chunk($chunkSize)
            ->tap(function (Collection $chunks) use ($chunkSize) {
                dump(sprintf('Total Cunks: %s', $chunks->count()));
            })
            ->each(function (Collection $chunk) use ($labels, $manufacturers, $i) {
                dump(sprintf('Chunks %s/%s', (++$i), $chunk->count()));
                $chunk->map(function (string $file) use ($labels, $manufacturers) {
                    return [
                        'file' => $file,
                        'item' => (new \App\Services\Parser\StarCitizenUnpacked\Item($file, $labels, $manufacturers))->getData()
                    ];
                })
                    ->filter(function (array $data) {
                        return $data['item'] !== null;
                    })
                    ->filter(function (array $data) {
                        $item = $data['item'];
                        return isset($item['name']) && !in_array($item['name'], $this->ignoredNames, true);
                    })
                    ->filter(function (array $data) {
                        $item = $data['item'];
                        return isset($item['type']) && !in_array($item['type'], $this->ignoredTypes, true);
                    })
                    ->map(function (array $data) {
                        \App\Jobs\StarCitizenUnpacked\Import\Item::dispatch($data['item']);

                        return [
                            'item' =>  $data['item'],
                            'file' => $data['file'],
                        ];
                    })
                    ->each(function (array $data) {
                        ['item' => $item, 'file' => $path] = $data;
                        ItemSpecificationCreator::createSpecification($item, $path);
                    });
            });
    }
}
