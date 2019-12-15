<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 30.09.2018
 * Time: 18:25
 */

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
