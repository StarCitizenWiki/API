<?php

declare(strict_types=1);

/**
 * Vehicle JSON sortField mappings.
 *
 * Maps sort keys to JSONB paths for PostgreSQL sorting.
 */
return [
    'Cooling.GenerationSegments' => ['path' => 'Cooling.GenerationSegments', 'cast' => 'numeric'],
    'Cooling.UsageShieldsPct' => ['path' => 'Cooling.UsageShieldsPct', 'cast' => 'numeric'],
    'Cooling.UsageQuantumPct' => ['path' => 'Cooling.UsageQuantumPct', 'cast' => 'numeric'],
    'Power.GenerationSegments' => ['path' => 'Power.GenerationSegments', 'cast' => 'numeric'],
    'Power.UsedSegmentsShields' => ['path' => 'Power.UsedSegmentsShields', 'cast' => 'numeric'],
    'Power.UsedSegmentsQuantum' => ['path' => 'Power.UsedSegmentsQuantum', 'cast' => 'numeric'],
];
