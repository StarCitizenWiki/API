<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\AbstractBaseResource;
use Illuminate\Support\Facades\Request;
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
#[OA\Server(url: 'https://api.star-citizen.wiki/api/v2/')]
#[OA\Parameter(
    name: 'page',
    in: 'query',
    schema: new OA\Schema(
        schema: 'page',
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
        schema: 'limit',
        description: 'Items per page, set to 0, to return all items',
        type: 'integer',
        format: 'int64',
        maximum: 1000,
        minimum: 0,
    ),
)]
#[OA\Parameter(
    name: 'locale',
    in: 'query',
    schema: new OA\Schema(
        schema: 'locale',
        description: 'Localization to use.',
        collectionFormat: 'csv',
        enum: [
            'de_DE',
            'en_EN',
        ]
    ),
)]
#[OA\Schema(
    schema: 'query',
    type: 'string',
)]
abstract class AbstractApiV2Controller extends Controller
{
    public const SC_DATA_KEY = 'api.sc_data_version';

    public const INVALID_LIMIT_STRING = 'Limit has to be greater than 0';

    public const INVALID_LOCALE_STRING = 'Locale Code \'%s\' is not valid';

    public const INVALID_RELATION_STRING = '\'%s\' does not exist';

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
        if ($this->request::has(self::LIMIT) && null !== $this->request::get(self::LIMIT)) {
            $itemLimit = (int)$this->request::get(self::LIMIT);

            if ($itemLimit > 0) {
                $this->limit = $itemLimit;
            } elseif (0 === $itemLimit) {
                $this->limit = 0;
            }
        }
    }

    /**
     * Processes the 'locale' Request-Parameter
     */
    private function processLocale(): void
    {
        if ($this->request::has(self::LOCALE) && null !== $this->request::get(self::LOCALE, null)) {
            $this->setLocale($this->request::get(self::LOCALE));
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
        } else {
            $this->errors[self::LOCALE] = sprintf(static::INVALID_LOCALE_STRING, $localeCode);
        }
    }

    /**
     * Disables the pagination by setting the limit to 0
     *
     * @return $this
     */
    protected function disablePagination(): self
    {
        $this->limit = 0;

        return $this;
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
        return collect($includes)->map(function($include) {
            if (is_array($include)) {
                [$to, $from] = $include;
                return AllowedInclude::relationship($to, $from);
            }

            return AllowedInclude::relationship($include);
        })->toArray();
    }
}
