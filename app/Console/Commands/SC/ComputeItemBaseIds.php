<?php

namespace App\Console\Commands\SC;

use Illuminate\Console\Command;

class ComputeItemBaseIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sc:compute-item-base-ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        \App\Jobs\SC\ComputeItemBaseIds::dispatch();
    }
}
