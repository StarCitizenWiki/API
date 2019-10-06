<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 09.08.2018
 * Time: 11:00.
 */

namespace App\Repositories\Web\User;

use App\Contracts\Web\User\AuthRepositoryInterface;
use App\Models\Account\User\User;
use App\Models\Account\User\UserGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use MediaWiki\OAuthClient\Client;
use MediaWiki\OAuthClient\ClientConfig;
use MediaWiki\OAuthClient\Consumer;
use MediaWiki\OAuthClient\Exception as OAuthException;

/**
 * Mediawiki Bridge.
 */
class AuthRepository implements AuthRepositoryInterface
{
    /**
     * @var \MediaWiki\OAuthClient\Client
     */
    private $client;

    /**
     * Creates the OAuth Client.
     */
    public function __construct()
    {
        $conf = new ClientConfig(sprintf('%s/%s', config('services.mediawiki.url'), 'index.php?title=Special:OAuth'));
        $conf->setConsumer(new Consumer(config('services.mediawiki.client_id'), config('services.mediawiki.client_secret')));

        $this->client = new Client($conf);
    }

    /**
     * {@inheritdoc}
     */
    public function startAuth()
    {
        try {
            [$authUrl, $requestToken] = $this->client->initiate();
        } catch (OAuthException $e) {
            Log::error('Error in OAuth init request', $e->getMessage());

            return view('errors.503');
        }

        Session::put('oauth.req_token', $requestToken);

        return redirect($authUrl);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserFromProvider(Request $request): User
    {
        $ver = $request->get('oauth_verifier');

        try {
            $accessToken = $this->client->complete(Session::get('oauth.req_token'), $ver);
        } catch (OAuthException $e) {
            Log::error('Error in retrieving OAuth User', $e->getMessage());

            return abort(500);
        }

        try {
            $ident = $this->client->identify($accessToken);
        } catch (OAuthException $e) {
            Log::error('Error in completing OAuth User request', $e->getMessage());

            return abort(500);
        }

        return $this->userDetails($ident);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrCreateLocalUser(User $oauthUser, string $provider): User
    {
        /** @var \App\Models\Account\User\User $authUser */
        $authUser = User::query()->where('provider_id', $oauthUser->id)->where('provider', $provider)->first();

        if ($authUser) {
            $this->syncLocalUserGroups($oauthUser, $authUser);

            if ($authUser->email !== $oauthUser->email) {
                $authUser->update(
                    [
                        'email' => $oauthUser->email,
                    ]
                );
            }

            return $authUser;
        }

        return $this->createLocalUser($oauthUser, $provider);
    }

    /**
     * {@inheritdoc}
     */
    public function syncLocalUserGroups(User $oauthUser, User $user): void
    {
        $groups = $oauthUser->extra['groups'] ?? null;

        if (is_array($groups)) {
            $groupIDs = UserGroup::select('id')->whereIn('name', $groups)->get();

            $user->groups()->sync($groupIDs);
        }
    }

    /**
     * Creates the local User Record.
     *
     * @param \App\Models\Account\User\User $oauthUser
     * @param string                        $provider
     *
     * @return \App\Models\Account\User\User
     */
    private function createLocalUser(User $oauthUser, string $provider): User
    {
        /** @var \App\Models\Account\User\User $localUser */
        $localUser = User::create(
            [
                'username' => $oauthUser->username,
                'email' => $oauthUser->email,
                'blocked' => $oauthUser->blocked,
                'provider_id' => $oauthUser->id,
                'provider' => $provider,
                'api_token' => Str::random(60),
            ]
        );

        $this->syncLocalUserGroups($oauthUser, $localUser);

        return $localUser;
    }

    /**
     * @param \stdClass $data OAuth user data
     *
     * @return User
     */
    private function userDetails($data): User
    {
        $user = new User();
        $user->id = $data->sub;
        $user->email = optional($data)->email;
        $user->username = $data->username;
        $user->blocked = $data->blocked;

        $user->extra = [
            'groups' => $data->groups,
            'rights' => $data->rights,
            'grants' => optional($data)->grants,
            'editcount' => $data->editcount,
        ];

        return $user;
    }
}
