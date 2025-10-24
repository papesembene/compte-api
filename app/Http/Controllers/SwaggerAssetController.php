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

        return (new Response(
            $fileSystem->get($path),
            200,
            [
                'Content-Type' => pathinfo($asset)['extension'] == 'css'
                    ? 'text/css'
                    : 'application/javascript',
            ]
        ))->setSharedMaxAge(31536000)
            ->setMaxAge(31536000)
            ->setExpires(new \DateTime('+1 year'));
    }
}