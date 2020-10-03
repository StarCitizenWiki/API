<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\CommLink\Import;

use App\Jobs\Rsi\CommLink\Parser\ParseCommLink;
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
    public function testHandleMissingInDb(): void
    {
        $this->artisan('comm-links:import 12663')
            ->expectsOutput('Comm-Link does not exist in DB.')
            ->assertExitCode(1);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testHandle(): void
    {
        factory(CommLink::class)->create(['id' => 12663]);

        Storage::disk('comm_links')->createDir('12663');
        Storage::disk('comm_links')->put('12663\2012-01-01_000000.html', '');

        Bus::fake();

        $this->artisan('comm-links:import 12663')
            ->assertExitCode(0);

        Bus::assertDispatched(ParseCommLink::class);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testHandleMissingFile(): void
    {
        factory(CommLink::class)->create(['id' => 18000]);

        Bus::fake();

        $this->artisan('comm-links:import 18000')
            ->assertExitCode(1);

        Bus::assertNotDispatched(ParseCommLink::class);
    }
}
