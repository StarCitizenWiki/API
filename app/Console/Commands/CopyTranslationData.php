<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CopyTranslationData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unpacked:copy-translations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        DB::table('sc_item_translations')->insertUsing(
            ['locale_code', 'item_uuid', 'translation', 'updated_at', 'created_at'],
            function (Builder $query) {
                $query
                    ->select(['locale_code', 'item_uuid', 'translation', 'updated_at', 'created_at'])
                    ->from('star_citizen_unpacked_item_translations')
                ->where('locale_code', 'de_DE');
            }
        );

        return Command::SUCCESS;
    }
}
