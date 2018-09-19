<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 13.09.2018
 * Time: 18:20
 */

namespace Tests\Feature\Notification\Rsi\CommLink;

use App\Events\Rsi\CommLink\CommLinksChanged as CommLinksChangedEvent;
use App\Models\Account\Admin\Admin;
use App\Models\Account\Admin\AdminGroup;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\CommLinksChanged as CommLinksChangedModel;
use App\Notifications\Rsi\CommLink\CommLinksChanged as CommLinksChangedNotification;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Test Notification for changed Comm Links
 */
class CommLinkChangedTest extends TestCase
{
    private $admins;
    private $commLinks;

    /**
     * @covers \App\Events\Rsi\CommLink\CommLinksChanged
     * @covers \App\Listeners\Rsi\CommLink\SendCommLinksChangedNotification
     * @covers \App\Notifications\Rsi\CommLink\CommLinksChanged
     * @covers \App\Mail\Rsi\CommLink\CommLinksChanged
     * @covers \App\Models\Account\Admin\Admin
     */
    public function testNotificationSendToAdmins()
    {
        Notification::fake();

        event(new CommLinksChangedEvent());

        Notification::assertSentTo([$this->admins], CommLinksChangedNotification::class);
    }

    /**
     * Creates Admin Groups, Editors, Sysops, CommLinks
     */
    protected function setUp()
    {
        parent::setUp();
        $this->createAdminGroups();

        $this->admins = factory(Admin::class, 2)->create()->each(
            function (Admin $admin) {
                $admin->groups()->sync(AdminGroup::where('name', 'sysop')->first()->id);
            }
        );

        $this->commLinks = factory(CommLink::class, 5)->create()->each(
            function (CommLink $commLink) {
                CommLinksChangedModel::create(
                    [
                        'comm_link_id' => $commLink->id,
                        'had_content' => rand(0, 1) ? true : false,
                        'type' => 'update',
                    ]
                );
            }
        );
    }
}
