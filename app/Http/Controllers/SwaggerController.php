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
        $documentation = $request->offsetGet('documentation') ?: config('l5-swagger.default');
        $config = $request->offsetGet('config') ?: config('l5-swagger.documentations.' . $documentation, []);

        if ($proxy = $config['proxy'] ?? false) {
            if (! is_array($proxy)) {
                $proxy = [$proxy];
            }
            Request::setTrustedProxies(
                $proxy,
                Request::HEADER_X_FORWARDED_FOR |
                Request::HEADER_X_FORWARDED_HOST |
                Request::HEADER_X_FORWARDED_PORT |
                Request::HEADER_X_FORWARDED_PROTO |
                Request::HEADER_X_FORWARDED_AWS_ELB
            );
        }

        $urlToDocs = $this->generateDocumentationFileURL($documentation, $config);
        $useAbsolutePath = config('l5-swagger.documentations.'.$documentation.'.paths.use_absolute_path', true);

        // Need the / at the end to avoid CORS errors on Homestead systems.
        return ResponseFacade::make(
            view('l5-swagger::index', [
                'documentation' => $documentation,
                'secure' => RequestFacade::secure(),
                'urlToDocs' => $urlToDocs,
                'operationsSorter' => $config['operations_sort'] ?? null,
                'configUrl' => $config['additional_config_url'] ?? null,
                'validatorUrl' => $config['validator_url'] ?? null,
                'useAbsolutePath' => $useAbsolutePath,
            ]),
            200
        );
    }
}