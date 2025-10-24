<?php

namespace App\Http\Controllers;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use L5Swagger\Exceptions\L5SwaggerException;

class SwaggerAssetController extends BaseController
{
    public function index(Request $request)
    {
        $fileSystem = new Filesystem();
        $documentation = $request->offsetGet('documentation') ?: config('l5-swagger.default');
        $asset = $request->offsetGet('asset');

        $path = public_path('swagger-ui/' . $asset);

        if (! $fileSystem->exists($path)) {
            throw new L5SwaggerException('Requested L5 Swagger asset file ('.$asset.') does not exist');
        }

        // Force HTTPS for assets when the request is over HTTPS to avoid mixed content
        $headers = [
            'Content-Type' => pathinfo($asset)['extension'] == 'css'
                ? 'text/css'
                : 'application/javascript',
        ];

        // Add CSP header to allow mixed content if necessary, but better to serve over HTTPS
        if ($request->isSecure()) {
            $headers['Content-Security-Policy'] = "upgrade-insecure-requests";
        }

        return (new Response(
            $fileSystem->get($path),
            200,
            $headers
        ))->setSharedMaxAge(31536000)
            ->setMaxAge(31536000)
            ->setExpires(new \DateTime('+1 year'));
    }
}