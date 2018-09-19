<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Web\Admin\Rsi\CommLink\Category;

use App\Models\Account\Admin\Admin;
use App\Models\Account\Admin\AdminGroup;
use Tests\Feature\Controller\Web\Admin\Rsi\CommLink\Channel\ChannelControllerTestCase;

/**
 * Class Category Controller Test
 *
 * @covers \App\Policies\Web\Admin\Rsi\CommLink\CommLinkPolicy<extended>
 *
 * @covers \App\Http\Middleware\Web\Admin\RedirectIfNotAdmin
 * @covers \App\Http\Middleware\Web\Admin\RedirectIfAdmin
 * @covers \App\Http\Middleware\CheckUserState
 *
 * @covers \App\Providers\RouteServiceProvider
 *
 * @covers \App\Models\Rsi\CommLink\Channel\Channel
 */
class ChannelControllerEditorTest extends ChannelControllerTestCase
{
    protected const RESPONSE_STATUSES = [
        'index' => \Illuminate\Http\Response::HTTP_OK,

        'show' => \Illuminate\Http\Response::HTTP_OK,
    ];

    /**
     * {@inheritdoc}
     * Adds the specific group to the Admin model
     */
    protected function setUp()
    {
        parent::setUp();
        $this->admin = factory(Admin::class)->create();
        $this->admin->groups()->sync(AdminGroup::where('name', 'editor')->first()->id);
    }
}
