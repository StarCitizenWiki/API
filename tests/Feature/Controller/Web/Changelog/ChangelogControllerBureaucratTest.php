<?php declare(strict_types=1);

namespace Tests\Feature\Controller\Web\Changelog;

use App\Models\Account\User\UserGroup;

/**
 * Class ChangelogControllerSysopTest
 */
class ChangelogControllerBureaucratTest extends ChangelogTestCase
{
    protected const RESPONSE_STATUSES = [
        'index' => \Illuminate\Http\Response::HTTP_OK,
    ];

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->user->groups()->sync(UserGroup::where('name', 'bureaucrat')->first()->id);
    }
}
