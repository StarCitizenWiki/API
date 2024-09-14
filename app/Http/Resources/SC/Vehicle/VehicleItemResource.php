<?php

namespace App\Http\Resources\SC\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'vehicle_item_v2',
    title: 'Vehicle Item',
    type: 'object',
    allOf: [
        new OA\Schema(ref: '#/components/schemas/item_base_v2'),
        new OA\Schema(ref: '#/components/schemas/vehicle_item_specification_v2'),
    ],
)]

#[OA\Schema(
    schema: 'vehicle_item_specification_v2',
    title: 'Vehicle Items',
    type: 'object',
    oneOf: [
        new OA\Schema(
            title: 'Turret item',
            properties: [
                new OA\Property(property: 'min_size', type: 'integer'),
                new OA\Property(property: 'max_size', type: 'integer'),
                new OA\Property(property: 'max_mounts', type: 'integer', nullable: true),
                new OA\Property(property: 'max_missiles', type: 'integer', nullable: true),
                new OA\Property(property: 'max_bombs', type: 'integer', nullable: true),
            ],
            type: 'object',
        ),
        new OA\Schema(
            title: 'Bomb',
            properties: [
                new OA\Property(property: 'bomb', ref: '#/components/schemas/bomb_v2'),
            ],
            type: 'object',
        ),
        new OA\Schema(
            title: 'Cooler',
            properties: [
                new OA\Property(property: 'cooler', ref: '#/components/schemas/cooler_v2'),
            ],
            type: 'object',
        ),
        new OA\Schema(
            title: 'Emp',
            properties: [
                new OA\Property(property: 'emp', ref: '#/components/schemas/emp_v2'),
            ],
            type: 'object',
        ),
        new OA\Schema(
            title: 'Flight Controller',
            properties: [
                new OA\Property(property: 'flight_controller', ref: '#/components/schemas/flight_controller_v2'),
            ],
            type: 'object',
        ),
        new OA\Schema(
            title: 'Fuel Intake',
            properties: [
                new OA\Property(property: 'fuel_intake', ref: '#/components/schemas/fuel_intake_v2'),
            ],
            type: 'object',
        ),
        new OA\Schema(
            title: 'Fuel Tank',
            properties: [
                new OA\Property(property: 'fuel_tank', ref: '#/components/schemas/fuel_tank_v2'),
            ],
            type: 'object',
        ),
        new OA\Schema(
            title: 'Mining Laser',
            properties: [
                new OA\Property(property: 'mining_laser', ref: '#/components/schemas/mining_laser_v2'),
            ],
            type: 'object',
        ),
        new OA\Schema(
            title: 'Mining Module',
            properties: [
                new OA\Property(property: 'mining_module', ref: '#/components/schemas/mining_module_v2'),
            ],
            type: 'object',
        ),
        new OA\Schema(
            title: 'Missile',
            properties: [
                new OA\Property(property: 'missile', ref: '#/components/schemas/missile_v2'),
            ],
            type: 'object',
        ),
        new OA\Schema(
            title: 'Power Plant',
            properties: [
                new OA\Property(property: 'power_plant', ref: '#/components/schemas/power_plant_v2'),
            ],
            type: 'object',
        ),
        new OA\Schema(
            title: 'Quantum Drive',
            properties: [
                new OA\Property(property: 'quantum_drive', ref: '#/components/schemas/quantum_drive_v2'),
            ],
            type: 'object',
        ),
        new OA\Schema(
            title: 'Quantum Interdiction Generator',
            properties: [
                new OA\Property(property: 'quantum_interdiction_generator', ref: '#/components/schemas/qig_v2'),
            ],
            type: 'object',
        ),
        new OA\Schema(
            title: 'Self Destruct',
            properties: [
                new OA\Property(property: 'self_destruct', ref: '#/components/schemas/self_destruct_v2'),
            ],
            type: 'object',
        ),
        new OA\Schema(
            title: 'Shield',
            properties: [
                new OA\Property(property: 'shield', ref: '#/components/schemas/shield_v2'),
            ],
            type: 'object',
        ),
        new OA\Schema(
            title: 'Thruster',
            properties: [
                new OA\Property(property: 'thruster', ref: '#/components/schemas/thruster_v2'),
            ],
            type: 'object',
        ),
        new OA\Schema(
            title: 'Armor',
            properties: [
                new OA\Property(property: 'armor', ref: '#/components/schemas/vehicle_armor_v2'),
            ],
            type: 'object',
        ),
        new OA\Schema(
            title: 'Vehicle Weapon',
            properties: [
                new OA\Property(property: 'vehicle_weapon', ref: '#/components/schemas/vehicle_weapon_v2'),
            ],
            type: 'object',
        ),
    ],
)]
class VehicleItemResource extends AbstractBaseResource {}
