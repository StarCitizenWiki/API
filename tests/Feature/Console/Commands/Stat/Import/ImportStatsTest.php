<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\Stat\Import;

use App\Jobs\Api\StarCitizen\Stat\Parser\ParseStat;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class ImportStatsTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testHandle(): void
    {
        Bus::fake();

        $this->artisan('stats:import')
            ->expectsOutput('Starting funding statistics import')
            ->assertExitCode(0);

        Bus::assertDispatched(ParseStat::class);
    }
}
