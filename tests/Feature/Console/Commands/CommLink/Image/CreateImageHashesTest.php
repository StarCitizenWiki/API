<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\CommLink\Image;

use App\Jobs\Rsi\CommLink\Image\CreateImageHash;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Image\Image;
use App\Models\Rsi\CommLink\Image\ImageMetadata;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class CreateImageHashesTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testHandle(): void
    {
        /** @var CommLink $commLink */
        $commLink = CommLink::factory()->create();

        $images = Image::factory()->count(20)->create();
        $images->each(
            function (Image $image) {
                $image->metadata()->save(ImageMetadata::factory()->make());
            }
        );

        $commLink->images()->saveMany($images);

        Bus::fake();

        $this->artisan('comm-links:images-create-hashes')
            ->expectsOutput('Starting calculation of image hashes')
            ->assertExitCode(0);

        Bus::assertDispatchedTimes(CreateImageHash::class, 20);
    }
}
