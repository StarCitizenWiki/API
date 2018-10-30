<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 11.08.2018
 * Time: 22:04
 */

namespace Tests\Feature\Controller\Web\User;

use App\Models\Account\User\User;
use Tests\TestCase;

/**
 * Admin Test Case
 */
class UserTestCase extends TestCase
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
     * @var \App\Models\Account\User\User
     */
    protected $user;

    /**
     * Sets up all needed Admin Groups and creates a Admin Model
     */
    protected function setUp()
    {
        parent::setUp();
        $this->createUserGroups();
        $this->user = factory(User::class)->create();
    }
}
