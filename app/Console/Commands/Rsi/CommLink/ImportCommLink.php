<?php

declare(strict_types=1);

namespace App\Console\Commands\Rsi\CommLink;

use App\Jobs\Rsi\CommLink\Import\ImportCommLink as ImportCommLinkJob;
use App\Jobs\Rsi\CommLink\Import\ImportCommLinks as ImportCommLinksJob;
use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class ImportCommLink extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comm-links:import {id? : Comm-Link ID starting at 12663} {--all : Import all downloaded Comm-Links}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import a single or all Comm-Links. Downloads single Comm-Link if missing.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        if ($this->hasOption('all')) {
            ImportCommLinksJob::dispatch(-1);
            return Command::SUCCESS;
        }

        if (!$this->hasArgument('id')) {
            $this->error('Missing Comm-Link ID argument.');

            return Command::FAILURE;
        }

        $id = (int)$this->argument('id');

        try {
            $commLink = CommLink::query()->where('cig_id', $id)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return Artisan::call('comm-links:download', [
                'id' => $id,
                '--import' => true
            ]);
        }

        if (count(Storage::disk('comm_links')->files($id)) === 0) {
            $this->error('Comm-Link does not exist on \'comm_links\' disk.');

            return Command::FAILURE;
        }

        $file = basename(Arr::last(Storage::disk('comm_links')->files($id)));

        dispatch(new ImportCommLinkJob($id, $file, $commLink, true));

        return Command::SUCCESS;
    }
}
