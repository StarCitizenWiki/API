<?php

declare(strict_types=1);

namespace App\Models\Rsi\CommLink\Image;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImageMetadata extends Model
{
    protected $table = 'comm_link_image_metadata';

    protected $fillable = [
        'size',
        'mime',
        'last_modified',
    ];

    protected $casts = [
        'last_modified' => 'datetime',
        'size' => 'integer',
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

    public function getMimeClassAttribute(): string
    {
        switch ($this->mime) {
            case 'image/jpeg':
                return 'success';

            case 'image/png':
                return 'primary';

            case 'image/gif':
                return 'warning';

            default:
                return 'secondary';
        }
    }
}
