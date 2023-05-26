<?php declare(strict_types=1);

namespace Tests\Feature\Controller\Api;

use App\Http\Controllers\Api\AbstractApiController;
use Tests\TestCase;

/**
 * @covers \App\Http\Middleware\CheckUserState
 */
class ApiTestCase extends TestCase
{
    /**
     * Default German Translation from Factories
     */
    protected const GERMAN_DEFAULT_TRANSLATION = 'Deutsches Lorem Ipsum';

    /**
     * Model $perPage content
     */
    protected const MODEL_DEFAULT_PAGINATION_COUNT = 5;

    /**
     * Show Api Endpoint without Trailing Slash
     */
    protected const BASE_API_ENDPOINT = '';

    /**
     * Name to use for 'Not Found' Tests
     */
    protected const NOT_EXISTENT_NAME = 'NotExistent';

    /**
     * ID to use for 'Not Found' Tests
     */
    protected const NOT_EXISTENT_ID = 999999;

    /**
     * @var array Base Transformer Structure
     */
    protected $structure = [];


    /**
     * Index Method Tests
     */

    /**
     * Test Index with default Pagination
     */
    public function testIndexPaginatedDefault(): void
    {
        $response = $this->get(static::BASE_API_ENDPOINT);

        $response->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        $this->structure,
                    ],
                    'meta',
                ]
            )
            ->assertJsonCount(static::MODEL_DEFAULT_PAGINATION_COUNT, 'data');
    }

    /**
     * Test Index with no Pagination
     *
     * @param int $allCount Count of Resources in DB
     */
    public function testIndexAll(int $allCount): void
    {
        $response = $this->get(
            sprintf(
                '%s?limit=%d',
                static::BASE_API_ENDPOINT,
                0
            )
        );

        $response->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        $this->structure,
                    ],
                    'meta',
                ]
            )
            ->assertJsonCount($allCount, 'data');
    }

    /**
     * Test Index with custom Pagination (limit)
     *
     * @param int $limit The Pagination Limit
     */
    public function testIndexPaginatedCustom(int $limit)
    {
        $response = $this->get(
            sprintf(
                '%s?limit=%d',
                static::BASE_API_ENDPOINT,
                $limit
            )
        );

        $response->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        $this->structure,
                    ],
                    'meta',
                ]
            )
            ->assertJsonCount($limit, 'data');
    }

    /**
     * Test Index with invalid Pagination (limit)
     *
     * @param int $limit The Pagination Limit
     */
    public function testIndexInvalidLimit(int $limit)
    {
        $response = $this->get(
            sprintf(
                '%s?limit=%d',
                static::BASE_API_ENDPOINT,
                $limit
            )
        );

        $response->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        $this->structure,
                    ],
                    'meta' => [
                        'errors' => [
                            'limit',
                        ],
                    ],
                ]
            )
            ->assertSee(AbstractApiController::INVALID_LIMIT_STRING);
    }
}
