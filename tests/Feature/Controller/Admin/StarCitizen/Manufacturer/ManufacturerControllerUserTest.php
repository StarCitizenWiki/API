<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 11.08.2018
 * Time: 22:47
 */

namespace Tests\Feature\Controller\Admin\StarCitizen\Manufacturer;

use App\Models\Account\Admin\Admin;
use App\Models\Account\Admin\AdminGroup;
use Illuminate\Http\Response;

/**
 * @covers \App\Policies\Web\Admin\StarCitizen\Manufacturer\ManufacturerPolicy<extended>
 *
 * @covers \App\Models\Api\StarCitizen\Manufacturer\Manufacturer
 */
class ManufacturerControllerUserTest extends ManufacturerControllerTestCase
{
    /**
     * {@inheritdoc}
     */
    protected const RESPONSE_STATUSES = [
        'index' => Response::HTTP_OK,

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
        $this->admin->groups()->sync(AdminGroup::where('name', 'user')->first()->id);
    }
}