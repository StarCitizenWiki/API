<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Web\StarCitizen\Vehicle\GroundVehicle;

use App\Models\Account\User\UserGroup;
use Illuminate\Http\Response;

/**
 * @covers \App\Policies\Web\StarCitizen\Vehicle\VehiclePolicy<extended>
 *
 * @covers \App\Models\StarCitizen\Vehicle\GroundVehicle\GroundVehicle<extended>
 */
class GroundVehicleControllerSichterTest extends GroundVehicleControllerTestCase
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
    protected function setUp(): void
    {
        parent::setUp();
        $this->user->groups()->sync(UserGroup::where('name', 'sichter')->first()->id);
    }
}
