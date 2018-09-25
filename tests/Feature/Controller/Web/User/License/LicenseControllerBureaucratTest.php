<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Web\User\License;

use App\Models\Account\User\UserGroup;

/**
 * Class AdminControllerTest
 *
 * @covers \App\Policies\Web\User\License\LicensePolicy<extended>
 *
 * @covers \App\Http\Middleware\CheckUserState
 */
class LicenseControllerBureaucratTest extends LicenseControllerTestCase
{
    protected const RESPONSE_STATUSES = [
        'show' => \Illuminate\Http\Response::HTTP_FORBIDDEN,

        'accept' => \Illuminate\Http\Response::HTTP_FORBIDDEN,

        'show_accepted' => \Illuminate\Http\Response::HTTP_FORBIDDEN,
    ];

    /**
     * {@inheritdoc}
     * Adds the specific group to the Admin model
     */
    protected function setUp()
    {
        parent::setUp();
        $this->user->groups()->sync(UserGroup::where('name', 'bureaucrat')->first()->id);
    }
}
