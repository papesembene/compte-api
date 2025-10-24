<?php

namespace App\Http\Controllers;

use L5Swagger\Http\Controllers\SwaggerController as BaseSwaggerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Illuminate\Support\Facades\Request as RequestFacade;

class SwaggerController extends BaseSwaggerController
{
    public function api(Request $request)
    {
        // Serve the OpenAPI YAML file
        $yamlPath = public_path('api-docs/openapi.yaml');
        if (!file_exists($yamlPath)) {
            return ResponseFacade::make('OpenAPI file not found', 404);
        }

        $content = file_get_contents($yamlPath);
        return ResponseFacade::make($content, 200, ['Content-Type' => 'application/yaml']);
    }
}