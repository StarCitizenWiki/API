<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\Galactapedia;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Normalizer;

class Article extends Model
{
    use HasFactory;

    protected $table = 'galactapedia_articles';

    protected $fillable = [
        'cig_id',
        'title',
        'slug',
        'in_wiki',
        'disabled',
        'thumbnail',
    ];

    protected $casts = [
        'in_wiki' => 'boolean',
        'disabled' => 'boolean',
    ];

    private static array $ticks = ['’', '´', '‘', '’', '’', '’', '\'', '’', '’', 'ˈ', '`', '´'];

    public static function normalizeContent(string $translation): string
    {
        $translation = preg_replace(
            '/]\s+\(http/',
            '](http',
            $translation
        );

        // Fix heading
        $translation = preg_replace('/^(#+)\s+?(\w)/', '$1 $2', $translation);

        // Fix ticks
        $translation = str_replace(['“', '”'], '"', $translation);
        $translation = str_replace(self::$ticks, '\'', $translation);

        if (! Normalizer::isNormalized($translation)) {
            $translation = Normalizer::normalize($translation);
        }

        return trim($translation);
    }

    public function getRouteKey(): string
    {
        return $this->cig_id ?? '';
    }

    public function getCleanTitleAttribute(): string
    {
        return self::normalizeContent($this->title);
    }

    /**
     * Creates a link to the rsi galactapedia
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

    public function translations(): HasMany
    {
        return $this->hasMany(ArticleTranslation::class);
    }

    /**
     * Categories of the article
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
     */
    public function properties(): HasMany
    {
        return $this->hasMany(
            ArticleProperty::class,
        );
    }

    /**
     * Related articles
     */
    public function related(): BelongsToMany
    {
        return $this->belongsToMany(
            __CLASS__,
            'galactapedia_article_relates',
            'article_id',
            'related_article_id',
        );
    }

    /**
     * GraphQL Templates associated with this article
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
}
