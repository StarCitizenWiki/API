<?php

namespace Controller\Api\V2;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ItemRelatedItemsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createSystemLanguages();
        Artisan::call('sc:import-items');
    }

    public function test_show_with_related_items(): void
    {
        // known item from existing fixtures
        $uuid = '9c478af1-acfd-4e88-b065-c5ebeb05f507';
        $response = $this->get('api/v2/items/'.$uuid.'?include=related_items');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'related_items' => [
                        'variant_items',
                        'set_items',
                    ],
                ],
            ]);
    }
}
