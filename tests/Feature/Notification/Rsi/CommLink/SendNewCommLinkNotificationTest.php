<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 13.09.2018
 * Time: 18:20
 */

namespace Tests\Feature\Notification\Rsi\CommLink;

use App\Events\Rsi\CommLink\NewCommLinksDownloaded as NewCommLinksDownloadedEvent;
use App\Models\Account\User\User;
use App\Models\Account\User\UserGroup;
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
    private $commLinks;

    /**
     * @covers \App\Events\Rsi\CommLink\NewCommLinksDownloaded
     * @covers \App\Listeners\Rsi\CommLink\SendNewCommLinksDownloadedNotification
     * @covers \App\Notifications\Rsi\CommLink\NewCommLinksDownloaded
     * @covers \App\Mail\Rsi\CommLink\NewCommLinksDownloaded
     * @covers \App\Models\Account\User\User
     */
    public function testNotificationSendToAdmins()
    {
        Notification::fake();

        event(new NewCommLinksDownloadedEvent());

        Notification::assertSentTo([$this->admins], NewCommLinksDownloadedNotification::class);
    }

    /**
     * Creates Admin Groups, Sysops, CommLinks
     */
    protected function setUp()
    {
        parent::setUp();
        $this->createUserGroups();

        $this->admins = factory(User::class, 2)->create()->each(
            function (User $user) {
                $user->groups()->sync(UserGroup::where('name', 'sysop')->first()->id);
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
