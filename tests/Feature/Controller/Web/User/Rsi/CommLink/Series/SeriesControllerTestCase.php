<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 07.08.2018
 * Time: 11:52
 */

namespace Tests\Feature\Controller\Web\User\Rsi\CommLink\Series;

use App\Http\Controllers\Web\User\Rsi\CommLink\Series\SeriesController;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\CommLinkTranslation;
use App\Models\Rsi\CommLink\Series\Series;
use Dingo\Api\Http\Response;
use Tests\Feature\Controller\Web\User\UserTestCase;

/**
 * Class Series Controller Test Case
 */
class SeriesControllerTestCase extends UserTestCase
{
    /**
     * @var \App\Models\Rsi\CommLink\Series\Series
     */
    protected $series;

    /**
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $commLinks;

    /**
     * @covers \App\Http\Controllers\Web\User\Rsi\CommLink\Series\SeriesController::index
     */
    public function testIndex()
    {
        $response = $this->actingAs($this->user)->get(route('web.user.rsi.comm-links.series.index'));

        $response->assertStatus(static::RESPONSE_STATUSES['index']);
        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('user.rsi.comm_links.series.index')->assertSee($this->series->name);
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\User\Rsi\CommLink\Series\SeriesController::show
     * @covers \App\Models\Rsi\CommLink\Series\Series
     */
    public function testShow()
    {
        $response = $this->actingAs($this->user)->get(
            route('web.user.rsi.comm-links.series.show', $this->series)
        );

        $response->assertStatus(static::RESPONSE_STATUSES['show']);
        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('user.rsi.comm_links.index')->assertSee(
                $this->commLinks->first()->title
            );
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\User\Rsi\CommLink\Series\SeriesController
     */
    public function testConstructor()
    {
        $controller = $this->getMockBuilder(SeriesController::class)->disableOriginalConstructor()->getMock();
        $controller->expects($this->once())->method('middleware')->with('auth');

        $reflectedClass = new \ReflectionClass(SeriesController::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($controller);
    }

    /**
     * {@inheritdoc}
     * Creates needed Comm Link Series
     */
    protected function setUp()
    {
        parent::setUp();
        $this->createSystemLanguages();

        $this->series = factory(Series::class)->create();

        $this->commLinks = factory(CommLink::class, 5)->create(['series_id' => $this->series->id])->each(
            function (CommLink $commLink) {
                $commLink->translations()->save(factory(CommLinkTranslation::class)->make());
            }
        );
    }
}
