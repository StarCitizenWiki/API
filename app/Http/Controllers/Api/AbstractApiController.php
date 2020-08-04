<?php declare(strict_types = 1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Transformers\Api\LocaleAwareTransformerInterface;
use Carbon\Carbon;
use Dingo\Api\Http\Response;
use Dingo\Api\Routing\Helpers;
use Dingo\Api\Transformer\Factory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use League\Fractal\TransformerAbstract;

/**
 * Base Controller that has Dingo Helpers
 */
abstract class AbstractApiController extends Controller
{
    use Helpers;

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
     * @var TransformerAbstract The Default Transformer for index and show
     */
    protected TransformerAbstract $transformer;

    /**
     * @var array Parameter Errors
     */
    protected array $errors = [];

    /**
     * @var int Pagination Limit, 0 = no pagination
     */
    protected int $limit;

    /**
     * @var string Locale Code, set if Transformer implements LocaleAwareTransformerInterface
     */
    protected string $localeCode;

    /**
     * @var array Extra Metadata to include
     */
    protected array $extraMeta = [];

    /**
     * AbstractApiController constructor.
     *
     * @param Request $request API Request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->processRequestParams();
        app(Factory::class)->disableEagerLoading();
    }

    /**
     * Processes all possible Request Parameters
     */
    protected function processRequestParams()
    {
        $this->processLimit();
        $this->processIncludes();
        $this->processLocale();
    }

    /**
     * Creates the API Response, Collection if no pagination, Paginator if a limit is set
     * Item if a single model is given
     *
     * @param Builder|Model $query
     *
     * @return Response
     */
    protected function getResponse($query): Response
    {
        if ($query instanceof Model) {
            return $this->response->item($query, $this->transformer)->setMeta($this->getMeta());
        }

        if ($this->limit === 0) {
            return $this->response->collection($query->get(), $this->transformer)->setMeta($this->getMeta());
        }

        $manufacturer = $query->paginate($this->limit);

        return $this->response->paginator($manufacturer, $this->transformer)->setMeta($this->getMeta());
    }

    /**
     * Generates the Meta Array
     *
     * @return array Meta Array
     */
    protected function getMeta(): array
    {
        $meta = [
            'processed_at' => Carbon::now()->toDateTimeString(),
        ];

        if (!empty($this->errors)) {
            $meta['errors'] = $this->errors;
        }

        if (!empty($this->transformer->getAvailableIncludes())) {
            $meta['valid_relations'] = array_map('Illuminate\Support\Str::snake', $this->transformer->getAvailableIncludes());
        }

        return array_merge($meta, $this->extraMeta);
    }

    /**
     * Set the Locale
     *
     * @param string $localeCode
     */
    protected function setLocale(string $localeCode)
    {
        if (in_array($localeCode, config('language.codes'), true)) {
            $this->localeCode = $localeCode;
            if ($this->transformer instanceof LocaleAwareTransformerInterface) {
                $this->transformer->setLocale($localeCode);
            }
        } else {
            $this->errors[self::LOCALE] = sprintf(static::INVALID_LOCALE_STRING, $localeCode);
        }
    }

    /**
     * Processes the given 'include' model relation key
     *
     * @param string|array $relations
     */
    protected function checkIncludes($relations): void
    {
        if (!is_array($relations)) {
            $relations = explode(',', $relations);
        }

        $relations = collect($relations);

        $relations->transform(
            static function ($relation) {
                return trim($relation);
            }
        )->transform(
            static function ($relation) {
                return Str::camel($relation);
            }
        )->each(
            function ($relation) {
                if (!in_array($relation, $this->transformer->getAvailableIncludes(), true)) {
                    $this->errors['include'][] = sprintf(static::INVALID_RELATION_STRING, Str::snake($relation));
                }
            }
        );
    }

    /**
     * Processes the 'limit' Request-Parameter
     */
    private function processLimit(): void
    {
        if ($this->request->has(self::LIMIT) && null !== $this->request->get(self::LIMIT, null)) {
            $itemLimit = (int) $this->request->get(self::LIMIT);

            if ($itemLimit > 0) {
                $this->limit = $itemLimit;
            } elseif (0 === $itemLimit) {
                $this->limit = 0;
            } else {
                $this->errors[self::LIMIT] = static::INVALID_LIMIT_STRING;
            }
        }
    }

    /**
     * Processes the 'include' Model Relations Request-Parameter
     */
    private function processIncludes(): void
    {
        if ($this->request->has('include') && null !== $this->request->get('include', null)) {
            $this->checkIncludes($this->request->get('include', []));
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
}
