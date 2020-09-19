<?php declare(strict_types=1);

namespace Tests\Feature\Controller\Api\V1\StarCitizen;


use App\Http\Controllers\Api\AbstractApiController;
use Tests\Feature\Controller\Api\ApiTestCase;

class StarCitizenTestCase extends ApiTestCase
{
    /**
     * Show Method Tests
     */

    /**
     * Test Show Specific Resource
     *
     * @param string $name The Resource Name
     */
    public function testShow(string $name): void
    {
        $response = $this->get(
            sprintf(
                '%s/%s',
                static::BASE_API_ENDPOINT,
                urlencode($name)
            )
        );

        $response->assertOk()
            ->assertSee($name)
            ->assertJsonStructure(
                [
                    'data' => $this->structure,
                    'meta',
                ]
            )
            ->assertHeader('content-type', 'application/json')
            ->assertHeader('x-ratelimit-limit')
            ->assertHeader('etag');
    }

    /**
     * Test Show Specific Resource that does not exist
     */
    public function testShowNotFound(): void
    {
        $response = $this->get(
            sprintf(
                '%s/%s',
                static::BASE_API_ENDPOINT,
                static::NOT_EXISTENT_NAME
            )
        );

        $response->assertNotFound()
            ->assertSee(
                sprintf(
                    AbstractApiController::NOT_FOUND_STRING,
                    static::NOT_EXISTENT_NAME
                ),
                false
            )
            ->assertHeader('content-type', 'application/json')
            ->assertHeader('x-ratelimit-limit');
    }

    /**
     * Test Show Specific Resource with multiple Translations
     *
     * @param string $name The Resource Name
     */
    public function testShowMultipleTranslations(string $name): void
    {
        $response = $this->get(
            sprintf(
                '%s/%s',
                static::BASE_API_ENDPOINT,
                urlencode($name)
            )
        );
        $response->assertOk()
            ->assertSee($name)
            ->assertJsonStructure(
                [
                    'data' => $this->structure,
                    'meta',
                ]
            )
            ->assertJsonStructure(
                [
                    'data' => [
                        'description' => [
                            'en_EN',
                            'de_DE',
                        ],
                    ],
                ]
            )
            ->assertSee(static::GERMAN_DEFAULT_TRANSLATION)
            ->assertHeader('content-type', 'application/json')
            ->assertHeader('x-ratelimit-limit')
            ->assertHeader('etag');
    }

    /**
     * Test Show Specific Resource with only german Translation
     *
     * @param string $name The Resource Name
     */
    public function testShowLocaleGerman(string $name): void
    {
        $response = $this->get(
            sprintf(
                '%s/%s?locale=%s',
                static::BASE_API_ENDPOINT,
                urlencode($name),
                'de_DE'
            )
        );

        $response->assertOk()
            ->assertSee($name)
            ->assertJsonStructure(
                [
                    'data' => $this->structure,
                    'meta',
                ]
            )
            ->assertSee(static::GERMAN_DEFAULT_TRANSLATION)
            ->assertHeader('content-type', 'application/json')
            ->assertHeader('x-ratelimit-limit')
            ->assertHeader('etag');
    }

    /**
     * Test Show Specific Resource with invalid Locale Code
     *
     * @param string $name The Resource Name
     */
    public function testShowLocaleInvalid(string $name): void
    {
        $response = $this->get(
            sprintf(
                '%s/%s?locale=%s',
                static::BASE_API_ENDPOINT,
                urlencode($name),
                'invalid'
            )
        );

        $response->assertOk()
            ->assertSee($name)
            ->assertJsonStructure(
                [
                    'data' => $this->structure,
                    'meta' => [
                        'errors' => [
                            'locale',
                        ],
                    ],
                ]
            )
            ->assertHeader('content-type', 'application/json')
            ->assertHeader('x-ratelimit-limit')
            ->assertHeader('etag');
    }


    /**
     * Search Method Tests
     */

    /**
     * Test Search for specific Resource
     *
     * @param string $name The Resource Name
     */
    public function testSearch(string $name): void
    {
        $response = $this->post(
            sprintf(
                '%s/%s',
                static::BASE_API_ENDPOINT,
                'search'
            ),
            [
                'query' => urlencode($name),
            ]
        );

        $response->assertOk()
            ->assertSee($name)
            ->assertJsonStructure(
                [
                    'data' => [
                        $this->structure,
                    ],
                    'meta',
                ]
            )
            ->assertHeader('content-type', 'application/json')
            ->assertHeader('x-ratelimit-limit')
            ->assertHeader('etag');
    }

    /**
     * Test Search for specific Resource
     *
     * @param string $name The Resource Name
     */
    public function testSearchWithGermanTranslation(string $name): void
    {
        $response = $this->post(
            sprintf(
                '%s/%s',
                static::BASE_API_ENDPOINT,
                'search'
            ),
            [
                'query' => urlencode($name),
            ]
        );

        $response->assertOk()
            ->assertSee($name)
            ->assertJsonStructure(
                [
                    'data' => [
                        $this->structure,
                    ],
                    'meta',
                ]
            )
            ->assertJsonStructure(
                [
                    'data' => [
                        [
                            'description' => [
                                'en_EN',
                                'de_DE',
                            ],
                        ],
                    ],
                ]
            )
            ->assertSee(static::GERMAN_DEFAULT_TRANSLATION)
            ->assertHeader('content-type', 'application/json')
            ->assertHeader('x-ratelimit-limit')
            ->assertHeader('etag');
    }

    /**
     * Test Search for Resource that does not exist
     */
    public function testSearchNotFound(): void
    {
        $response = $this->post(
            sprintf(
                '%s/%s',
                static::BASE_API_ENDPOINT,
                'search'
            ),
            [
                'query' => static::NOT_EXISTENT_NAME,
            ]
        );

        $response->assertNotFound()
            ->assertHeader('content-type', 'application/json')
            ->assertHeader('x-ratelimit-limit');
    }
}
