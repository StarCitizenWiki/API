<?php

declare(strict_types=1);

namespace App\Models\Rsi\CommLink\Image;

use App\Models\Rsi\CommLink\CommLink;
use App\Services\ImageHash\Implementations\PDQHash\PDQHash;
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

    /**
     * @return BelongsToMany
     */
    public function commLinks(): BelongsToMany
    {
        return $this->belongsToMany(CommLink::class, 'comm_link_image', 'comm_link_image_id', 'comm_link_id')->orderByDesc('cig_id');
    }

    /**
     * @return BelongsToMany
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            'comm_link_image_tag',
            'image_id',
            'tag_id'
        )->orderByDesc('images_count');
    }

    /**
     * @return HasOne
     */
    public function hash(): HasOne
    {
        return $this->hasOne(ImageHash::class, 'comm_link_image_id')
            ->withDefault(
                [
                    'perceptual_hash' => 0xDEADBEEF,
                    'difference_hash' => 0xDEADBEEF,
                    'average_hash' => 0xDEADBEEF,
                ]
            );
    }

    /**
     * @return HasMany
     */
    public function duplicates(): HasMany
    {
        return $this->hasMany(__CLASS__, 'base_image_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function baseImage(): BelongsTo
    {
        return $this->belongsTo(__CLASS__, 'id', 'base_image_id');
    }

    /**
     * Retrieve similar images to this one
     *
     * @param int $similarity
     * @param int $limit
     *
     * @return Collection
     */
    public function similarImages(int $similarity = 90, int $limit = 15): Collection
    {
        return ImageHash::query()
            ->select(['comm_link_image_hashes.comm_link_image_id', 'pdq_quality', 'perceptual_hash'])
            ->selectRaw(
                <<<SQL
(BIT_COUNT(CONV(HEX(pdq_hash1), 16, 10) ^ CONV(HEX(?), 16, 10)) +
BIT_COUNT(CONV(HEX(pdq_hash2), 16, 10) ^ CONV(HEX(?), 16, 10)) +
BIT_COUNT(CONV(HEX(pdq_hash3), 16, 10) ^ CONV(HEX(?), 16, 10)) +
BIT_COUNT(CONV(HEX(pdq_hash4), 16, 10) ^ CONV(HEX(?), 16, 10))) as pdq_distance
SQL,
                [
                    $this->hash->pdq_hash1,
                    $this->hash->pdq_hash2,
                    $this->hash->pdq_hash3,
                    $this->hash->pdq_hash4,
                ]
            )
            ->selectRaw(
                'BIT_COUNT(CONV(HEX(perceptual_hash), 16, 10) ^ CONV(HEX(?), 16, 10)) AS p_distance',
                [$this->hash->perceptual_hash]
            )
            ->join('comm_link_images', 'comm_link_image_hashes.comm_link_image_id', '=', 'comm_link_images.id')
            ->join('comm_link_image_metadata', 'comm_link_image_metadata.comm_link_image_id', '=', 'comm_link_images.id')
            // Filter out small images
            ->where('size', '>=', 250 * 1024)
            ->where('comm_link_images.id', '!=', $this->id)
            ->where('comm_link_images.base_image_id', 'IS NULL')
            ->orderBy('pdq_distance')
            ->limit($limit)
            ->get()
            ->map(
                function (object $data) {
                    $id = $data->comm_link_image_id;

                    $image = Image::query()->find($id);

                    if ($data->pdq_quality <= 66 || $data->pdq_distance === null) {
                        $image->similarity = round((1 - ($data->p_distance / 64)) * 100);
                        $image->similarity_method = __('Basierend auf Merkmalen des Inhalts');
                    } else {
                        $image->similarity = round((1 - ($data->pdq_distance / 256)) * 100);
                        $image->similarity_method = ''; #PDQ
                    }

                    return $image;
                }
            )
            ->filter()
            ->sortByDesc('similarity')
            ->filter(fn (object $image) => $image->similarity >= $similarity);
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

        if (!Str::startsWith($this->src, ['/media', '/rsi', '/layoutscache', '/i/'])) {
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
            case 'image/jpeg':
                $ext = 'jpg';
                break;
            case 'image/webp':
                $ext = 'webp';
                break;
            case 'image/tiff':
                $ext = 'tif';
                break;
            case 'image/x-icon':
                $ext = 'ico';
                break;
            case 'video/x-m4v':
                $ext = 'm4v';
                break;
            case 'video/h264':
                $ext = 'mp4';
                break;
            default:
                $ext = explode('/', $this->metadata->mime)[1] ?? '';
                break;
        }

        return $ext !== '' ? sprintf('.%s', $ext) : '';
    }
}
