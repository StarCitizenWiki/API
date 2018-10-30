<?php declare(strict_types = 1);

/*
 * This file is part of Laravel Hashids.
 *
 * (c) Vincent Klaiber <hello@vinkla.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Default Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the connections below you wish to use as
    | your default connection for all work. Of course, you may use many
    | connections at once using the manager class.
    |
    */

    'default' => 'main',

    /*
    |--------------------------------------------------------------------------
    | Hashids Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the connections setup for your application. Example
    | configuration has been included, but you may add as many connections as
    | you would like.
    |
    */

    'connections' => [
        'main' => [
            'salt' => 'star-citizen.wiki_HashIDsSalt',
            'length' => 4,
        ],

        \App\Models\Api\Notification::class => [
            'salt' => 'star-citizen.wiki_notification',
            'length' => 4,
        ],

        \App\Models\Account\User\User::class => [
            'salt' => 'star-citizen.wiki_user',
            'length' => 4,
        ],

        /**
         * Star Citizen
         */
        \App\Models\Api\StarCitizen\ProductionNote\ProductionNote::class => [
            'salt' => 'star-citizen.wiki_production_note',
            'length' => 4,
        ],
    ],
];
