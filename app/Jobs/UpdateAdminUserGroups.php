<?php declare(strict_types = 1);

namespace App\Jobs;

use App\Models\Admin\Admin;
use App\Models\Admin\Group;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class UpdateAdminUserGroups
 * @package App\Jobs
 */
class UpdateAdminUserGroups implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app('Log')::info(__CLASS__." Starting Job");

        $groups = Group::all()->keyBy('name')->all();

        $this->initClient();
        $users = Admin::with('groups')->take(50)->get()->keyBy('username');

        $names = array_keys($users->toArray());

        $names = rtrim(implode('%7C', $names), '%7C');

        $response = $this->client->get('?action=query&format=json&prop=&list=users&usprop=groups&ususers='.$names);

        $wikiData = json_decode((string) $response->getBody()->getContents(), true);

        if (isset($wikiData['query']['users'])) {
            foreach ($wikiData['query']['users'] as $user) {
                if (!isset($users[$user['name']])) {
                    app('Log')::error(__CLASS__." User {$user['name']} not found in DB");

                    return;
                }

                /** @var \App\Models\Admin\Admin $currentUser */
                $currentUser = $users[$user['name']];

                $user['groups'] = array_flip($user['groups']);
                $validGroups = array_intersect_key($groups, $user['groups']);

                $groupIds = [];
                foreach ($validGroups as $validGroup) {
                    $groupIds[] = $validGroup->id;
                }

                $currentUser->groups()->sync($groupIds);

                app('Log')::debug(__CLASS__."Synced Groups [".print_r($validGroups, true)."] to User {$user['name']}");
            }
            app('Log')::info(__CLASS__." Job Finished");
        } else {
            app('Log')::error(__CLASS__.' Wiki Query returned invalid Data');
        }
    }

    /**
     * Inits the Guzzle Client
     */
    private function initClient()
    {
        $this->client = new Client(
            [
                'base_uri' => config('api.wiki_url'),
                'timeout'  => 10,
            ]
        );
    }
}
