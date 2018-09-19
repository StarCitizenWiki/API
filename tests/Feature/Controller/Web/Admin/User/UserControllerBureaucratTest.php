<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Web\Admin\User;

use App\Models\Account\Admin\AdminGroup;
use Illuminate\Http\Response;

/**
 * Class UserControllerTest
 *
 * @covers \App\Policies\Web\Admin\User\UserPolicy<extended>
 *
 * @covers \App\Models\Account\User\User
 *
 * @covers \App\Http\Middleware\Web\Admin\RedirectIfNotAdmin
 * @covers \App\Http\Middleware\Web\Admin\RedirectIfAdmin
 * @covers \App\Http\Middleware\CheckUserState
 *
 * @covers \App\Providers\RouteServiceProvider
 */
class UserControllerBureaucratTest extends UserControllerTestCase
{
    protected const RESPONSE_STATUSES = [
        'index' => Response::HTTP_OK,

        'edit' => Response::HTTP_OK,
        'edit_not_found' => Response::HTTP_NOT_FOUND,

        'update' => Response::HTTP_FOUND,
        'update_not_found' => Response::HTTP_NOT_FOUND,

        'delete' => Response::HTTP_FOUND,
        'delete_not_found' => Response::HTTP_NOT_FOUND,
    ];

    /**
     * {@inheritdoc}
     * Adds the specific group to the Admin model
     */
    protected function setUp()
    {
        parent::setUp();
        $this->admin->groups()->sync([AdminGroup::where('name', 'bureaucrat')->first()->id]);
    }
}
