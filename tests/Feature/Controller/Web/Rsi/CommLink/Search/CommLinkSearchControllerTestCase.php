<?php declare(strict_types=1);

namespace Tests\Feature\Controller\Web\Rsi\CommLink\Search;

use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Image\Image;
use App\Models\Rsi\CommLink\Link\Link;
use Illuminate\Database\Eloquent\Collection;
use Tests\Feature\Controller\Web\UserTestCase;


/**
 * Class Comm-Link Controller Test Case.
 */
class CommLinkSearchControllerTestCase extends UserTestCase
{
    /**
     * @var Collection
     */
    protected $commLinks;

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
