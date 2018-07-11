<?php declare(strict_types = 1);

namespace App\Policies;

use App\Models\ShortUrl\ShortUrl;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;

/**
 * Class ShortUrlPolicy
 */
class ShortUrlPolicy
{
    use HandlesAuthorization;

    /**
     * @param \App\Models\User $user
     * @param mixed            $ability
     *
     * @return bool
     */
    public function before($user, $ability)
    {
        if (Auth::guard('admin')->check()) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the shortUrl.
     *
     * @param  \App\Models\User              $user
     * @param  \App\Models\ShortUrl\ShortUrl $shortUrl
     *
     * @return mixed
     */
    public function view(User $user, ShortUrl $shortUrl)
    {
        return $this->idsMatch($user, $shortUrl);
    }

    /**
     * Determine whether the user can create shortUrls.
     *
     * @param  \App\Models\User $user
     *
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->state !== User::STATE_BLOCKED;
    }

    /**
     * Determine whether the user can update the shortUrl.
     *
     * @param  \App\Models\User              $user
     * @param  \App\Models\ShortUrl\ShortUrl $shortUrl
     *
     * @return mixed
     */
    public function update(User $user, ShortUrl $shortUrl)
    {
        return $this->idsMatch($user, $shortUrl);
    }

    /**
     * Determine whether the user can delete the shortUrl.
     *
     * @param  \App\Models\User              $user
     * @param  \App\Models\ShortUrl\ShortUrl $shortUrl
     *
     * @return mixed
     */
    public function delete(User $user, ShortUrl $shortUrl)
    {
        return $this->idsMatch($user, $shortUrl);
    }

    /**
     * @param \App\Models\User              $user
     * @param \App\Models\ShortUrl\ShortUrl $shortUrl
     *
     * @return bool
     */
    private function idsMatch(User $user, ShortUrl $shortUrl): bool
    {
        return $user->id === (int) $shortUrl->user_id;
    }
}
