<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\System\ModelChangelog;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Trait HasModelChangelogTrait
 */
trait HasModelChangelogTrait
{
    /**
     * Changelogs
     *
     * @return MorphMany
     */
    public function changelogs(): MorphMany
    {
        return $this->morphMany(ModelChangelog::class, 'changelog')->orderByDesc('created_at');
    }
}
