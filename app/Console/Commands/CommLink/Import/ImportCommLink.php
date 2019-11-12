<?php

declare(strict_types=1);

namespace App\Console\Commands\CommLink\Import;

use App\Jobs\Rsi\CommLink\Parser\ParseCommLink;
use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class ImportCommLink extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:comm-link {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-Import a single downloaded Comm-Link.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->hasArgument('id')) {
            $this->error('Comm-Link ID missing.');

            return;
        }

        $id = (int) $this->argument('id');

        try {
            $commLink = CommLink::query()->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            $this->error('Comm-Link does not exist in DB.');

            return;
        }

        $file = basename(Arr::last(Storage::disk('comm_links')->files($id)));

        dispatch(new ParseCommLink($id, $file, $commLink, true));
    }
}
