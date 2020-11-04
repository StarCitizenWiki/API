<?php declare(strict_types = 1);

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
    protected function setUp(): void
    {
        parent::setUp();
        $this->createUserGroups();
        $this->user = User::factory()->create();
    }
}
