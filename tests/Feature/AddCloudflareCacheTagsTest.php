<?php

declare(strict_types=1);

describe('Cloudflare cache tag middleware integration', function (): void {
    it('sets Cache-Tag header on API controllers', function (): void {
        $this->getJson('/api/comm-links')
            ->assertSuccessful()
            ->assertHeader('Cache-Tag', 'api, api-comm-links');
    });

    it('sets Cache-Tag header on web controllers', function (): void {
        $this->get('/comm-links')
            ->assertSuccessful()
            ->assertHeader('Cache-Tag', 'web, web-comm-links');
    });
});
