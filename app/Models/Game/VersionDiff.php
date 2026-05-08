<?php

declare(strict_types=1);

namespace App\Models\Game;

use App\Support\Game\DeepDiff;
use App\Support\Game\EntityTypeConfig;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;

class VersionDiff extends Model
{
    protected $table = 'game_version_diffs';

    protected $fillable = [
        'from_version_id',
        'to_version_id',
        'entity_type',
        'entity_id',
        'change_type',
        'column_changes',
        'data_changes',
    ];

    protected $casts = [
        'column_changes' => AsCollection::class,
        'data_changes' => AsCollection::class,
    ];

    public function fromVersion(): BelongsTo
    {
        return $this->belongsTo(GameVersion::class, 'from_version_id');
    }

    public function toVersion(): BelongsTo
    {
        return $this->belongsTo(GameVersion::class, 'to_version_id');
    }

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeForVersionPair($query, int $fromVersionId, int $toVersionId): void
    {
        $query->where('from_version_id', $fromVersionId)
            ->where('to_version_id', $toVersionId);
    }

    /**
     * Get change counts grouped by entity type key and change type.
     *
     * @return array<string, array{added: int, removed: int, modified: int}>
     */
    public static function getChangeCounts(int $fromId, int $toId): array
    {
        $raw = static::query()
            ->forVersionPair($fromId, $toId)
            ->selectRaw('entity_type, change_type, count(*) as count')
            ->groupBy('entity_type', 'change_type')
            ->get();

        $counts = [];

        foreach ($raw as $row) {
            $key = EntityTypeConfig::keyForModel($row->entity_type) ?? $row->entity_type;

            if (! isset($counts[$key])) {
                $counts[$key] = ['added' => 0, 'removed' => 0, 'modified' => 0];
            }

            $counts[$key][$row->change_type] = (int) $row->count;
        }

        return $counts;
    }

    public function scopeForEntityType($query, string $type): void
    {
        $query->where('entity_type', EntityTypeConfig::modelClass($type));
    }

    public function scopeItems($query): void
    {
        $query->where('entity_type', Item::class);
    }

    public function scopeVehicles($query): void
    {
        $query->where('entity_type', Vehicle::class);
    }

    public function scopeAdded($query): void
    {
        $query->where('change_type', 'added');
    }

    public function scopeRemoved($query): void
    {
        $query->where('change_type', 'removed');
    }

    public function scopeModified($query): void
    {
        $query->where('change_type', 'modified');
    }

    public function isRegression(): bool
    {
        if ($this->change_type === 'removed') {
            return true;
        }

        if ($this->change_type !== 'modified') {
            return false;
        }

        foreach ($this->data_changes ?? [] as $path => $change) {
            if ($change instanceof Collection) {
                $change = $change->toArray();
            }

            if (! is_array($change)) {
                continue;
            }

            if (array_key_exists('old', $change) && array_key_exists('new', $change) && $change['new'] === null && $change['old'] !== null) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resolve the entity data model for the relevant version.
     * For removed: from_version. For added/modified: to_version.
     */
    public function resolveEntityData(): ?Model
    {
        $entity = $this->entity;

        if ($entity === null) {
            return null;
        }

        $versionId = $this->change_type === 'removed'
            ? $this->from_version_id
            : $this->to_version_id;

        return $entity->data->first(fn (Model $d) => $d->game_version_id === $versionId);
    }

    public function resolveDisplayName(): ?string
    {
        return ($data = $this->resolveEntityData()) !== null
            ? ($data->name ?? $data->title ?? $data->output_name ?? $data->debug_name)
            : null;
    }

    public function resolveClassName(): ?string
    {
        return $this->resolveEntityData()?->class_name;
    }

    /**
     * Build the web URL for this entity using EntityTypeConfig route mapping.
     * For removed entities, appends ?version= to point at the old version.
     */
    public function resolveWebUrl(): ?string
    {
        $entity = $this->entity;

        if ($entity === null) {
            return null;
        }

        $typeKey = EntityTypeConfig::keyForModel($this->entity_type);
        $config = $typeKey !== null ? EntityTypeConfig::get($typeKey) : null;

        if ($config === null) {
            return null;
        }

        $identifier = $entity->slug ?? $entity->uuid;

        try {
            $url = route($config['route_name'], [$config['route_param'] => $identifier]);
        } catch (\Throwable) {
            return null;
        }

        if ($this->change_type === 'removed') {
            $url .= '?version='.$this->fromVersion->code;
        }

        return $url;
    }

    /**
     * Build a nested tree from flat column_changes and data_changes.
     * Delegates to DeepDiff::buildChangeTree.
     *
     * @return array<string, mixed>
     */
    public function buildChangeTree(): array
    {
        return DeepDiff::buildChangeTree(
            $this->column_changes ? $this->column_changes->toArray() : [],
            $this->data_changes ? $this->data_changes->toArray() : [],
        );
    }
}
