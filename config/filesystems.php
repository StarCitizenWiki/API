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

        'tools' => [
            'driver' => 'local',
            'root' => storage_path('app/tools'),
        ],

        'tools_media' => [
            'driver' => 'local',
            'root' => storage_path('app/tools/media'),
        ],

        'tools_media_images' => [
            'driver' => 'local',
            'root' => storage_path('app/tools/media/images'),
            'visibility' => 'public',
        ],

        'api' => [
            'driver' => 'local',
            'root' => storage_path('app/api'),
        ],

        'starmap' => [
            'driver' => 'local',
            'root' => storage_path('app/api/starmap'),
        ],

        'ships' => [
            'driver' => 'local',
            'root' => storage_path('app/api/ships'),
        ],

    ],

];
