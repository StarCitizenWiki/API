<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 30.08.2018
 * Time: 10:25
 */

namespace App\Models\Rsi\CommLink\Channel;

use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    protected $table = 'comm_link_channels';

    protected $fillable = [
        'name',
    ];

    public function commLinks()
    {
        return $this->hasMany(CommLink::class);
    }
}
