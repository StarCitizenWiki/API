<?php

declare(strict_types=1);

use App\Http\Resources\AbstractBaseResource;

uses(Tests\TestCase::class);

it('generates api urls from named routes', function (string $routeName, array $parameters): void {
    $resource = new class(null) extends AbstractBaseResource
    {
        public function toArray($request): array
        {
            return [];
        }

        public function apiUrl(string $routeName, array $parameters = []): string
        {
            return route($routeName, $parameters);
        }
    };

    expect($resource->apiUrl($routeName, $parameters))
        ->toBe(route($routeName, $parameters));
})->with([
    'comm-link' => ['comm-links.show', ['id' => 101]],
    'comm-link-similar' => ['comm-link-images.similar', ['image' => 202]],
    'vehicle' => ['vehicles.show', ['vehicle' => 'avenger']],
    'shipmatrix-vehicle' => ['shipmatrix.vehicles.show', ['vehicle' => 'f7c-hornet']],
    'starsystem' => ['starsystems.show', ['code' => 'SOL']],
    'celestial-object' => ['celestial-objects.show', ['code' => 'CRUSADER']],
    'galactapedia' => ['galactapedia.show', ['article' => 'galactapedia-slug']],
    'item' => ['items.show', ['identifier' => 'item-uuid']],
    'manufacturer' => ['manufacturers.show', ['manufacturer' => 'Aegis Dynamics']],
]);
