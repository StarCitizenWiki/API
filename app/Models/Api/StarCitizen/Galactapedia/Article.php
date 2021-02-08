<?php

declare(strict_types=1);

namespace App\Models\Api\StarCitizen\Galactapedia;

use App\Events\ModelUpdating;
use App\Models\System\Translation\AbstractHasTranslations;
use App\Traits\HasModelChangelogTrait as ModelChangelog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends AbstractHasTranslations
{
    use HasFactory;
    use ModelChangelog;

    protected $table = 'galactapedia_articles';

    protected $dispatchesEvents = [
        'updating' => ModelUpdating::class,
        'created' => ModelUpdating::class,
        'deleting' => ModelUpdating::class,
    ];

    protected $fillable = [
        'cig_id',
        'title',
        'slug',
        'thumbnail'
    ];

    /**
     * @return string
     */
    public function getRouteKey(): string
    {
        return $this->cig_id;
    }

    /**
     * @return HasMany
     */
    public function translations(): HasMany
    {
        return $this->hasMany(ArticleTranslation::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'galactapedia_article_categories',
            'article_id',
            'category_id',
        );
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            'galactapedia_article_tags',
            'article_id',
            'tag_id',
        );
    }

    public function properties(): HasMany
    {
        return $this->hasMany(
            ArticleProperty::class,
        );
    }

    public function related(): BelongsToMany
    {
        return $this->belongsToMany(
            Article::class,
            'galactapedia_article_relates',
            'article_id',
            'related_article_id',
        );
    }

    public function templates(): BelongsToMany
    {
        return $this->belongsToMany(
            Template::class,
            'galactapedia_article_templates',
            'article_id',
            'template_id',
        );
    }
}
