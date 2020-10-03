<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\CommLink\Image;

use App\Jobs\Rsi\CommLink\Image\CreateImageMetadatum;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Image\Image;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class CreateImageMetadataTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testHandle(): void
    {
        /** @var CommLink $commLink */
        $commLink = factory(CommLink::class)->create();
        $commLink->images()->saveMany(factory(Image::class, 20)->make());

        Bus::fake();

        $this->artisan('comm-links:images-create-metadata')
            ->expectsOutput('Starting creation of image metadata.')
            ->assertExitCode(0);

        Bus::assertDispatchedTimes(CreateImageMetadatum::class, 20);
    }
}
