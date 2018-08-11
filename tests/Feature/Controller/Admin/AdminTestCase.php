<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 11.08.2018
 * Time: 22:04
 */

namespace Tests\Feature\Controller\Admin;

use App\Models\Account\Admin\Admin;
use Tests\TestCase;

/**
 * Admin Test Case
 */
class AdminTestCase extends TestCase
{
    /**
     * Response Status Array for all Methods
     */
    protected const RESPONSE_STATUSES = [];

    /**
     * Model ID for models that do not exist
     */
    protected const MODEL_ID_NOT_EXISTENT = 999999;

    /**
     * @var \App\Models\Account\Admin\Admin
     */
    protected $admin;

    /**
     * Sets up all needed Admin Groups and creates a Admin Model
     */
    protected function setUp()
    {
        parent::setUp();
        $this->createAdminGroups();
        $this->admin = factory(Admin::class)->create();
    }
}
