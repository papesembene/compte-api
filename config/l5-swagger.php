<?php

return [

    'default' => 'v1',

    'documentations' => [

        'v1' => [

            'api' => [
                'title' => 'Compte API v1',
            ],

            'routes' => [
                // URL pour accéder à la doc Swagger
                'api' => 'api/documentation',
            ],

            'paths' => [
                'use_absolute_path' => true,

                // Swagger UI assets
                'swagger_ui_assets_path' => 'vendor/swagger-ui/dist/',

                // Nom du fichier JSON généré (on ne l'utilise pas ici)
                'docs_json' => 'api-docs.json',

                // Nom du fichier YAML existant
                'docs_yaml' => 'openapi.yaml',

                // On force Swagger UI à utiliser YAML
                'format_to_use_for_docs' => 'yaml',

                // PAS de scan PHP, on met null ou vide
                'annotations' => [],

                // Chemin absolu vers le répertoire où les annotations analysées seront stockées
                'docs' => public_path('api-docs'),

                // Répertoires à exclure du scan
                'excludes' => [],
            ],
        ],
    ],

    'defaults' => [
        'routes' => [
            'docs' => 'docs',
            'oauth2_callback' => 'api/oauth2-callback',
            'middleware' => [
                'api' => [],
                'asset' => [],
                'docs' => [],
                'oauth2_callback' => [],
            ],
        ],

        // ✅ Ajout du proxy pour éviter l’erreur précédente
        'proxy' => false,

        'paths' => [
            'use_absolute_path' => true,
            'swagger_ui_assets_path' => 'vendor/swagger-ui/dist/',
            'docs_json' => 'api-docs.json',     
            'docs_yaml' => 'api-docs/openapi.yaml',
            'format_to_use_for_docs' => 'yaml',
            'annotations' => [],    
            'base' => env('L5_SWAGGER_BASE_PATH', null),           
        ],

        'scanOptions' => [
            // PAS de scan
            'pattern' => null,
            'exclude' => [],
        ],

        // Toujours générer YAML
        'generate_always' => false,
        'generate_yaml_copy' => true,

        'ui' => [
            'display' => [
                'dark_mode' => false,
                'doc_expansion' => 'none',
                'filter' => true,
            ],
            'authorization' => [
                'persist_authorization' => false,
                'oauth2' => [
                    'use_pkce_with_authorization_code_grant' => false,
                ],
            ],
        ],

        // ✅ Ces trois lignes évitent l’erreur "Undefined array key"
        'operations_sort' => 'alpha',
        'validator_url' => null,
        'additional_config_url' => null,

        'constants' => [
            'L5_SWAGGER_CONST_HOST' => env('L5_SWAGGER_CONST_HOST', 'http://localhost:8000'),
        ],
        'securityDefinitions' => [
    'securitySchemes' => [
        [
            'type' => 'http',
            'scheme' => 'bearer',
            'bearerFormat' => 'JWT',
        ],
    ],
],

    ],
];
