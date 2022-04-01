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

    'default' => env('FILESYSTEM_DRIVER', 'local'),

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

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

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
        
        // 'admin' => [
        //     'driver' => 'local',
        //     'root' => storage_path('storage/admin'),
        // ],
        'admin' =>[
            'driver' => 'local',
            'root' => public_path('admin'),
        ],

        'local' => [
            'driver' => 'local',
            'root' => public_path('data'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => public_path('uploads'),
            'url' => env('APP_URL').'/program',
            'visibility' => 'public',
        ],

        'media' => [
            'driver' => 'local',
            'root' => public_path(),
            // 'root' => 'public/voices'),
            'url' => env('APP_URL'),
            'visibility' => 'public',
        ],

        'upload' => [
            'driver' =>'local',
            'root' => public_path('uploads'),
            'path' => public_path('uploads/'),
            'url' => env('APP_URL').'/uploads/',
            'visibility' =>'public',
            'files' => [
                'driver' => 'local',
                'root' => public_path('uploads/files'),
                'path' => public_path('uploads/files'),
                'url' => env('APP_URL') . '/uploads/files/',
                'visibility' => 'public',

            ],
            'streams' => [
                'driver' => 'local',
                'root' => public_path('uploads/streams'),
                'path' => public_path('uploads/streams'),
                'url' => env('APP_URL') . '/uploads/streams/',
                'visibility' => 'public',
                'hls' => [
                    'driver' => 'local',
                    'root' => public_path('uploads/streams/hls'),
                    'path' => public_path('uploads/streams/hls'),
                    'url' => env('APP_URL') . '/uploads/streams/hls',
                    'visibility' => 'public',
                ],
            ],
        ],
        'export' => [
            'driver' => 'local',
            'root' => public_path('uploads') . '/export/',
            'path' => public_path('uploads') . '/export/',
            'url' => env('APP_URL') . '/uploads/export/',
            'visibility' => 'public',
            'devices'=>[
                'statistical'=>[
                    'driver' => 'local',
                    'root' => public_path('uploads') . '/export/devices/statistical/',
                    'path' => public_path('uploads'). '/export/devices/statistical/',
                    'url' => env('APP_URL') . '/uploads/export/devices/statistical/',
                    'visibility' => 'public',
                ],
            ],
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
        ],
    ],
];
