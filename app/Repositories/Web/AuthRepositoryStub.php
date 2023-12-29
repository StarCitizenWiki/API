<?php

declare(strict_types=1);

namespace App\Repositories\Web;

use App\Contracts\Web\User\AuthRepositoryInterface;
use App\Models\Account\User\User;
use App\Models\Account\User\UserGroup;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Stub Implementation.
 */
class AuthRepositoryStub implements AuthRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function startAuth()
    {
        return redirect()->route('web.auth.login.callback');
    }

    /**
     * {@inheritdoc}
     */
    public function getUserFromProvider(Request $request): User
    {
        return new User();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrCreateLocalUser(User $oauthUser, string $provider): User
    {
        $user = User::query()->where('username', 'Local Wiki Admin')->first();

        if (null !== $user) {
            return $user;
        }

        /** @var User $user */
        $user = User::query()->create(
            [
                'username' => 'Local Wiki Admin',
                'email' => 'admin@example.com',
                'blocked' => false,
                'provider' => 'starcitizenwiki',
                'provider_id' => 1,
                'last_login' => Carbon::now()->toDateTimeString(),
                'api_token' => Str::random(60),
                'created_at' => Carbon::now()->toDateTimeString(),
                'updated_at' => Carbon::now()->toDateTimeString(),
            ]
        );

        $group = UserGroup::query()->firstOrCreate(
            [
                'name' => 'bureaucrat',
            ],
            [
                'permission_level' => UserGroup::BUREAUCRAT,
            ]
        );

        $user->groups()->sync([$group->id]);

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function syncLocalUserGroups(User $oauthUser, User $user): void
    {
        // Unused Stub
    }
}
