<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 11.08.2018
 * Time: 22:47
 */

namespace Tests\Feature\Controller\Web\Admin\StarCitizen\Vehicle\Focus;

use App\Models\Account\Admin\AdminGroup;
use Illuminate\Http\Response;

/**
 * @covers \App\Policies\Web\Admin\StarCitizen\Vehicle\VehiclePolicy<extended>
 *
 * @covers \App\Models\Api\StarCitizen\Vehicle\Ship\Ship<extended>
 */
class FocusControllerBureaucratTest extends FocusControllerTestCase
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
        $this->admin->groups()->sync(AdminGroup::where('name', 'bureaucrat')->first()->id);
    }
}
