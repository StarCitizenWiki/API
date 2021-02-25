<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\Galactapedia;

use App\Events\ModelUpdating;
use App\Models\System\Translation\AbstractHasTranslations;
use App\Traits\HasModelChangelogTrait as ModelChangelog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Query\Builder;

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
        'thumbnail',
    ];

    /**
     * @return string
     */
    public function getRouteKey(): string
    {
        return $this->cig_id ?? '';
    }

    /**
     * Creates a link to the rsi galactapedia
     *
     * @return string
     */
    public function getUrlAttribute(): string
    {
        return sprintf(
            '%s/galactapedia/article/%s-%s',
            config('api.rsi_url'),
            $this->cig_id,
            $this->slug
        );
    }

    /**
     * Previous Comm-Link
     *
     * @return Builder|Model|object|null
     */
    public function getPrevAttribute()
    {
        return self::query()->where('id', '<', $this->id)->orderBy('id', 'desc')->first(['cig_id']);
    }

    /**
     * Next Comm-Link
     *
     * @return Builder|Model|object|null
     */
    public function getNextAttribute()
    {
        return self::query()->where('id', '>', $this->id)->orderBy('id')->first(['cig_id']);
    }

    /**
     * @return HasMany
     */
    public function translations(): HasMany
    {
        return $this->hasMany(ArticleTranslation::class);
    }

    /**
     * Categories of the article
     *
     * @return BelongsToMany
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'galactapedia_article_categories',
            'article_id',
            'category_id',
        );
    }

    /**
     * Tags of the article
     *
     * @return BelongsToMany
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            'galactapedia_article_tags',
            'article_id',
            'tag_id',
        );
    }

    /**
     * Article properties
     *
     * @return HasMany
     */
    public function properties(): HasMany
    {
        return $this->hasMany(
            ArticleProperty::class,
        );
    }

    /**
     * Related articles
     *
     * @return BelongsToMany
     */
    public function related(): BelongsToMany
    {
        return $this->belongsToMany(
            Article::class,
            'galactapedia_article_relates',
            'article_id',
            'related_article_id',
        );
    }

    /**
     * GraphQL Templates associated with this article
     *
     * @return BelongsToMany
     */
    public function templates(): BelongsToMany
    {
        return $this->belongsToMany(
            Template::class,
            'galactapedia_article_templates',
            'article_id',
            'template_id',
        );
    }

    /**
     * @return HasManyThrough
     */
    public function translationChangelogs(): HasManyThrough
    {
        return $this->hasManyThrough(
            \App\Models\System\ModelChangelog::class,
            ArticleTranslation::class,
            'article_id',
            'changelog_id'
        )->where('changelog_type', ArticleTranslation::class);
    }
}
