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
            'comm_link_translations',
            'comm_link_image',
        ],

        'ShipMatrix' => [
            'manufacturers',
            'manufacturer_translations',
            'production_notes',
            'production_note_translations',
            'production_statuses',
            'production_status_translations',
            'vehicle_foci',
            'vehicle_focus_translations',
            'vehicle_sizes',
            'vehicle_size_translations',
            'vehicle_types',
            'vehicle_type_translations',
            'vehicles',
            'vehicle_loaners',
            'vehicle_skus',
            'vehicle_translations',
            'vehicle_vehicle_focus',
        ],

        'Galactapedia' => [
            'galactapedia_templates',
            'galactapedia_tags',
            'galactapedia_categories',
            'galactapedia_articles',
            'galactapedia_article_translations',
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
