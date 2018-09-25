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
class LicenseControllerEditorTest extends LicenseControllerTestCase
{
    protected const RESPONSE_STATUSES = [
        'show' => \Illuminate\Http\Response::HTTP_OK,

        'accept' => \Illuminate\Http\Response::HTTP_OK,

        'show_accepted' => \Illuminate\Http\Response::HTTP_OK,
    ];

    /**
     * {@inheritdoc}
     * Adds the specific group to the Admin model
     */
    protected function setUp()
    {
        parent::setUp();
        $this->user->groups()->sync(UserGroup::where('name', 'editor')->first()->id);
    }
}
