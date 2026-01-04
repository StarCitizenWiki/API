<?php

/**
 * [
 * 'table' => 'vehicles',
 * 'primary_key' => 'id',
 * 'rename' => [
 * 'old_column_name' => 'new_column_name',
 * ],
 * 'drop' => [
 * 'unused_column',
 * ],
 * 'defaults' => [
 * 'created_at' => now(), // example
 * ],
 * ],
 */

return [
    'from_connection' => env('MIGRATE_FROM_CONNECTION', 'mariadb'),
    'to_connection' => env('MIGRATE_TO_CONNECTION', 'pgsql'),

    'groups' => [
        'CommLinks' => [
            'comm_link_categories',
            'comm_link_channels',
            'comm_link_series',

            [
                'table' => 'comm_link_images',
                'primary_key' => 'id',

                'drop' => ['base_image_id', 'local'],
            ],

            'comm_link_image_metadata',
            'comm_link_links',
            'comm_links',
            'comm_link_link',
            'comm_link_image',
        ],

        'ShipMatrix' => [
            ['table' => 'manufacturers', 'target' => 'shipmatrix_manufacturers'],
            ['table' => 'production_notes', 'target' => 'shipmatrix_production_notes', 'defaults' => ['created_at' => now(), 'updated_at' => now()]],
            ['table' => 'production_statuses', 'target' => 'shipmatrix_production_statuses', 'defaults' => ['created_at' => now(), 'updated_at' => now()]],
            ['table' => 'vehicle_foci', 'target' => 'shipmatrix_vehicle_foci', 'defaults' => ['created_at' => now(), 'updated_at' => now()]],
            ['table' => 'vehicle_sizes', 'target' => 'shipmatrix_vehicle_sizes', 'defaults' => ['created_at' => now(), 'updated_at' => now()]],
            ['table' => 'vehicle_types', 'target' => 'shipmatrix_vehicle_types', 'defaults' => ['created_at' => now(), 'updated_at' => now()]],
            ['table' => 'vehicles', 'target' => 'shipmatrix_vehicles'],
            ['table' => 'vehicle_loaners', 'target' => 'shipmatrix_vehicle_loaners'],
            ['table' => 'vehicle_skus', 'target' => 'shipmatrix_vehicle_skus'],
            ['table' => 'vehicle_vehicle_focus', 'target' => 'shipmatrix_vehicle_vehicle_focus'],
            ['table' => 'vehicle_components', 'target' => 'shipmatrix_vehicle_components'],
            ['table' => 'vehicle_vehicle_component', 'target' => 'shipmatrix_vehicle_component'],
        ],

        'Galactapedia' => [
            'galactapedia_templates',
            'galactapedia_tags',
            'galactapedia_categories',
            'galactapedia_articles',
            'galactapedia_article_templates',
            'galactapedia_article_tags',
            'galactapedia_article_relates',
            'galactapedia_article_properties',
            'galactapedia_article_categories',
        ],

        'Stats' => [
            'stats',
        ],
    ],
];
