<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizenUnpacked;

use App\Console\Commands\AbstractQueueCommand;
use App\Jobs\SC\Import\ItemSpecificationCreator;
use App\Jobs\SC\Import\ShopItems;
use App\Jobs\SC\Import\Vehicle;
use App\Services\Parser\StarCitizenUnpacked\Item;
use App\Services\Parser\StarCitizenUnpacked\Labels;
use App\Services\Parser\StarCitizenUnpacked\Manufacturers;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ImportItems extends AbstractQueueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unpacked:import-items';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Items and Vehicles';

    private array $ignoredNames = [
        'TRGT. STATUS',
        'TEST STRING NAME',
    ];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $labels = (new Labels())->getData();
        $manufacturers = (new Manufacturers())->getData();

        $files = Storage::allFiles('api/scunpacked-data/items') + Storage::allFiles('api/scunpacked-data/ships');

        $this->info('Importing Items');
        collect($files)
            ->filter(function (string $file) {
                return !str_contains($file, '-raw.json');
            })
            ->chunk(25)
            ->tap(function (Collection $chunks) {
                $this->createProgressBar($chunks->count());
            })
            ->each(function (Collection $chunk) use ($labels, $manufacturers) {
                $this->bar->advance();

                $chunk->map(function (string $file) use ($labels, $manufacturers) {
                    return [
                        'file' => $file,
                        'item' => (new Item($file, $labels, $manufacturers))->getData()
                    ];
                })
                    ->filter(function (array $data) {
                        return $data['item'] !== null;
                    })
                    ->filter(function (array $data) {
                        $item = $data['item'];
                        return isset($item['name']) && !in_array($item['name'], $this->ignoredNames, true);
                    })
                    ->map(function (array $data) {
                        \App\Jobs\SC\Import\Item::dispatch($data['item']);

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

        $this->info("\n\nImporting Vehicles");
        Vehicle::dispatch();

        $this->info('Done. You can import shop items by running unpacked:import-shop-items');
        return Command::SUCCESS;
    }
}
