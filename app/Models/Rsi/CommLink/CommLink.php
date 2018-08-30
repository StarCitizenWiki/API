<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 30.08.2018
 * Time: 10:22
 */

namespace App\Models\Rsi\CommLink;

use App\Traits\HasModelChangelogTrait as ModelChangelog;
use Illuminate\Database\Eloquent\Model;

class CommLink extends Model
{
    use ModelChangelog;

    protected $fillable = [
        'cig_id',
        'comment_count',
        'file',
        'hash',
        'resort_id',
        'category_id',
        'created_at',
    ];

    public function resort()
    {
        return $this->belongsTo(Resort::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
