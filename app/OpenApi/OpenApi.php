<?php

namespace App\OpenApi;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="My Backend API",
 *     version="1.0.0",
 *     description="API documentation for my Laravel backend."
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class OpenApi {}
