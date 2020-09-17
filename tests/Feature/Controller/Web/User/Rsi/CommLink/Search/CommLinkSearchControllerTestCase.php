<?php declare(strict_types=1);

namespace Tests\Feature\Controller\Web\User\Rsi\CommLink\Search;

use App\Http\Controllers\Web\User\Rsi\CommLink\CommLinkController;
use App\Jobs\Rsi\CommLink\Parser\ParseCommLink;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\CommLinkTranslation;
use App\Models\Rsi\CommLink\Image\Image;
use Dingo\Api\Dispatcher;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Controller\Web\User\UserTestCase;


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

        $this->commLinks = factory(CommLink::class, 20)->create();
        $this->commLinks->each(
            function (CommLink $commLink) {
                $commLink->images()->saveMany(factory(Image::class, 3)->make());
                $commLink->links()->saveMany(factory(Image::class, 3)->make());
            }
        );
    }
}
