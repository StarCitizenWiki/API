<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '3.0.0',
    title: 'Star Citizen API',
    contact: new OA\Contact(email: 'foxftw@star-citizen.wiki'),
)]
#[OA\Server(url: 'https://api.star-citizen.wiki')]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    bearerFormat: 'JWT',
    scheme: 'bearer',
)]
abstract class Controller
{
    /**
     * Clean the name for query use.
     */
    protected function cleanQueryName(string $name): string
    {
        return str_replace('_', ' ', urldecode($name));
    }
}
