<?php
/*
 * Copyright (c) 2020
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

declare(strict_types=1);

namespace App\Console\Commands\Starmap\Download;

use App\Jobs\Api\StarCitizen\Starmap\Download\DownloadStarmap as DownloadStarmapJob;
use App\Jobs\Api\StarCitizen\Starmap\Parser\ParseStarmap;
use Illuminate\Bus\Dispatcher;
use Illuminate\Console\Command;

/**
 * Start Starmap Download Shop
 */
class DownloadStarmap extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'starmap:download 
                            {--f|force : Force Download, Overwrite File if exist} 
                            {--i|import : Import System, Celestial Objects and Jumppoint Tunnel after Download}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Starts the Starmap Download Job';

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
        $this->info('Dispatching Starmap Download');

        if ($this->option('force')) {
            $this->info('Forcing Download');
        }

        $this->dispatcher->dispatch(new DownloadStarmapJob($this->option('force')));
/*
        if ($this->option('import')) {
            $this->info('Starting Import');
            $this->dispatcher->dispatch(new ParseStarmapDownload());
        }*/

        return 0;
    }
}
