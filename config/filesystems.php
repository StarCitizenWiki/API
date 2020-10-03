<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => 'local',

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => 's3',

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "s3", "rackspace"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'visibility' => 'public',
        ],

        'comm_links' => [
            'driver' => 'local',
            'root' => storage_path('app/comm_links'),
        ],

        'comm_link_images' => [
            'driver' => 'local',
            'root' => storage_path('app/public/comm_link_images'),
        ],

        'api' => [
            'driver' => 'local',
            'root' => storage_path('app/api'),
        ],

        'starmap' => [
            'driver' => 'local',
            'root' => storage_path('app/api/starmap'),
        ],

        'vehicles' => [
            'driver' => 'local',
            'root' => storage_path('app/api/vehicles'),
        ],

        'stats' => [
            'driver' => 'local',
            'root' => storage_path('app/api/stats'),
        ],

    ],

];
