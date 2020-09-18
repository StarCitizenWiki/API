<?php declare(strict_types=1);
/*
 * Copyright (c) 2020
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

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