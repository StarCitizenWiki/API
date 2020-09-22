<?php

declare(strict_types=1);

namespace App\Traits\Jobs\ShipMatrix;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

trait GetNewestShipMatrixFilenameTrait
{
    /**
     * Tries to return the newest ship matrix filename from the 'vehicles' disk
     *
     * @returns string
     *
     * @throws RuntimeException If 'vehicles' disk has no directories or if no file was found
     */
    protected function getNewestShipMatrixFilename(): string
    {
        $newestShipMatrixDir = Arr::last(Storage::disk('vehicles')->directories());

        if (null === $newestShipMatrixDir) {
            throw new RuntimeException('No Shipmatrix directories found');
        }

        $file = Arr::last(Storage::disk('vehicles')->files($newestShipMatrixDir));

        if (null !== $file && Str::contains($file, 'shipmatrix')) {
            return $file;
        }

        app('Log')::error('No Shipmatrix File on Disk \'vehicles\' found');

        throw new RuntimeException('No Shipmatrix File on Disk \'vehicles\' found');
    }
}