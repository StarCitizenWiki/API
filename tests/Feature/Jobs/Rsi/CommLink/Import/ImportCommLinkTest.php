<?php

namespace Tests\Feature\Jobs\Rsi\CommLink\Import;

use App\Jobs\Rsi\CommLink\Import\ImportCommLink;
use App\Models\Rsi\CommLink\CommLink;
use Database\Seeders\Rsi\CommLink\SeriesTableSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportCommLinkTest extends TestCase
{
    /**
     * Tests ideal conditions
     *
     * @covers \App\Jobs\Rsi\CommLink\Import\ImportCommLink::handle
     * @covers \App\Jobs\Rsi\CommLink\Import\ImportCommLink::filePath
     * @covers \App\Jobs\Rsi\CommLink\Import\ImportCommLink::createCommLink
     * @covers \App\Jobs\Rsi\CommLink\Import\ImportCommLink::getCommLinkData
     * @covers \App\Jobs\Rsi\CommLink\Import\ImportCommLink::addEnglishCommLinkTranslation
     * @covers \App\Jobs\Rsi\CommLink\Import\ImportCommLink::syncImageIds
     * @covers \App\Jobs\Rsi\CommLink\Import\ImportCommLink::syncLinkIds
     * @covers \App\Jobs\Rsi\CommLink\Import\ImportCommLink::checkCommLinkForChanges
     * @covers \App\Jobs\Rsi\CommLink\Import\ImportCommLink::contentHasChanged
     * @covers \App\Services\Parser\CommLink\AbstractBaseElement
     * @covers \App\Services\Parser\CommLink\Content
     * @covers \App\Services\Parser\CommLink\Image
     * @covers \App\Services\Parser\CommLink\Link
     * @covers \App\Services\Parser\CommLink\Metadata
     */
    public function testImport(): void
    {
        Storage::persistentFake('comm_links');
        File::copy(
            storage_path('framework/testing/comm_links/12663/2020-11-22_222222.html'),
            storage_path('framework/testing/disks/comm_links/12663/2020-11-22_222222.html'),
        );

        $job = new ImportCommLink(12663, '2020-11-22_222222.html');
        $job->handle();

        self::assertEquals(1, CommLink::query()->count());
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSystemLanguages();
        Artisan::call(
            'db:seed',
            [
                '--class' => SeriesTableSeeder::class,
            ]
        );
    }
}
