<?php

declare(strict_types=1);

namespace App\Models\Rsi\CommLink;

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

    public function commLinks(): HasMany
    {
        return $this->hasMany(CommLink::class);
    }
}
