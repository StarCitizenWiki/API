<?php declare(strict_types = 1);

namespace App\Console\Commands;

use App\Jobs\Api\StarCitizen\Vehicle\Parser\ParseShipMatrixDownload;
use Illuminate\Bus\Dispatcher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Import given or latest shipmatrix file into db
 */
class ImportShipMatrix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:shipmatrix {file? : File on Vehicle Disk to import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Shipmatrix File into the Database';

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
     * @return mixed
     */
    public function handle()
    {
        $this->info("Dispatching Ship Matrix Parsing Job");
        if (null !== $this->argument('file')) {
            $file = $this->argument('file');

            $this->info("Using File {$file}");
            if (!Storage::disk('vehicles')->exists($file)) {
                $path = Storage::disk('vehicles')->path('');

                $this->error("File {$file} does not exist in Path {$path}");

                return false;
            }

            $this->dispatcher->dispatchNow(new ParseShipMatrixDownload($this->argument('file')));
        } else {
            $this->dispatcher->dispatchNow(new ParseShipMatrixDownload());
        }
    }
}
