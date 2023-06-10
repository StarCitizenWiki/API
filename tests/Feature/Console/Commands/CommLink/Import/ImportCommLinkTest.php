<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\CommLink\Import;

use App\Jobs\Rsi\CommLink\Import\ImportCommLink;
use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Exception\RuntimeException;
use Tests\TestCase;

class ImportCommLinkTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testHandleMissingId(): void
    {
        $this->expectException(RuntimeException::class);

        $this->artisan('comm-links:import');
    }

    /**
     * A basic feature test example.
     *
     * @return void
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
