<?php declare(strict_types = 1);

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

/**
 * Generic Model to hold all Changelogs as Json
 */
class ModelChangelog extends Model
{
    protected $fillable = [
        'changelog',
    ];

    public function changelog()
    {
        return $this->morphTo();
    }
}
