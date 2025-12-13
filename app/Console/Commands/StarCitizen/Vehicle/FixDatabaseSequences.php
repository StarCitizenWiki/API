<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizen\Vehicle;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixDatabaseSequences extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'starcitizen:fix-sequences';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix PostgreSQL sequences for vehicle-related tables to prevent ID conflicts';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tables = [
            'production_statuses',
            'production_notes',
            'vehicle_sizes',
            'vehicle_types',
        ];

        $this->info('Fixing PostgreSQL sequences for vehicle-related tables...');

        foreach ($tables as $table) {
            $sequence = "{$table}_id_seq";

            try {
                DB::statement("SELECT setval('{$sequence}', (SELECT COALESCE(MAX(id), 1) FROM {$table}))");
                $this->info("✓ Fixed sequence for {$table}");
            } catch (\Exception $e) {
                $this->error("✗ Failed to fix sequence for {$table}: {$e->getMessage()}");

                return Command::FAILURE;
            }
        }

        $this->newLine();
        $this->info('All sequences have been successfully fixed!');

        return Command::SUCCESS;
    }
}
