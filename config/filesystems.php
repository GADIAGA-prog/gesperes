<?php

return [
    'default' => env('FILESYSTEM_DISK', 'local'),
    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
        ],
        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],
        // Disque dédié aux documents RH sensibles (non public)
        // Pilotable par env DOCUMENTS_DISK :
        //   - en dev (Laragon) : défaut 'local' → storage/app/private/documents
        //   - sur Laravel Cloud : DOCUMENTS_DISK=s3 → bucket Object Storage (le FS local y est éphémère)
        // Les clés non pertinentes pour un driver sont simplement ignorées par Laravel.
        'documents' => [
            'driver' => env('DOCUMENTS_DISK', 'local'),
            // -- driver local (dev) --
            'root' => storage_path('app/private/documents'),
            'serve' => true,
            // -- driver s3 (Laravel Cloud Object Storage) --
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'visibility' => 'private',
            'throw' => false,
        ],
    ],
    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],
];
