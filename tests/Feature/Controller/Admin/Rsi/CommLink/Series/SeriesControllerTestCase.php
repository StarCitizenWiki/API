<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 07.08.2018
 * Time: 11:52
 */

namespace Tests\Feature\Controller\Admin\Rsi\CommLink\Series;

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
     * {@inheritdoc}
     * Creates needed Comm Link Series
     */
    protected function setUp()
    {
        parent::setUp();

        $this->series = factory(Series::class)->create();
    }
}
