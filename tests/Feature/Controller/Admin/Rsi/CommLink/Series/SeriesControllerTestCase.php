<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 07.08.2018
 * Time: 11:52
 */

namespace Tests\Feature\Controller\Admin\Rsi\CommLink\Series;

use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\CommLinkTranslation;
use App\Models\Rsi\CommLink\Series\Series;
use Dingo\Api\Http\Response;
use Tests\Feature\Controller\Admin\AdminTestCase;

/**
 * Class Series Controller Test Case
 */
class SeriesControllerTestCase extends AdminTestCase
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
     * @covers \App\Http\Controllers\Web\Admin\Rsi\CommLink\Series\SeriesController::index
     */
    public function testIndex()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('web.admin.rsi.comm_links.series.index'));

        $response->assertStatus(static::RESPONSE_STATUSES['index']);
        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('admin.rsi.comm_links.series.index')->assertSee($this->series->name);
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\Rsi\CommLink\Series\SeriesController::show
     * @covers \App\Models\Rsi\CommLink\Series\Series
     */
    public function testShow()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(
            route('web.admin.rsi.comm_links.series.show', $this->series)
        );

        $response->assertStatus(static::RESPONSE_STATUSES['show']);
        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('admin.rsi.comm_links.index')->assertSee(
                $this->commLinks->first()->title
            );
        }
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
