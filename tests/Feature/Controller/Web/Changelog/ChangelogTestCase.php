<?php declare(strict_types=1);

namespace Tests\Feature\Controller\Web\Changelog;

use App\Http\Controllers\Web\Changelog\ChangelogController;
use Illuminate\Http\Response;
use Tests\Feature\Controller\Web\UserTestCase;

/**
 * Class ChangelogTestCase
 */
class ChangelogTestCase extends UserTestCase
{
    /**
     * @covers \App\Http\Controllers\Web\Changelog\ChangelogController::index
     *
     * @covers \App\Policies\Web\Changelog\ChangelogPolicy
     */
    public function testIndex()
    {
        $response = $this->actingAs($this->user)->get(route('web.changelogs.index'));

        $response->assertStatus(static::RESPONSE_STATUSES['index']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertSee(__('Änderungsübersicht'));
        }
    }
}
