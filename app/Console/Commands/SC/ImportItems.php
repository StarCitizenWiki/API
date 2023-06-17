<?php

declare(strict_types=1);

namespace App\Console\Commands\SC;

use App\Console\Commands\AbstractQueueCommand;
use App\Jobs\SC\Import\ItemSpecificationCreator;
use App\Jobs\SC\Import\Vehicle;
use App\Services\Parser\SC\Item;
use App\Services\Parser\SC\Labels;
use App\Services\Parser\SC\Manufacturers;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use JsonException;

class ImportItems extends AbstractQueueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sc:import-items {--type=} {--skipVehicles} {--skipItems}';

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
     * @throws FileNotFoundException
     * @throws JsonException
     */
    public function handle(): int
    {
        $labels = (new Labels())->getData();
        $manufacturers = (new Manufacturers())->getData();

        $files = Storage::allFiles('api/scunpacked-data/items') + Storage::allFiles('api/scunpacked-data/ships');

        if ($this->option('skipItems') === false) {
            collect($files)
                ->filter(function (string $file) {
                    return !str_contains($file, '-raw.json');
                })
                ->tap(function (Collection $chunks) {
                    $this->info(sprintf(
                        'Importing %d items in chunks of 25 (%d).',
                        $chunks->count(),
                        (int)($chunks->count() / 25)
                    ));
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
                        ->filter(function (array $data) {
                            if (!$this->option('type') !== null) {
                                return true;
                            }

                            $types = array_map(
                                'strtolower',
                                array_map('trim', explode(',', $this->option('type')))
                            );

                            return in_array(strtolower($data['item']['type']), $types, true);
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
        }

        if ($this->option('skipVehicles') === false) {
            $this->info("\n\nImporting Vehicles");
            Artisan::call('sc:import-vehicles');
        }

        $this->info('Done. You can import shop items by running sc:import-shops');
        return Command::SUCCESS;
    }
}
