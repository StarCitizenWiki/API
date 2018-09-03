<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 30.08.2018
 * Time: 10:22
 */

namespace App\Models\Rsi\CommLink;

use App\Models\Rsi\CommLink\Category\Category;
use App\Models\Rsi\CommLink\Channel\Channel;
use App\Models\Rsi\CommLink\Content\Content;
use App\Models\Rsi\CommLink\Series\Series;
use App\Models\System\Translation\AbstractHasTranslations as HasTranslations;
use App\Traits\HasModelChangelogTrait as ModelChangelog;

class CommLink extends HasTranslations
{
    use ModelChangelog;

    protected $fillable = [
        'cig_id',
        'title',
        'comment_count',
        'file',
        'channel_id',
        'category_id',
        'series_id',
        'created_at',
    ];

    protected $with = [
        'channel',
        'category',
        'series',
        'content',
    ];

    public function getRouteKey()
    {
        return $this->cig_id;
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function series()
    {
        return $this->belongsTo(Series::class);
    }

    public function content()
    {
        return $this->hasOne(Content::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany(CommLinkTranslation::class);
    }
}
