<?php declare(strict_types=1);

namespace App\Models\Rsi\CommLink\Image;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImageHash extends Model
{
    protected $table = 'comm_link_image_hashes';

    protected $fillable = [
        'perceptual_hash',
        'p_hash_1',
        'p_hash_2',

        'difference_hash',
        'd_hash_1',
        'd_hash_2',

        'average_hash',
        'a_hash_1',
        'a_hash_2',
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
