<?php declare(strict_types = 1);

namespace App\Models\Rsi\CommLink\Image;

use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $table = 'comm_link_images';

    protected $fillable = [
        'src',
        'alt',
    ];

    public function commLinks()
    {
        return $this->belongsToMany(CommLink::class);
    }
}
