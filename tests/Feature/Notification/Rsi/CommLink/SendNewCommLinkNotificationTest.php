<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 13.09.2018
 * Time: 18:20
 */

namespace Tests\Feature\Notification\Rsi\CommLink;

use App\Events\Rsi\CommLink\NewCommLinksDownloaded as NewCommLinksDownloadedEvent;
use App\Models\Account\Admin\Admin;
use App\Models\Account\Admin\AdminGroup;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\CommLinksChanged as CommLinksChangedModel;
use App\Notifications\Rsi\CommLink\NewCommLinksDownloaded as NewCommLinksDownloadedNotification;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Test Notification for newly downloaded Comm Links
 */
class SendNewCommLinkNotificationTest extends TestCase
{
    private $admins;
    private $editors;
    private $commLinks;

    /**
     * @covers \App\Events\Rsi\CommLink\NewCommLinksDownloaded
     * @covers \App\Listeners\Rsi\CommLink\SendNewCommLinksDownloadedNotification
     * @covers \App\Notifications\Rsi\CommLink\NewCommLinksDownloaded
     * @covers \App\Mail\Rsi\CommLink\NewCommLinksDownloaded
     * @covers \App\Models\Account\Admin\Admin
     */
    public function testNotificationSendToAdmins()
    {
        Notification::fake();

        event(new NewCommLinksDownloadedEvent());

        Notification::assertSentTo([$this->admins], NewCommLinksDownloadedNotification::class);
    }

    /**
     * @covers \App\Events\Rsi\CommLink\NewCommLinksDownloaded
     * @covers \App\Listeners\Rsi\CommLink\SendNewCommLinksDownloadedNotification
     * @covers \App\Notifications\Rsi\CommLink\NewCommLinksDownloaded
     * @covers \App\Mail\Rsi\CommLink\NewCommLinksDownloaded
     * @covers \App\Models\Account\Admin\Admin
     */
    public function testNotificationSendToEditors()
    {
        Notification::fake();

        event(new NewCommLinksDownloadedEvent());

        Notification::assertSentTo([$this->editors], NewCommLinksDownloadedNotification::class);
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

        $this->editors = factory(Admin::class, 2)->create()->each(
            function (Admin $admin) {
                $admin->groups()->sync(AdminGroup::where('name', 'editor')->first()->id);
            }
        );

        $this->commLinks = factory(CommLink::class, 5)->create()->each(
            function (CommLink $commLink) {
                CommLinksChangedModel::create(
                    [
                        'comm_link_id' => $commLink->id,
                        'had_content' => rand(0, 1) ? true : false,
                        'type' => 'creation',
                    ]
                );
            }
        );
    }
}