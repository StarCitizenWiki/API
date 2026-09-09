<?php

declare(strict_types=1);

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleCuratedData extends Model
{
    use HasFactory;

    protected $table = 'game_vehicle_curated_data';

    protected $fillable = [
        'game_vehicle_id',
        'trailer_url',
        'original_pledge_price',
        'original_warbond_price',
        'added_in_version',
        'concept_date',
        'sale_date',
        'retire_date',
        'pledge_availability',
        'qa_urls',
        'brochure_url',
        'presentation_urls',
        'whitleys_guide_url',
        'galactapedia_url',
        'wiki_page_title',
        'wiki_revision_id',
        'raw',
        'synced_at',
    ];

    protected $casts = [
        'original_pledge_price' => 'integer',
        'original_warbond_price' => 'integer',
        'concept_date' => 'date',
        'sale_date' => 'date',
        'retire_date' => 'date',
        'qa_urls' => 'array',
        'presentation_urls' => 'array',
        'wiki_revision_id' => 'integer',
        'raw' => 'array',
        'synced_at' => 'datetime',
    ];
}
