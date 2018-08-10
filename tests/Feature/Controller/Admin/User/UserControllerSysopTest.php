<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Admin\User;

use App\Models\Account\Admin\Admin;
use App\Models\Account\Admin\AdminGroup;
use Illuminate\Http\Response;

/**
 * Class UserControllerTest
 *
 * @covers \App\Policies\Web\Admin\User\UserPolicy
 * @covers \App\Http\Middleware\Web\Admin\RedirectIfNotAdmin
 * @covers \App\Http\Middleware\Web\Admin\RedirectIfAdmin
 * @covers \App\Http\Middleware\CheckUserState
 */
class UserControllerSysopTest extends AbstractBaseUserControllerTestCase
{
    protected const RESPONSE_STATUSES = [
        'index' => Response::HTTP_OK,

        'edit' => Response::HTTP_OK,
        'edit_not_found' => Response::HTTP_BAD_REQUEST,

        'update' => Response::HTTP_FOUND,
        'update_not_found' => Response::HTTP_BAD_REQUEST,

        'delete' => Response::HTTP_FOUND,
        'delete_not_found' => Response::HTTP_BAD_REQUEST,
    ];

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->admin = factory(Admin::class)->create();
        $this->admin->groups()->sync([AdminGroup::where('name', 'sysop')->first()->id]);
    }
}
