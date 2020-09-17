<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Web\User\Rsi\CommLink\Image;

use App\Http\Controllers\Web\User\Rsi\CommLink\Series\SeriesController;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\CommLinkTranslation;
use App\Models\Rsi\CommLink\Image\Image;
use App\Models\Rsi\CommLink\Series\Series;
use Dingo\Api\Dispatcher;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\Collection;
use Tests\Feature\Controller\Web\User\UserTestCase;

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
     * @covers \App\Http\Controllers\Web\User\Rsi\CommLink\Image\ImageController::index
     */
    public function testIndex(): void
    {
        $response = $this->actingAs($this->user)->get(route('web.user.rsi.comm-links.images.index'));

        $response->assertStatus(static::RESPONSE_STATUSES['index']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('user.rsi.comm_links.images.index');
        }
    }

    /**
     * {@inheritdoc}
     * Creates needed Comm-Links and Images
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->commLinks = factory(CommLink::class, 20)->create();
        $this->commLinks->each(
            function (CommLink $commLink) {
                $commLink->images()->saveMany(factory(Image::class, 3)->make());
                $commLink->links()->saveMany(factory(Image::class, 3)->make());
            }
        );
    }
}
