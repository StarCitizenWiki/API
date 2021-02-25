<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Traits\HasModelChangelogTrait;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Used in models using the has changelogs trait
 * @see HasModelChangelogTrait
 */
interface HasChangelogsInterface
{
    /**
     * @return MorphMany
     *
     * @see HasModelChangelogTrait::changelogs()
     */
    public function changelogs(): MorphMany;
}
