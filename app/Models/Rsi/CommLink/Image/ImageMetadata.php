<?php

declare(strict_types=1);

namespace App\Models\Rsi\CommLink\Image;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImageMetadata extends Model
{
    use HasFactory;

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
     */
    public function image(): BelongsTo
    {
        return $this->belongsTo(Image::class);
    }
}
