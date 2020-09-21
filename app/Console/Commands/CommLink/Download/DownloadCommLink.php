<?php declare(strict_types=1);

namespace App\Console\Commands\CommLink\Download;

use App\Console\Commands\CommLink\AbstractCommLinkCommand as CommLinkCommand;
use App\Console\Commands\CommLink\Image\CreateImageHashes;
use App\Jobs\Rsi\CommLink\Download\DownloadCommLink as DownloadCommLinkJob;
use App\Jobs\Rsi\CommLink\Image\CreateImageMetadata;
use App\Jobs\Rsi\CommLink\Parser\ParseCommLinkDownload;
use Illuminate\Bus\Dispatcher;
use Illuminate\Support\Collection;

/**
 * Download one or multiple Comm-Links by provided ID(s)
 * If --import option is passed the downloaded Comm-Links will also be parsed
 */
class DownloadCommLink extends CommLinkCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comm-links:download {id* : Comm-Link ID(s)} 
                                                {--i|import : Import Comm-Link after Download} 
                                                {--o|overwrite : Overwrite existing Comm-Links}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download Comm-Links with given IDs';

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
        $this->info('Downloading specified Comm-Links');

        $minId = collect($this->argument('id'))->filter(
            static function ($id) {
                return is_numeric($id);
            }
        )
            ->filter(
                static function ($id) {
                    return (int) $id >= self::FIRST_COMM_LINK_ID;
                }
            )
            ->tap(
                function (Collection $collection) {
                    $this->createProgressBar($collection->count());
                }
            )
            ->each(
                function (int $id) {
                    $this->dispatcher->dispatch(new DownloadCommLinkJob($id, $this->option('overwrite') === true));
                    $this->advanceBar();
                }
            )
            ->min();

        $this->finishBar();

        if ($this->option('import') === true) {
            $this->dispatchImportJob((int) $minId);
        }

        return 0;
    }

    /**
     * Import jobs to run after downloading comm link files
     *
     * @param int $minId
     */
    private function dispatchImportJob(int $minId): void
    {
        $this->info("\nImporting Comm-Links");
        $this->dispatcher->dispatch(new ParseCommLinkDownload($minId));
        $this->dispatcher->dispatch(new CreateImageMetadata());
        $this->dispatcher->dispatch(new CreateImageHashes());
    }
}
