<?php declare(strict_types = 1);

namespace App\Console\Commands\CommLink\Download;

use App\Jobs\Rsi\CommLink\Parser\ParseCommLinkDownload;
use Illuminate\Bus\Dispatcher;
use Illuminate\Console\Command;
use App\Jobs\Rsi\CommLink\Download\DownloadCommLink as DownloadCommLinkJob;

/**
 * Class DownloadCommLink
 */
class DownloadCommLink extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'download:comm-link {id* : Comm Link ID(s)} {--i|import : Import Comm Link after Download}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download Comm Links with given IDs';

    /**
     * @var \Illuminate\Bus\Dispatcher
     */
    private $dispatcher;

    /**
     * Create a new command instance.
     *
     * @param \Illuminate\Bus\Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher)
    {
        parent::__construct();

        $this->dispatcher = $dispatcher;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info('Downloading specified Comm Links');
        $ids = collect($this->argument('id'));

        $ids = $ids->filter(
            function ($id) {
                return is_numeric($id);
            }
        )->filter(
            function ($id) {
                return (int) $id >= 12663;
            }
        );

        $bar = $this->output->createProgressBar(count($ids));

        $ids->each(
            function (int $id) use ($bar) {
                $this->dispatcher->dispatch(new DownloadCommLinkJob($id));
                $bar->advance();
            }
        );

        $bar->finish();

        if ($this->option('import')) {
            $this->info("\nImporting Comm Links");
            $this->dispatcher->dispatch(new ParseCommLinkDownload((int) $ids->min()));
        }
    }
}
