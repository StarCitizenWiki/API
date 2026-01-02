<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\System\Language;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'translation_v2',
    title: 'Grouped Translations',
    description: 'Translations of an entity',
    properties: [
        new OA\Property(property: Language::ENGLISH, type: 'string'),
        new OA\Property(property: Language::GERMAN, type: 'string'),
        new OA\Property(property: Language::CHINESE, type: 'string'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'translation_single_v2',
    title: 'Single Translation',
    description: 'Translation of an entity',
    type: 'string'
)]
final class TranslationSchemas {}
