<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Documentation par défaut
    |--------------------------------------------------------------------------
    */
    'default' => 'v1',

    /*
    |--------------------------------------------------------------------------
    | Liste des documentations
    |--------------------------------------------------------------------------
    */
    'documentations' => [

        'v1' => [

            'api' => [
                'title' => 'Bank API v1.0.1',
            ],

            'routes' => [
                // Routes Swagger
                'api' => 'api/documentation',
                'docs' => 'docs',
                'oauth2_callback' => 'api/oauth2-callback',

                // Aucun middleware par défaut (tu peux en ajouter si besoin)
                'middleware' => [
                    'api' => [],
                    'asset' => [],
                    'docs' => [],
                    'oauth2_callback' => [],
                ],
            ],

            'paths' => [
                /*
                |--------------------------------------------------------------------------
                | Chemins relatifs (très important pour Render)
                |--------------------------------------------------------------------------
                */
                'use_absolute_path' => false,

                // Dossier contenant les assets de Swagger UI
                'swagger_ui_assets_path' => '/swagger-ui/',

                // Nom du fichier JSON/YAML généré
                'docs_json' => 'api-docs.json',
                'docs_yaml' => 'api-docs/openapi.yaml',

                // Format principal utilisé
                'format_to_use_for_docs' => 'yaml',

                // Dossier public où Swagger met les fichiers
                'docs' => base_path('public/api-docs'),

                // On ne scanne pas automatiquement les annotations ici
                'annotations' => [],

                // Fichiers/dossiers exclus du scan (facultatif)
                'excludes' => [],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration par défaut (fallback)
    |--------------------------------------------------------------------------
    */
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

        /*
        |--------------------------------------------------------------------------
        | Proxy (utile sur Render, car l'app tourne derrière un reverse proxy HTTPS)
        |--------------------------------------------------------------------------
        */
        'proxy' => ['*'],

        'paths' => [
            'use_absolute_path' => false,
            'swagger_ui_assets_path' => '/swagger-ui/',
            'docs_json' => 'api-docs.json',
            'docs_yaml' => 'api-docs/openapi.yaml',
            'format_to_use_for_docs' => 'yaml',
            'annotations' => [],
            'base' => env('L5_SWAGGER_BASE_PATH', null),
        ],

        'scanOptions' => [
            'pattern' => null,
            'exclude' => [],
        ],

        /*
        |--------------------------------------------------------------------------
        | Génération
        |--------------------------------------------------------------------------
        */
        'generate_always' => true,
        'generate_yaml_copy' => true,

        /*
        |--------------------------------------------------------------------------
        | UI Swagger
        |--------------------------------------------------------------------------
        */
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

        /*
        |--------------------------------------------------------------------------
        | Autres paramètres
        |--------------------------------------------------------------------------
        */
        'operations_sort' => 'alpha',
        'validator_url' => null,
        'additional_config_url' => null,

        /*
        |--------------------------------------------------------------------------
        | Constantes & Sécurité
        |--------------------------------------------------------------------------
        */
        'constants' => [
            'L5_SWAGGER_CONST_HOST' => env('APP_URL', 'https://localhost'),
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
