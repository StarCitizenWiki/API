<?php

declare(strict_types=1);

namespace App\Console\Commands\SC;

use App\Console\Commands\AbstractQueueCommand;
use App\Jobs\SC\Import\Vehicle;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use JsonException;

class ImportVehicles extends AbstractQueueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sc:import-vehicles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import vehicles from scunpacked';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $vehicles = File::get(scdata('v2/ships.json'));
        } catch (FileNotFoundException $e) {
            $this->error('ships.json not found. Did you clone scunpacked?');
            return Command::FAILURE;
        }

        try {
            $vehicles = json_decode($vehicles, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $this->error($e->getMessage());

            return Command::FAILURE;
        }

        collect($vehicles)
            ->tap(function (Collection $chunks) {
                $this->info(sprintf(
                    'Importing %d vehicles in chunks of 5 (%d).',
                    $chunks->count(),
                    (int)($chunks->count() / 5)
                ));
            })
            ->chunk(5)
            ->tap(function (Collection $chunks) {
                $this->createProgressBar($chunks->count());
            })
            ->each(function (Collection $chunk) {
                $this->bar->advance();
                $chunk
                    ->filter(function (array $vehicle) {
                        return $this->isNotIgnoredClass($vehicle['ClassName']);
                    })
                    ->map(function (array $vehicle) {
                        $vehicle['filePathV2'] = scdata(sprintf(
                            'v2/ships/%s-raw.json',
                            strtolower($vehicle['ClassName'])
                        ));

                        $vehicle['filePath'] = scdata(sprintf(
                            'ships/%s.json',
                            strtolower($vehicle['ClassName'])
                        ));

                        return $vehicle;
                    })->each(function (array $vehicle) {
                        Vehicle::dispatch($vehicle);
                    });
            });

        return Command::SUCCESS;
    }

    private function isNotIgnoredClass(string $class): bool
    {
        $tests = [
            '_Hangar',
            'Active1',
            'BIS29',
            'Bombless',
            'CINEMATIC_ONLY',
            //'F7A_Mk1',
            'Fleetweek',
            'fw22nfz',
            'Indestructible',
            'Krugeri',
            'modifiers',
            'NO_CUSTOM',
            'NoCrimesAgainst',
            'Prison',
            'Showdown',
            'SM_TE',
            'Test',
            'Tutorial',
            'Unmanned',
        ];

        $isGood = true;

        foreach ($tests as $toTest) {
            $isGood = $isGood && stripos($class, $toTest) === false;
        }

        $isGood = $isGood && $class !== 'TEST_Boat';

        return $isGood;
    }
}
