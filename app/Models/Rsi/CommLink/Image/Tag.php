<?php

declare(strict_types=1);

namespace App\Models\Rsi\CommLink\Image;

use App\Models\Rsi\CommLink\CommLink;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Class Image
 */
class Tag extends Model
{
    use HasFactory;

    protected $table = 'comm_link_image_tags';

    protected $fillable = [
        'name',
        'name_en',
    ];

    protected $withCount = [
        'images',
    ];

    /**
     * @inheritDoc
     */
    public function getRouteKeyName(): string
    {
        return 'name';
    }

    /**
     * @return BelongsToMany
     */
    public function images(): BelongsToMany
    {
        return $this->belongsToMany(Image::class, 'comm_link_image_tag');
    }

    /**
     * @return mixed
     */
    public function getTranslatedNameAttribute()
    {
        $locale = app()->getLocale();
        if ($locale === 'en' && !empty($this["name_$locale"])) {
            return $this["name_$locale"];
        }

        return $this->name;
    }
}
