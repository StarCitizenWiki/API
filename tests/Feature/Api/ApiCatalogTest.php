<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

describe('api-catalog', function (): void {
    it('returns an RFC 9727 catalog document linking the OpenAPI description and human docs', function (): void {
        $response = $this->get('/.well-known/api-catalog');

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/linkset+json')
            ->assertJsonStructure([
                'linkset' => [
                    ['anchor', 'link' => [['href', 'rel', 'type']]],
                ],
            ]);

        $linkset = $response->json('linkset');

        expect($linkset)->toHaveCount(1)
            ->and($linkset[0]['anchor'])->toBe(url('/api'))
            ->and($linkset[0]['link'][0]['href'])->toBe(url('/api/openapi'))
            ->and($linkset[0]['link'][0]['rel'])->toBe('service-desc')
            ->and($linkset[0]['link'][1]['href'])->toBe(url('/developers'))
            ->and($linkset[0]['link'][1]['rel'])->toBe('service-doc')
            ->and($linkset[0]['link'][2]['href'])->toBe(url('/up'))
            ->and($linkset[0]['link'][2]['rel'])->toBe('status');
    });

    it('includes the api-catalog Link header in HEAD responses', function (): void {
        $response = $this->head('/.well-known/api-catalog');

        $response->assertOk()
            ->assertHeader('Link', '</.well-known/api-catalog>; rel="api-catalog", </api/openapi>; rel="service-desc"; type="application/vnd.oai.openapi"');
    });

    it('exposes the api-catalog Link header on regular pages', function (): void {
        $response = $this->get('/');

        $response->assertOk()
            ->assertHeader('Link', '</.well-known/api-catalog>; rel="api-catalog", </api/openapi>; rel="service-desc"; type="application/vnd.oai.openapi"');
    });
});
