<?php declare(strict_types=1);

namespace App\Models\Rsi\CommLink\Image;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImageHash extends Model
{
    protected $table = 'comm_link_image_hashes';

    protected $fillable = [
        'hash',
    ];

    public function image(): BelongsTo
    {
        return $this->belongsTo(Image::class);
    }
}
