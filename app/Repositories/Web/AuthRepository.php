<?php

declare(strict_types=1);

namespace App\Repositories\Web;

use App\Contracts\Web\User\AuthRepositoryInterface;
use App\Models\Account\User\User;
use App\Models\Account\User\UserGroup;
use Illuminate\Http\Request;
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
     * @var Client
     */
    private Client $client;

    /**
     * Creates the OAuth Client.
     */
    public function __construct()
    {
        $conf = new ClientConfig(
            sprintf(
                '%s/%s',
                config('services.mediawiki.url'),
                'index.php?title=Special:OAuth'
            )
        );

        $conf->setConsumer(
            new Consumer(
                config('services.mediawiki.client_id'),
                config('services.mediawiki.client_secret')
            )
        );

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
            app('Log')::error(sprintf('Error in OAuth init request: %s', $e->getMessage()));

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
        $token = Session::get('oauth.req_token');

        try {
            $accessToken = $this->client->complete($token, $ver);
        } catch (OAuthException $e) {
            app('Log')::error(sprintf('Error in retrieving OAuth User: %s', $e->getMessage()));

            abort(500);
        }

        Session::remove('oauth.req_token');
        Session::put(config('mediawiki.driver.session.token', ''), $accessToken->key);
        Session::put(config('mediawiki.driver.session.secret', ''), $accessToken->secret);

        try {
            $ident = $this->client->identify($accessToken);
        } catch (OAuthException $e) {
            app('Log')::error(sprintf('Error in completing OAuth User request: %s', $e->getMessage()));

            abort(500);
        }

        return $this->userDetails($ident);
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

    /**
     * {@inheritdoc}
     */
    public function getOrCreateLocalUser(User $oauthUser, string $provider): User
    {
        /** @var User $authUser */
        $authUser = User::query()
            ->where('provider_id', $oauthUser->id)
            ->where('provider', $provider)
            ->first();

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
            $groupIDs = UserGroup::query()
                ->select('id')
                ->whereIn('name', $groups)
                ->get();

            $user->groups()->sync($groupIDs);
        }
    }

    /**
     * Creates the local User Record.
     *
     * @param User   $oauthUser
     * @param string $provider
     *
     * @return User
     */
    private function createLocalUser(User $oauthUser, string $provider): User
    {
        /** @var User $localUser */
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
}
