<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\CommLink\Import;

use App\Jobs\Rsi\CommLink\Import\ImportCommLink;
use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportCommLinkTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testHandleMissingId(): void
    {
        $this->artisan('comm-links:import')->assertExitCode(1);
    }

    /**
     * A basic feature test example.
     */
    public function testHandleAllOption(): void
    {
        Bus::fake();
        $this->artisan('comm-links:import --all')->assertExitCode(0);
    }

    /**
     * A basic feature test example.
     */
    public function testHandle(): void
    {
        CommLink::factory()->create(['cig_id' => 12663]);

        Storage::disk('comm_links')->createDirectory('12663');
        Storage::disk('comm_links')->put('12663\2012-01-01_000000.html', '');

        Bus::fake();

        $this->artisan('comm-links:import 12663')
            ->assertExitCode(0);

        Bus::assertDispatched(ImportCommLink::class);
    }
}
