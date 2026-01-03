<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizen\Vehicle;

use App\Events\StarCitizen\ShipMatrix\ShipMatrixStructureChanged;
use App\Services\RsiDownloadClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JsonException;
use RuntimeException;
use Throwable;

class ImportShipMatrix implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private const SHIPS_ENDPOINT = '/ship-matrix/index';

    private const VEHICLES_DISK = 'vehicles';

    public function handle(RsiDownloadClient $client): void
    {
        app('Log')::info('Downloading and importing Ship Matrix');

        try {
            $shipMatrixPath = $this->downloadShipMatrix($client);
            $this->cleanupDailyFiles($shipMatrixPath);
            $vehicles = $this->loadVehicles($shipMatrixPath);
            $this->assertStructure($vehicles);
        } catch (Throwable $e) {
            app('Log')::error(
                'Ship Matrix download or import failed',
                [
                    'message' => $e->getMessage(),
                ]
            );
            $this->fail($e);

            return;
        }

        collect($vehicles)->each(
            static function (array $vehicle): void {
                dispatch(new ImportVehicle(new Collection($vehicle)));
            }
        );
    }

    /**
     * @throws RuntimeException|RequestException|ConnectionException|JsonException
     */
    private function downloadShipMatrix(RsiDownloadClient $client): string
    {
        $path = $this->buildPath();

        $response = $client->forRsi()->throw()->get(config('services.rsi_url').self::SHIPS_ENDPOINT);

        $parsed = json_decode($response->body(), false, 512, JSON_THROW_ON_ERROR);

        if (($parsed->success ?? 0) !== 1) {
            throw new RuntimeException(
                sprintf('RSI data is not valid. Expected success = 1, got %d', $parsed->success ?? 0)
            );
        }

        $responseJsonData = json_encode($parsed->data, JSON_THROW_ON_ERROR);

        Storage::disk(self::VEHICLES_DISK)->put($path, $responseJsonData);

        return $path;
    }

    /**
     * @return array<int, array<string, mixed>>
     *
     * @throws FileNotFoundException|JsonException
     */
    private function loadVehicles(string $path): array
    {
        $content = Storage::disk(self::VEHICLES_DISK)->get($path);

        if ($content === null) {
            throw new FileNotFoundException("Ship Matrix file {$path} could not be read");
        }

        return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param  array<int, array<string, mixed>>  $vehicles
     *
     * @throws JsonException|RuntimeException
     */
    private function assertStructure(array $vehicles): void
    {
        if ($vehicles === []) {
            throw new RuntimeException('Ship Matrix payload is empty');
        }

        $groundTruth = File::get(storage_path('framework/testing/shipmatrix/aurora_es.json'));
        $groundTruth = collect(json_decode($groundTruth, true, 512, JSON_THROW_ON_ERROR));

        $diff = $groundTruth->diffKeys($vehicles[0]);

        if ($diff->count() !== 0) {
            $keys = $diff->keys();

            app('Log')::error('Ship Matrix structure changed, aborting job. Missing keys:', $keys->toArray());
            if (class_exists(ShipMatrixStructureChanged::class)) {
                ShipMatrixStructureChanged::dispatch();
            }

            throw new RuntimeException('Ship Matrix structure changed. Missing keys: '.$keys->implode(', '));
        }
    }

    private function cleanupDailyFiles(string $keepPath): void
    {
        $directory = Str::before($keepPath, '/');

        $files = Storage::disk(self::VEHICLES_DISK)->files($directory);

        collect($files)
            ->filter(static fn (string $file): bool => $file !== $keepPath && Str::contains($file, 'shipmatrix'))
            ->each(static fn (string $file): bool => Storage::disk(self::VEHICLES_DISK)->delete($file));
    }

    private function buildPath(): string
    {
        $dirName = now()->format('Y-m-d');
        $fileTimeStamp = now()->format('Y-m-d_H-i');
        $filename = "shipmatrix_{$fileTimeStamp}.json";

        return "{$dirName}/{$filename}";
    }
}
