<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizenUnpacked;

use App\Jobs\StarCitizenUnpacked\Import\WeaponAttachment;
use App\Models\StarCitizenUnpacked\Item;
use Illuminate\Console\Command;

class ImportWeaponAttachments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unpacked:import-weapon-attachments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import personal weapon attachments from scunpacked';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (Item::count() === 0) {
            $this->error('You need to run "unpacked:import-shop-items" first');
            return 1;
        }

        WeaponAttachment::dispatch();
        return 0;
    }
}
