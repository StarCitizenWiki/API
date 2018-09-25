<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 11.08.2018
 * Time: 22:47
 */

namespace Tests\Feature\Controller\Web\User\StarCitizen\Vehicle\Ship;

use App\Models\Account\User\UserGroup;
use Illuminate\Http\Response;

/**
 * @covers \App\Policies\Web\User\StarCitizen\Vehicle\VehiclePolicy<extended>
 *
 * @covers \App\Models\Api\StarCitizen\Vehicle\Ship\Ship<extended>
 */
class ShipControllerSysopTest extends ShipControllerTestCase
{
    /**
     * {@inheritdoc}
     */
    protected const RESPONSE_STATUSES = [
        'index' => Response::HTTP_OK,

        'edit' => Response::HTTP_OK,
        'edit_not_found' => Response::HTTP_NOT_FOUND,

        'update' => Response::HTTP_FOUND,
        'update_not_found' => Response::HTTP_NOT_FOUND,
    ];

    /**
     * {@inheritdoc}
     * Adds the specific group to the Admin model
     */
    protected function setUp()
    {
        parent::setUp();
        $this->user->groups()->sync(UserGroup::where('name', 'sysop')->first()->id);
    }
}