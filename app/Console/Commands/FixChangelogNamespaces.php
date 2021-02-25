<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixChangelogNamespaces extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:fix-changelogs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes old namespaces present in the changelog table';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        DB::table('model_changelogs')
            ->update([
                'changelog_type' => DB::raw('REPLACE("changelog_type", "\Api\StarCitizen", "\StarCitizen")'),
            ]);

        return 0;
    }
}
