<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ModelUpdating as ModelUpdateEvent;
use App\Models\StarCitizenUnpacked\Shop\ShopItem;
use App\Models\System\ModelChangelog;
use App\Models\System\Translation\AbstractTranslation as Translation;
use Illuminate\Support\Facades\Auth;

/**
 * Listener that processes Model Changes and writes them to the DB
 */
class ModelUpdating
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    private $model;

    /**
     * Handle the event.
     *
     * @param ModelUpdateEvent $event
     *
     * @return void
     */
    public function handle(ModelUpdateEvent $event): void
    {
        $this->model = $event->model;

        // TODO Hacky
        $createdAt = now();
        if ($this->model instanceof Translation) {
            $createdAt = now()->addSecond();
        }

        $data = $this->getChangelogData();

        if ($data === null) {
            return;
        }

        $data = [
            'type' => $this->getChangelogType(),
            'changelog' => $data,
            'user_id' => $this->getCreatorId(),
            'created_at' => $createdAt,
        ];

        if ($this->model instanceof ShopItem && $this->model->pivotParent !== null) {
            ModelChangelog::create([
                'type' => $data['type'],
                'changelog' => [
                        'item_uuid' => $this->model->item_uuid,
                        'shop_uuid' => $this->model->shop_uuid,
                    ] + ($data['changelog'] ?? []),
                'changelog_type' => get_class($this->model),
                'user_id' => 0,
                'changelog_id' => $this->model->item_id,
            ]);

            return;
        }

        $this->model->changelogs()->create($data);
    }

    /**
     * Creates the Changelog Array
     *
     * @return array
     */
    private function getChangelogData(): ?array
    {
        $changelog = [];

        $changes = $this->getChanges();

        if (!empty($changes)) {
            $changelog['changes'] = $this->getChanges();
        }

        if ($this->model instanceof Translation) {
            $changelog['extra'] = [
                'locale' => $this->model->locale_code,
            ];
        }

        return empty($changelog) ? null : $changelog;
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
        /** Don't create changes for Model Creations, since all Old values will be null */
        if ($this->model->wasRecentlyCreated || null !== $this->model->deleted_at) {
            return [];
        }

        return collect($this->model->getDirty())
            ->filter(function ($value, $key) {
                if (!is_numeric($value)) {
                    return true;
                }

                return round((float)$value) !== round((float)$this->model->getOriginal($key));
            })
            ->map(
                function ($value, $key) {
                    return [
                        'value' => $value,
                        'key' => $key,
                        'original' => $this->model->getOriginal($key),
                    ];
                }
            )
            ->mapWithKeys(
                function (array $data) {
                    return [
                        $data['key'] => [
                            'old' => $data['original'],
                            'new' => $data['value'],
                        ],
                    ];
                }
            )
            ->toArray();
    }

    /**
     * Changing Admin ID
     *
     * @return int
     */
    private function getCreatorId(): int
    {
        return (int)(Auth::check() ? Auth::id() : 0);
    }
}
