<?php declare(strict_types = 1);

namespace App\Listeners;

use App\Events\ModelUpdating as ModelUpdateEvent;
use App\Models\System\Translation\AbstractTranslation as Translation;
use Illuminate\Support\Facades\Auth;

/**
 * Listener that processes Model Changes and writes them to the DB
 */
class ModelUpdating
{
    private const ADMIN_GUARD = 'admin';

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    private $model;

    /**
     * Handle the event.
     *
     * @param \App\Events\ModelUpdating $event
     *
     * @return void
     */
    public function handle(ModelUpdateEvent $event)
    {
        $this->model = $event->model;

        $this->model->changelogs()->create(
            [
                'type' => $this->getChangelogType(),
                'changelog' => $this->getChangelogData(),
                'admin_id' => $this->getCreatorId(),
            ]
        );
    }

    /**
     * Creates the Changelog Array
     *
     * @return array
     */
    private function getChangelogData(): array
    {
        $changelog = [
            'changes' => $this->getChanges(),
            'extra' => [],
        ];

        if ($this->model instanceof Translation) {
            $changelog['extra'] = [
                'locale' => $this->model->locale_code,
            ];
        }

        return $changelog;
    }

    /**
     * Returns the Changelog Type of this Model
     *
     * @return string
     */
    private function getChangelogType(): string
    {
        if ($this->model->wasRecentlyCreated) {
            $type = 'creation';
        } elseif (null !== $this->model->deleted_at) {
            $type = 'deletion';
        } else {
            $type = 'update';
        }

        return $type;
    }

    /**
     * @return array
     */
    private function getChanges(): array
    {
        $changes = [];

        /** Don't create changes for Model Creations, since all Old values will be null */
        if (!$this->model->wasRecentlyCreated) {
            foreach ($this->model->getDirty() as $key => $value) {
                $original = $this->model->getOriginal($key);
                $changes[$key] = [
                    'old' => $original,
                    'new' => (string) $value,
                ];
            }
        }

        return $changes;
    }

    /**
     * Changing Admin ID
     *
     * @return int
     */
    private function getCreatorId(): int
    {
        $id = 0;

        if (Auth::guard(self::ADMIN_GUARD)->check()) {
            $id = Auth::guard(self::ADMIN_GUARD)->user()->provider_id;
        }

        return (int)$id;
    }
}
