<?php

declare(strict_types=1);

namespace App\Models\Rsi\CommLink\Image;

use App\Models\Rsi\CommLink\CommLink;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;
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
    ];

    protected $casts = [
        'local' => 'boolean',
    ];

    protected $with = [
        'hash',
        'metadata',
    ];

    /**
     * @return BelongsToMany
     */
    public function commLinks(): BelongsToMany
    {
        return $this->belongsToMany(CommLink::class, 'comm_link_image', 'comm_link_image_id', 'comm_link_id');
    }

    /**
     * @return HasOne
     */
    public function hash(): HasOne
    {
        return $this->hasOne(ImageHash::class, 'comm_link_image_id')
            ->withDefault(
                [
                    'perceptual_hash' => 'DEADBEEF',
                    'p_hash_1' => 0,
                    'p_hash_2' => 0,

                    'difference_hash' => 'DEADBEEF',
                    'd_hash_1' => 0,
                    'd_hash_2' => 0,

                    'average_hash' => 'DEADBEEF',
                    'a_hash_1' => 0,
                    'a_hash_2' => 0,
                ]
            );
    }

    /**
     * Check if the hash exists
     *
     * @return bool
     */
    public function isHashed(): bool
    {
        return $this->hash->perceptual_hash !== 'DEADBEEF';
    }

    /**
     * @return HasOne
     */
    public function metadata(): HasOne
    {
        return $this->hasOne(ImageMetadata::class, 'comm_link_image_id')
            ->withDefault(
                [
                    'size' => 0,
                    'mime' => 'undefined',
                    'last_modified' => Carbon::minValue(),
                ]
            );
    }

    /**
     * Image Name
     *
     * @return string
     */
    public function getNameAttribute(): string
    {
        return Arr::last(explode('/', $this->src));
    }

    /**
     * Generates a downloadable image link
     *
     * @return string
     */
    public function getUrlAttribute(): string
    {
        $url = config('api.rsi_url');

        if (!Str::startsWith($this->src, ['/media', '/rsi', '/layoutscache'])) {
            $url = 'https://media.robertsspaceindustries.com';
        }

        return sprintf('%s%s', $url, $this->src);
    }

    /**
     * Returns a local or remote url if the image is local or remote
     *
     * @return string
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
        switch ($this->metadata->mime) {
            case 'image/bmp':
                $ext = '.bmp';
                break;
            case 'image/jpeg':
                $ext = '.jpg';
                break;
            case 'image/tiff':
                $ext = '.tif';
                break;
            case 'image/x-icon':
                $ext = '.ico';
                break;
            case 'image/png':
                $ext = '.png';
                break;
            case 'video/mp4':
                $ext = '.mp4';
                break;
            default:
                $ext = '';
                break;
        }

        return $ext;
    }
}
