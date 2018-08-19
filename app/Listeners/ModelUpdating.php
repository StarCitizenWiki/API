<?php declare(strict_types = 1);

namespace App\Listeners;

use App\Events\ModelUpdating as ModelUpdateEvent;
use Illuminate\Support\Facades\Auth;

/**
 * Listener that processes Model Changes and writes them to the DB
 */
class ModelUpdating
{
    private const ADMIN_GUARD = 'admin';

    /**
     * Handle the event.
     *
     * @param \App\Events\ModelUpdating $event
     *
     * @return void
     */
    public function handle(ModelUpdateEvent $event)
    {
        /** @var \Illuminate\Database\Eloquent\Model $item */
        $item = $event->model;
        $changes = [];
        foreach ($item->getDirty() as $key => $value) {
            $original = $item->getOriginal($key);
            $changes[$key] = [
                'old' => $original,
                'new' => (string) $value,
            ];
        }

        if (Auth::guard(self::ADMIN_GUARD)->check()) {
            $changes['by'] = [
                'id' => Auth::guard(self::ADMIN_GUARD)->user()->provider_id,
                'name' => Auth::guard(self::ADMIN_GUARD)->user()->username,
            ];
        }

        $item->changelogs()->create(
            [
                'changelog' => json_encode($changes),
            ]
        );

        app('Log')::debug('Updated '.($item->getTable()), $changes);
    }
}
