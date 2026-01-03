<?php

declare(strict_types=1);

namespace App\Models\Rsi\CommLink\Image;

use App\Models\Rsi\CommLink\CommLink;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Class Image
 */
class Image extends Model
{
    use HasFactory;

    protected $table = 'comm_link_images';

    protected $fillable = [
        'src',
        'alt',
        'local',
        'dir',
        'base_image_id',
    ];

    protected $casts = [
        'local' => 'boolean',
    ];

    protected $with = [
        'hash',
        'metadata',
    ];

    public function commLinks(): BelongsToMany
    {
        return $this->belongsToMany(
            CommLink::class,
            'comm_link_image',
            'comm_link_image_id',
            'comm_link_id'
        )
            ->orderByDesc('cig_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            'comm_link_image_tag',
            'image_id',
            'tag_id'
        )->orderByDesc('images_count');
    }

    public function hash(): HasOne
    {
        return $this->hasOne(ImageHash::class, 'comm_link_image_id')
            ->withDefault(
                [
                    'pdq_hash' => null,
                    'pdq_quality' => null,
                ]
            );
    }

    public function duplicates(): HasMany
    {
        return $this->hasMany(__CLASS__, 'base_image_id', 'id');
    }

    public function baseImage(): BelongsTo
    {
        return $this->belongsTo(
            __CLASS__,
            'base_image_id',
            'id',
        );
    }

    /**
     * Retrieve similar images to this one
     */
    public function similarImages(int $similarity = 90, int $limit = 15): Collection
    {
        if (empty($this->hash->pdq_hash)) {
            return collect();
        }

        return ImageHash::similarImagesForHash(
            $this->hash->pdq_hash,
            $similarity,
            $limit,
            $this->id,
            true
        );
    }

    /**
     * Check if the hash exists
     */
    public function isHashed(): bool
    {
        return ! empty($this->hash->pdq_hash);
    }

    public function metadata(): HasOne
    {
        return $this->hasOne(ImageMetadata::class, 'comm_link_image_id')
            ->withDefault(
                [
                    'size' => 0,
                    'mime' => 'undefined',
                    'last_modified' => Carbon::createFromTimestamp(0),
                ]
            );
    }

    /**
     * Image Name
     */
    public function getNameAttribute(): string
    {
        return Arr::last(explode('/', $this->src));
    }

    /**
     * Generates a downloadable image link
     */
    public function getUrlAttribute(): string
    {
        $url = config('services.rsi_url');

        if (! Str::startsWith($this->src, ['/media', '/rsi', '/layoutscache', '/i/'])) {
            $url = 'https://media.robertsspaceindustries.com';
        }

        return sprintf('%s%s', $url, $this->src);
    }

    /**
     * Returns a local or remote url if the image is local or remote
     */
    public function getLocalOrRemoteUrl(): string
    {
        if ($this->local) {
            return asset("storage/comm_link_images/{$this->dir}/{$this->name}");
        }

        return $this->url;
    }

    public function getExtension(): string
    {
        $ext = match ($this->metadata->mime) {
            'image/jpeg' => 'jpg',
            'image/webp' => 'webp',
            'image/tiff' => 'tif',
            'image/x-icon' => 'ico',
            'video/x-m4v' => 'm4v',
            'video/h264' => 'mp4',
            default => explode('/', $this->metadata->mime)[1] ?? '',
        };

        return $ext !== '' ? sprintf('.%s', $ext) : '';
    }

    public function getLocalPathAttribute(): string
    {
        return storage_path("app/public/comm_link_images/{$this->dir}/{$this->name}");
    }

    /**
     * Previous Image
     */
    public function getPrevAttribute()
    {
        return self::query()->where('id', '<', $this->id)
            ->whereNull('base_image_id')
            ->whereHas('metadata')
            ->whereHas('commLinks')
            ->orderBy('id', 'desc')
            ->first(['id']);
    }

    /**
     * Next Image
     */
    public function getNextAttribute()
    {
        return self::query()->where('id', '>', $this->id)
            ->whereNull('base_image_id')
            ->whereHas('metadata')
            ->whereHas('commLinks')
            ->orderBy('id')
            ->first(['id']);
    }
}
