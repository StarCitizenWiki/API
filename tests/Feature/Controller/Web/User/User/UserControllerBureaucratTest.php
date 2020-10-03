<?php declare(strict_types=1);

namespace Tests\Feature\Controller\Web\User\User;

use App\Models\Account\User\UserGroup;
use Illuminate\Http\Response;

/**
 * Class UserControllerTest
 *
 * @covers \App\Policies\Web\User\User\UserPolicy<extended>
 *
 * @covers \App\Models\Account\User\User
 *
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

        'update' => Response::HTTP_OK,
        'update_not_found' => Response::HTTP_NOT_FOUND,

        'block' => Response::HTTP_OK,
    ];

    /**
     * {@inheritdoc}
     * Adds the specific group to the Admin model
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->user->groups()->sync([UserGroup::where('name', 'bureaucrat')->first()->id]);
    }
}
