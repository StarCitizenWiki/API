<?php declare(strict_types = 1);

namespace App\Models\Rsi\CommLink\Image;

use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Image
 */
class Image extends Model
{
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function commLinks()
    {
        return $this->belongsToMany(CommLink::class, 'comm_link_image', 'comm_link_image_id', 'comm_link_id');
    }

    /**
     * Image Name
     *
     * @return string
     */
    public function getNameAttribute()
    {
        return array_last(explode('/', $this->src));
    }
}
