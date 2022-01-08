<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\Rsi\Transcript;

use App\Models\Transcript\Transcript;
use App\Transformers\Api\V1\AbstractV1Transformer as V1Transformer;
use League\Fractal\Resource\Item;

/**
 * Class TranscriptTransformer
 */
class TranscriptTransformer extends V1Transformer
{
    protected $availableIncludes = [
        'english',
        'german',
    ];

    /**
     * @param Transcript $transcript
     *
     * @return array
     */
    public function transform(Transcript $transcript): array
    {
        return [
            'title' => $transcript->title,
            'youtube_id' => $transcript->youtube_id,
            'youtube_url' => $transcript->youtube_url,
            'playlist_name' => $transcript->playlist_name,
            'upload_date' => $transcript->upload_date->format('Y-m-d'),
            'runtime' => $transcript->runtime,
            'runtime_formatted' => gmdate('H:i:s', $transcript->runtime),
            'thumbnail' => $transcript->thumbnail,
            'description' => $transcript->youtube_description,
        ];
    }


    /**
     * @param Transcript $commLink
     *
     * @return Item
     */
    public function includeEnglish(Transcript $commLink): Item
    {
        $translation = $commLink->english();

        return $this->item($translation, new TranslationTransformer());
    }

    /**
     * @param Transcript $commLink
     *
     * @return Item
     */
    public function includeGerman(Transcript $commLink): Item
    {
        //$translation = $commLink->german();
        $translation = null; // Disable this for now

        return $this->item($translation, new TranslationTransformer());
    }
}
