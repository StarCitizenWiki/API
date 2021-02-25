<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\Galactapedia;

use App\Events\ModelUpdating;
use App\Traits\HasModelChangelogTrait as ModelChangelog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleProperty extends Model
{
    use HasFactory;
    use ModelChangelog;

    protected $table = 'galactapedia_article_properties';

    protected $dispatchesEvents = [
        'updating' => ModelUpdating::class,
        'created' => ModelUpdating::class,
        'deleting' => ModelUpdating::class,
    ];

    protected $fillable = [
        'article_id',
        'name',
        'content',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
