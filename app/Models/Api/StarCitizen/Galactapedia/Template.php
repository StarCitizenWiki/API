<?php

declare(strict_types=1);

namespace App\Models\Api\StarCitizen\Galactapedia;

use App\Events\ModelUpdating;
use App\Traits\HasModelChangelogTrait as ModelChangelog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Template extends Model
{
    use HasFactory;
    use ModelChangelog;

    protected $table = 'galactapedia_templates';

    protected $dispatchesEvents = [
        'updating' => ModelUpdating::class,
        'created' => ModelUpdating::class,
        'deleting' => ModelUpdating::class,
    ];

    protected $fillable = [
        'template'
    ];

    public function article(): BelongsToMany
    {
        return $this->belongsToMany(Article::class);
    }
}
