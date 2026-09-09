<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle;

use App\Models\Game\VehicleCuratedData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'vehicle_curated_data',
    title: 'Vehicle Curated Data',
    properties: [
        new OA\Property(property: 'source', type: 'string', enum: ['starcitizen.tools']),
        new OA\Property(property: 'page_url', description: 'Wiki page this data was scraped from', type: 'string', example: 'https://starcitizen.tools/Origin_300i'),
        new OA\Property(property: 'synced_at', description: 'Timestamp of the last successful wiki sync', type: 'string', format: 'datetime', example: '2026-09-09T05:00:00+00:00', nullable: true),
        new OA\Property(property: 'trailer_url', description: 'Official vehicle trailer video URL', type: 'string', example: 'https://www.youtube.com/watch?v=qDQ8dbE8qSo', nullable: true),
        new OA\Property(property: 'original_pledge_price', description: 'Original concept pledge price in USD', type: 'integer', example: 55, nullable: true),
        new OA\Property(property: 'original_warbond_price', description: 'Original warbond pledge price in USD', type: 'integer', example: 45, nullable: true),
        new OA\Property(property: 'added_in_version', description: 'Wiki-verbatim release patch label like "Patch V0.8"', type: 'string', example: 'Patch V0.8', nullable: true),
        new OA\Property(property: 'concept_date', description: 'Real-world concept announcement date', type: 'string', format: 'date', example: '2013-06-21', nullable: true),
        new OA\Property(property: 'sale_date', description: 'Date the vehicle first went on sale', type: 'string', format: 'date', example: '2013-06-21', nullable: true),
        new OA\Property(property: 'retire_date', description: 'Date the vehicle was retired from the pledge store', type: 'string', format: 'date', nullable: true),
        new OA\Property(property: 'pledge_availability', description: 'Current pledge-store availability label', type: 'string', example: 'Always available', nullable: true),
        new OA\Property(property: 'brochure_url', description: 'Digital brochure PDF URL', type: 'string', nullable: true),
        new OA\Property(property: 'presentation_urls', description: 'Manufacturer presentation page URLs', type: 'array', items: new OA\Items(type: 'string'), example: ['https://robertsspaceindustries.com/comm-link/transmission/17046-Origin-Celebration']),
        new OA\Property(property: 'whitleys_guide_url', description: "Whitley's Guide review article URL", type: 'string', nullable: true),
        new OA\Property(property: 'galactapedia_url', description: 'Galactapedia article URL', type: 'string', nullable: true),
        new OA\Property(property: 'qa_urls', description: 'QA forum thread URLs covering the vehicle', type: 'array', items: new OA\Items(type: 'string'), example: ['https://robertsspaceindustries.com/spectrum/community/SC/forum/50272/thread/origin-300-series-qa']),
    ],
    type: 'object'
)]
/**
 * @mixin VehicleCuratedData
 */
class VehicleCuratedDataResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'source' => 'starcitizen.tools',
            'page_url' => 'https://starcitizen.tools/'.str_replace(' ', '_', $this->wiki_page_title),
            'synced_at' => $this->synced_at,
            'trailer_url' => $this->trailer_url,
            'original_pledge_price' => $this->original_pledge_price,
            'original_warbond_price' => $this->original_warbond_price,
            'added_in_version' => $this->added_in_version,
            'concept_date' => $this->concept_date?->format('Y-m-d'),
            'sale_date' => $this->sale_date?->format('Y-m-d'),
            'retire_date' => $this->retire_date?->format('Y-m-d'),
            'pledge_availability' => $this->pledge_availability,
            'brochure_url' => $this->brochure_url,
            'presentation_urls' => $this->presentation_urls ?? [],
            'whitleys_guide_url' => $this->whitleys_guide_url,
            'galactapedia_url' => $this->galactapedia_url,
            'qa_urls' => $this->qa_urls ?? [],
        ];
    }
}
