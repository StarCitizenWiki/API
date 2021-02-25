<?php

namespace Tests\Feature\Jobs\Api\StarCitizen\Stat\Import;

use App\Jobs\StarCitizen\Stat\Import\ImportStat;
use App\Models\StarCitizen\Stat\Stat;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class ImportStatJobTest extends TestCase
{
    /**
     * @covers \App\Jobs\StarCitizen\Stat\Import\ImportStat::__construct
     * @covers \App\Jobs\StarCitizen\Stat\Import\ImportStat::handle
     */
    public function testMissingFile(): void
    {
        Storage::fake('stats');

        /** @var ImportStat $job */
        $job = $this->partialMock(
            ImportStat::class,
            function ($mock) {
                $mock->shouldReceive('fail')->once();
            }
        );

        $job->handle();
    }

    /**
     * @covers \App\Jobs\StarCitizen\Stat\Import\ImportStat::__construct
     * @covers \App\Jobs\StarCitizen\Stat\Import\ImportStat::handle
     */
    public function testInvalidFile(): void
    {
        $timestamp = now()->format('Y-m-d');
        $statFileName = "stats_{$timestamp}.json";

        Storage::persistentFake('stats')->put(
            sprintf('%d/%s', now()->year, $statFileName),
            'INVALID]'
        );

        $job = Mockery::mock(ImportStat::class, [$statFileName])->makePartial();
        $job->shouldReceive('delete')->once();

        $job->handle();
    }

    /**
     * @covers \App\Jobs\StarCitizen\Stat\Import\ImportStat::__construct
     * @covers \App\Jobs\StarCitizen\Stat\Import\ImportStat::handle
     */
    public function testImport(): void
    {
        $statFileName = 'stats_TEST.json';

        Storage::persistentFake('stats');
        Storage::persistentFake('stats')->put(
            sprintf('%d/%s', now()->year, $statFileName),
            '{"fans":2832591,"funds":31532537924,"fleet":null}'
        );

        $job = new ImportStat($statFileName);

        $job->handle();

        self::assertEquals(1, Stat::query()->count());
    }
}
