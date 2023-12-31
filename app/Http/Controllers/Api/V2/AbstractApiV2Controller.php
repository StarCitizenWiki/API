<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2;

use App\Events\ApiRouteCalled;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedInclude;

#[OA\Info(
    version: '2.0.0',
    title: 'Star Citizen API',
    contact: new OA\Contact(email: 'foxftw@star-citizen.wiki'),
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
)]
#[OA\Server(url: 'https://api.star-citizen.wiki')]
#[OA\Parameter(
    name: 'page',
    in: 'query',
    schema: new OA\Schema(
        description: 'Page of pagination if any',
        type: 'integer',
        format: 'int64',
        minimum: 0,
    )
)]
#[OA\Parameter(
    name: 'limit',
    in: 'query',
    schema: new OA\Schema(
        description: 'Items per page, set to 0, to return all items',
        type: 'integer',
        format: 'int64',
        maximum: 1000,
        minimum: 1,
    ),
)]
#[OA\Parameter(
    name: 'locale',
    in: 'query',
    schema: new OA\Schema(
        description: 'Localization to use.',
        enum: [
            'de_DE',
            'en_EN',
        ]
    ),
)]
#[OA\Parameter(
    name: 'version',
    in: 'query',
    schema: new OA\Schema(
        description: 'Game Version',
        enum: [
            '3.12.11',
            '3.13.1',
            '3.14.1',
            '3.15.1',
            '3.17.5',
            '3.18.2',
        ]
    ),
    allowReserved: true
)]
#[OA\Parameter(
    parameter: 'commodity_includes_v2',
    name: 'include',
    in: 'query',
    schema: new OA\Schema(
        description: 'Available Commodity Item includes',
        type: 'array',
        items: new OA\Items(
            type: 'string',
            enum: [
                'shops',
                'shops.items',
            ]
        ),
    ),
    explode: false,
    allowReserved: true
)]
#[OA\Schema(
    schema: 'query',
    type: 'string',
)]
abstract class AbstractApiV2Controller extends Controller
{
    /**
     * Sprintf String which is used if no model was found
     */
    public const NOT_FOUND_STRING = 'No Results for Query \'%s\'';

    /**
     * Limit Get Parameter
     */
    private const LIMIT = 'limit';

    /**
     * Locale Get Parameter
     */
    private const LOCALE = 'locale';

    /**
     * @var Request The API Request
     */
    protected Request $request;

    /**
     * @var int Pagination Limit, 0 = no pagination
     */
    protected int $limit = 15;

    /**
     * AbstractApiController constructor.
     *
     * @param Request $request API Request
     */
    public function __construct(Request $request)
    {
        parent::__construct();
        $this->request = $request;

        $this->processRequestParams();

        ApiRouteCalled::dispatch([
            'url' => $request->fullUrl(),
            'user-agent' => $request->userAgent() ?? 'Star Citizen Wiki API',
            'forwarded-for' => $request->header('X-Forwarded-For', '127.0.0.1'),
        ]);
    }

    /**
     * Processes all possible Request Parameters
     */
    protected function processRequestParams(): void
    {
        $this->processLimit();
        $this->processLocale();
    }

    /**
     * Processes the 'limit' Request-Parameter
     */
    private function processLimit(): void
    {
        if ($this->request->input(self::LIMIT) !== null) {
            $itemLimit = (int) $this->request->get(self::LIMIT);

            if ($itemLimit > 500) {
                $this->limit = 500;
            } elseif ($itemLimit >= 0) {
                $this->limit = $itemLimit;
            }
        }
    }

    /**
     * Processes the 'locale' Request-Parameter
     */
    private function processLocale(): void
    {
        if ($this->request->has(self::LOCALE) && null !== $this->request->get(self::LOCALE, null)) {
            $this->setLocale($this->request->get(self::LOCALE));
        }
    }

    /**
     * Set the Locale
     *
     * @param string $localeCode
     */
    protected function setLocale(string $localeCode): void
    {
        if (in_array($localeCode, config('language.codes'), true)) {
            $this->localeCode = $localeCode;
            app()->setLocale(substr($localeCode, 0, 2));
        }
    }

    /**
     * Cleans the name for query use
     *
     * @param string $name
     * @return string
     */
    protected function cleanQueryName(string $name): string
    {
        return str_replace('_', ' ', urldecode($name));
    }

    protected function getAllowedIncludes(array $includes)
    {
        return collect($includes)->map(function ($include) {
            if (is_array($include)) {
                [$to, $from] = $include;

                return AllowedInclude::relationship($to, $from);
            }

            return AllowedInclude::relationship($include);
        })->flatten()->toArray();
    }
}
