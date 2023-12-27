<?php

declare(strict_types=1);

namespace App\Models\Rsi\CommLink\Image;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImageHash extends Model
{
    protected $table = 'comm_link_image_hashes';

    protected $fillable = [
        'perceptual_hash',
        'difference_hash',
        'average_hash',
        'pdq_hash1',
        'pdq_hash2',
        'pdq_hash3',
        'pdq_hash4',
        'pdq_quality',
    ];

    /**
     * The Comm-Link Image
     *
     * @return BelongsTo
     */
    public function image(): BelongsTo
    {
        return $this->belongsTo(Image::class);
    }
}
