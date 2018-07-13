<?php declare(strict_types = 1);

namespace App\Jobs\StarCitizen\Ships;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

/**
 * Class ParseShip
 */
class ParseShip implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $ship;

    /**
     * Create a new job instance.
     *
     * @param \Illuminate\Support\Collection $ship
     */
    public function __construct(Collection $ship)
    {
        $this->ship = $ship;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
    }
}
