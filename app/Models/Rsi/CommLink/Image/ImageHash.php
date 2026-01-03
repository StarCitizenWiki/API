<?php

declare(strict_types=1);

namespace App\Models\Rsi\CommLink\Image;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class ImageHash extends Model
{
    public const HASH_BITS = 256;

    protected $table = 'comm_link_image_hashes';

    protected $fillable = [
        'comm_link_image_id',
        'pdq_hash',
        'pdq_quality',
    ];

    /**
     * The Comm-Link Image
     */
    public function image(): BelongsTo
    {
        return $this->belongsTo(Image::class, 'comm_link_image_id');
    }

    public static function similarImagesForHash(
        string $pdqHash,
        int $similarity,
        int $limit = 50,
        ?int $excludeImageId = null,
        bool $excludeDerived = false
    ): Collection {
        $normalizedHash = self::normalizeBitString($pdqHash);

        if (strlen($normalizedHash) !== self::HASH_BITS) {
            return collect();
        }

        if (config('database.default') === 'sqlite') {
            return static::query()
                ->with(['image' => static fn ($query) => $query->withOnly([])])
                ->whereNotNull('pdq_hash')
                ->when(
                    $excludeImageId !== null,
                    fn ($query) => $query->where('comm_link_image_id', '!=', $excludeImageId)
                )
                ->when(
                    $excludeDerived,
                    fn ($query) => $query->whereHas(
                        'image',
                        fn ($imageQuery) => $imageQuery->whereNull('base_image_id')
                    )
                )
                ->get()
                ->map(
                    static function (ImageHash $hash) use ($normalizedHash): ?Image {
                        $image = $hash->image;
                        if ($image === null) {
                            return null;
                        }

                        $storedHash = self::normalizeBitString((string) $hash->pdq_hash);
                        if ($storedHash !== $normalizedHash) {
                            return null;
                        }

                        $image->similarity = 100;
                        $image->similarity_method = 'PDQ';
                        $image->pdq_distance = 0;

                        return $image;
                    }
                )
                ->filter()
                ->take($limit)
                ->values();
        }

        $maxDistance = self::maxDistanceForSimilarity($similarity);
        $distanceExpression = 'bit_count(pdq_hash # ?::bit(256))';

        return static::query()
            ->select('comm_link_image_hashes.*')
            ->selectRaw($distanceExpression.' as pdq_distance', [$normalizedHash])
            ->with(['image' => static fn ($query) => $query->withOnly([])])
            ->whereNotNull('pdq_hash')
            ->when(
                $excludeImageId !== null,
                fn ($query) => $query->where('comm_link_image_id', '!=', $excludeImageId)
            )
            ->when(
                $excludeDerived,
                fn ($query) => $query->whereHas(
                    'image',
                    fn ($imageQuery) => $imageQuery->whereNull('base_image_id')
                )
            )
            ->whereRaw($distanceExpression.' <= ?', [$normalizedHash, $maxDistance])
            ->orderBy('pdq_distance')
            ->limit($limit)
            ->get()
            ->map(
                static function (ImageHash $hash): ?Image {
                    $image = $hash->image;
                    if ($image === null) {
                        return null;
                    }

                    $distance = (int) ($hash->pdq_distance ?? self::HASH_BITS);
                    $image->similarity = round((1 - ($distance / self::HASH_BITS)) * 100);
                    $image->similarity_method = 'PDQ';
                    $image->pdq_distance = $distance;

                    return $image;
                }
            )
            ->filter()
            ->sortByDesc('similarity')
            ->values();
    }

    private static function normalizeBitString(string $value): string
    {
        $normalized = preg_replace('/[^01]/', '', $value) ?? '';

        return str_pad(substr($normalized, -self::HASH_BITS), self::HASH_BITS, '0', STR_PAD_LEFT);
    }

    private static function maxDistanceForSimilarity(int $similarity): int
    {
        $normalized = max(1, min(100, $similarity));
        $maxDistance = (int) floor(self::HASH_BITS * (1 - ($normalized / 100)));

        return max(0, min(self::HASH_BITS, $maxDistance));
    }
}
