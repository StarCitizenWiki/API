<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 12.08.2018
 * Time: 14:55
 */

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
    public function testShow(string $name)
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
            );
    }

    /**
     * Test Show Specific Resource that does not exist
     */
    public function testShowNotFound()
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
                )
            );
    }

    /**
     * Test Show Specific Resource with multiple Translations
     *
     * @param string $name The Resource Name
     */
    public function testShowMultipleTranslations(string $name)
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
            ->assertSee(static::GERMAN_DEFAULT_TRANSLATION);
    }

    /**
     * Test Show Specific Resource with only german Translation
     *
     * @param string $name The Resource Name
     */
    public function testShowLocaleGerman(string $name)
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
            ->assertSee(static::GERMAN_DEFAULT_TRANSLATION);
    }

    /**
     * Test Show Specific Resource with invalid Locale Code
     *
     * @param string $name The Resource Name
     */
    public function testShowLocaleInvalid(string $name)
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
            ->assertSee(
                sprintf(
                    AbstractApiController::INVALID_LOCALE_STRING,
                    'invalid'
                )
            );
    }


    /**
     * Search Method Tests
     */

    /**
     * Test Search for specific Resource
     *
     * @param string $name The Resource Name
     */
    public function testSearch(string $name)
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
            );
    }

    /**
     * Test Search for specific Resource
     *
     * @param string $name The Resource Name
     */
    public function testSearchWithGermanTranslation(string $name)
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
            ->assertSee(static::GERMAN_DEFAULT_TRANSLATION);
    }

    /**
     * Test Search for Resource that does not exist
     */
    public function testSearchNotFound()
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

        $response->assertNotFound();
    }
}
