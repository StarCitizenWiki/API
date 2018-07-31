<?php declare(strict_types = 1);

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

class ModelChangelog extends Model
{
    protected $fillable = [
        'changelog',
    ];

    public function changelog()
    {
        return $this->morphTo();
    }
}
