<?php

declare(strict_types=1);

namespace Tests\Feature\Controller\Web\Rsi\CommLink\Image;

use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Image\Image;
use App\Models\Rsi\CommLink\Link\Link;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Tests\Feature\Controller\Web\UserTestCase;

/**
 * Class Images Controller Test Case
 */
class ImageControllerTestCase extends UserTestCase
{
    /**
     * @var Collection
     */
    protected $commLinks;

    /**
     * @covers \App\Http\Controllers\Web\Rsi\CommLink\Image\ImageController::index
     */
    public function testIndex(): void
    {
        $response = $this->actingAs($this->user)->get(route('web.rsi.comm-links.images.index'));

        $response->assertStatus(static::RESPONSE_STATUSES['index']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('web.rsi.comm_links.images.index');
        }
    }

    /**
     * {@inheritdoc}
     * Creates needed Comm-Links and Images
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->commLinks = CommLink::factory()->count(20)->create();
        $this->commLinks->each(
            function (CommLink $commLink) {
                $commLink->images()->saveMany(Image::factory()->count(3)->make());
                $commLink->links()->saveMany(Link::factory()->count(3)->make());
            }
        );
    }
}
