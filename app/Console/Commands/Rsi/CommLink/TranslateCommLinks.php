<?php

declare(strict_types=1);

namespace App\Console\Commands\Rsi\CommLink;

use App\Console\Commands\Rsi\CommLink\AbstractCommLinkCommand as CommLinkCommand;
use App\Jobs\Rsi\CommLink\Translate\TranslateCommLinks as TranslateCommLinksJob;
use App\Traits\Jobs\GetFoldersTrait;

class TranslateCommLinks extends CommLinkCommand
{
    use GetFoldersTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comm-links:translate {modifiedTime=0 : Folders to include that were modified in the last minute}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Translate all Comm-Links using DeepL';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Dispatching Comm-Link Translation');

        $modifiedTime = (int)$this->argument('modifiedTime');

        if ($modifiedTime > 0) {
            $this->info("Including Comm-Links that were created in the last '{$modifiedTime}' minutes");
        } elseif ($modifiedTime === -1) {
            $this->info('Including all Comm-Links');
        }

        TranslateCommLinksJob::dispatch($this->filterDirectories('comm_links', $modifiedTime)->toArray());;

        return CommLinkCommand::SUCCESS;
    }
}
