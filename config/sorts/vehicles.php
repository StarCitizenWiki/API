<?php

declare(strict_types=1);

/**
 * Vehicle JSON sortField mappings.
 *
 * Maps sort keys to JSONB paths for PostgreSQL sorting.
 * Extracted from VehicleController's existing implementation.
 */
return [
    // Base
    'size_class' => ['path' => 'Size', 'cast' => 'numeric'],

    // Physical Dimensions
    'length' => ['path' => 'Length', 'cast' => 'numeric'],
    'width' => ['path' => 'Width', 'cast' => 'numeric'],
    'height' => ['path' => 'Height', 'cast' => 'numeric'],

    // Mass & Cargo
    'mass_total' => ['path' => 'MassTotal', 'cast' => 'numeric'],
    'cargo_capacity' => ['path' => 'Cargo', 'cast' => 'numeric'],
    'vehicle_inventory' => ['path' => 'Stowage', 'cast' => 'numeric'],

    // Crew
    'crew.min' => ['path' => 'Crew', 'cast' => 'numeric'],

    // Health & Durability
    'health' => ['path' => 'Health', 'cast' => 'numeric'],
    'armor.health' => ['path' => 'Armor.Health', 'cast' => 'numeric'],

    // Shield
    'shield.hp' => ['path' => 'ShieldsTotal.Hp', 'cast' => 'numeric'],
    'shield.face_type' => ['path' => 'ShieldController.FaceType', 'cast' => 'text'],

    // Speed & Performance
    'speed.scm' => ['path' => 'FlightCharacteristics.IFCS.ScmSpeed', 'cast' => 'numeric'],
    'speed.max' => ['path' => 'FlightCharacteristics.IFCS.MaxSpeed', 'cast' => 'numeric'],

    // Cross Section
    'cross_section.length' => ['path' => 'CrossSection.X', 'cast' => 'numeric'],
    'cross_section.width' => ['path' => 'CrossSection.Y', 'cast' => 'numeric'],
    'cross_section.height' => ['path' => 'CrossSection.Z', 'cast' => 'numeric'],

    // Types
    'is_vehicle' => ['path' => 'IsVehicle', 'cast' => 'boolean'],
    'is_gravlev' => ['path' => 'IsGravlev', 'cast' => 'boolean'],
    'is_spaceship' => ['path' => 'IsSpaceship', 'cast' => 'boolean'],

    // Signature / Emission
    'signature.ir_quantum' => ['path' => 'Emission.IrQuantum', 'cast' => 'numeric'],
    'signature.ir_shields' => ['path' => 'Emission.IrShields', 'cast' => 'numeric'],
    'signature.em_quantum' => ['path' => 'Emission.EmQuantum', 'cast' => 'numeric'],
    'signature.em_shields' => ['path' => 'Emission.EmShields', 'cast' => 'numeric'],
];
