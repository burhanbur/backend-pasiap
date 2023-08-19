<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *    title="Application Programming Interface (API) for Pasiap Apps",
 *    description="L5 Swagger OpenAPI documentation for Pasiap Apps",
 *    version="1.0.0",
 *    @OA\Contact(
 *        email="burhanburdev@gmail.com"
 *    ),
 * ),
 * 
 * @OA\Server(
 *    url=L5_SWAGGER_CONST_HOST,
 *    description="Demo API Server"
 * ),
 * 
 * @OA\SecurityScheme(
 *    securityScheme="bearerAuth",
 *    in="header",
 *    name="Authorization",
 *    type="http",
 *    scheme="bearer",
 *    bearerFormat="JWT",
 * ),
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
