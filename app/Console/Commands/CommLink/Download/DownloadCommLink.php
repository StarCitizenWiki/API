<?php declare(strict_types=1);

namespace App\Console\Commands\CommLink\Download;

use App\Console\Commands\CommLink\Image\CreateImageHashes;
use App\Jobs\Rsi\CommLink\Download\DownloadCommLink as DownloadCommLinkJob;
use App\Jobs\Rsi\CommLink\Image\CreateImageMetadata;
use App\Jobs\Rsi\CommLink\Parser\ParseCommLinkDownload;
use Illuminate\Bus\Dispatcher;
use Illuminate\Console\Command;

/**
 * Download one or multiple Comm-Links by provided ID(s)
 * If --import option is passed the downloaded Comm-Links will also be parsed
 */
class DownloadCommLink extends Command
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
        $ids = collect($this->argument('id'));

        $ids = $ids->filter(
            static function ($id) {
                return is_numeric($id);
            }
        )->filter(
            static function ($id) {
                return (int) $id >= 12663;
            }
        );

        $bar = $this->output->createProgressBar(count($ids));

        $ids->each(
            function (int $id) use ($bar) {
                $this->dispatcher->dispatch(new DownloadCommLinkJob($id, $this->hasOption('overwrite') === true));
                $bar->advance();
            }
        );

        $bar->finish();

        if ($this->hasOption('import') === true) {
            $this->info("\nImporting Comm-Links");
            $this->dispatcher->dispatch(new ParseCommLinkDownload((int) $ids->min()));
            $this->dispatcher->dispatch(new CreateImageMetadata());
            $this->dispatcher->dispatch(new CreateImageHashes());
        }

        return 0;
    }
}
