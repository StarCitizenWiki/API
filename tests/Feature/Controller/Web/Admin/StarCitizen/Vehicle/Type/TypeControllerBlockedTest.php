<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 11.08.2018
 * Time: 22:47
 */

namespace Tests\Feature\Controller\Web\Admin\StarCitizen\Vehicle\Type;

use App\Models\Account\Admin\Admin;
use App\Models\Account\Admin\AdminGroup;
use Illuminate\Http\Response;

/**
 * @covers \App\Policies\Web\Admin\TranslationPolicy<extended>
 *
 * @covers \App\Models\Api\StarCitizen\Vehicle\Type\VehicleType<extended>
 */
class TypeControllerBlockedTest extends TypeControllerTestCase
{
    /**
     * {@inheritdoc}
     */
    protected const RESPONSE_STATUSES = [
        'index' => Response::HTTP_FORBIDDEN,

        'edit' => Response::HTTP_FORBIDDEN,
        'edit_not_found' => Response::HTTP_FORBIDDEN,

        'update' => Response::HTTP_FORBIDDEN,
        'update_not_found' => Response::HTTP_FORBIDDEN,
    ];

    /**
     * {@inheritdoc}
     * Adds the specific group to the Admin model
     */
    protected function setUp()
    {
        parent::setUp();
        $this->admin = factory(Admin::class)->state('blocked')->create();
        $this->admin->groups()->sync(AdminGroup::where('name', 'sysop')->first()->id);
    }
}
