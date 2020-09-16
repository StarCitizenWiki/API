<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Web\User\Changelog;

use App\Models\Account\User\UserGroup;

/**
 * Class ChangelogControllerSysopTest
 */
class ChangelogControllerUserTest extends ChangelogTestCase
{
    protected const RESPONSE_STATUSES = [
        'index' => \Illuminate\Http\Response::HTTP_FORBIDDEN,
    ];

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->user->groups()->sync(UserGroup::where('name', 'user')->first()->id);
    }
}
