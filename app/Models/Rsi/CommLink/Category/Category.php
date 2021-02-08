<?php

declare(strict_types=1);

namespace App\Models\Rsi\CommLink\Category;

use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Comm-Link Category
 */
class Category extends Model
{
    use HasFactory;

    protected $table = 'comm_link_categories';

    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * {@inheritdoc}
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * @return HasMany
     */
    public function commLinks(): HasMany
    {
        return $this->hasMany(CommLink::class);
    }
}
