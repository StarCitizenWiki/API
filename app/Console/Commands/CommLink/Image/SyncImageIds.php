<?php declare(strict_types=1);
/*
 * Copyright (c) 2020
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace App\Console\Commands\CommLink\Image;

use App\Console\Commands\CommLink\AbstractCommLinkCommand as CommLinkCommand;
use Illuminate\Bus\Dispatcher;

class SyncImageIds extends CommLinkCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comm-links:sync-image-ids {offset=0 : Comm-Link start ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-Parse all local Comm-Links and sync the images';

    /**
     * @var Dispatcher
     */
    private Dispatcher $dispatcher;

    /**
     * Create a new command instance.
     *
     * @param Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher)
    {
        parent::__construct();

        $this->dispatcher = $dispatcher;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Dispatching Comm-Link Image Sync');

        $offset = $this->parseOffset();

        $this->info("Starting at Comm-Link ID {$offset}");

        $this->dispatcher->dispatch(new \App\Jobs\Rsi\CommLink\SyncImageIds($offset));

        return 0;
    }
}
