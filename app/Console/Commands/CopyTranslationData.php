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
    protected $description = 'Copies german translations to the new sc_item_translations table.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        DB::table('sc_item_translations')->insertUsing(
            ['locale_code', 'item_uuid', 'translation', 'updated_at', 'created_at'],
            function (Builder $query) {
                $query->select([
                    'star_citizen_unpacked_item_translations.locale_code',
                    'star_citizen_unpacked_item_translations.item_uuid',
                    'star_citizen_unpacked_item_translations.translation',
                    'star_citizen_unpacked_item_translations.updated_at',
                    'star_citizen_unpacked_item_translations.created_at'
                ])
                    ->from('star_citizen_unpacked_item_translations')
                    ->where('star_citizen_unpacked_item_translations.locale_code', 'de_DE')
                    ->leftJoin(
                        'sc_item_translations',
                        'sc_item_translations.locale_code',
                        '=',
                        'star_citizen_unpacked_item_translations.locale_code'
                    )
                    ->whereNull('sc_item_translations.item_uuid');
            }
        );

        return Command::SUCCESS;
    }
}
