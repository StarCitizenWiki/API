<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 26.07.2018
 * Time: 12:52
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Transformers\Api\LocaleAwareTransformerInterface;
use Carbon\Carbon;
use Dingo\Api\Routing\Helpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Base Controller that has Dingo Helpers
 */
abstract class AbstractApiController extends Controller
{
    use Helpers;

    const INVALID_LIMIT_STRING = 'Limit has to be greater than 0';

    const INVALID_LOCALE_STRING = 'Locale Code \'%s\' is not valid';

    const INVALID_RELATION_STRING = 'Relation \'%s\' does not exist';

    /**
     * Sprintf String which is used if no model was found
     */
    const NOT_FOUND_STRING = 'No Results for Query \'%s\'';

    /**
     * Array of valid Model Relations
     * Set this in the corresponding controller
     */
    protected const VALID_RELATIONS = [];

    /**
     * @var \Illuminate\Http\Request The API Request
     */
    protected $request;

    /**
     * @var \League\Fractal\TransformerAbstract The Default Transformer for index and show
     */
    protected $transformer;

    /**
     * @var array Parameter Errors
     */
    protected $errors = [];

    /**
     * @var array Parsed and validated relations
     */
    protected $validRelations = [];

    /**
     * @var int Pagination Limit, 0 = no pagination
     */
    protected $limit;

    /**
     * @var string Locale Code, set if Transformer implements LocaleAwareTransformerInterface
     */
    protected $localeCode;

    /**
     * AbstractApiController constructor.
     *
     * @param \Illuminate\Http\Request $request API Request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->processRequestParams();
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
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model $query
     *
     * @return \Dingo\Api\Http\Response
     */
    protected function getResponse($query)
    {
        $query->with($this->validRelations);

        if ($query instanceof Model) {
            $query->load($this->validRelations);

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

        if (!empty(static::VALID_RELATIONS)) {
            $meta['valid_relations'] = array_map('snake_case', static::VALID_RELATIONS);
        }

        return $meta;
    }

    /**
     * Set the Locale
     *
     * @param string $localeCode
     */
    protected function setLocale(string $localeCode)
    {
        if (in_array($localeCode, config('language.codes'))) {
            $this->localeCode = $localeCode;
            if ($this->transformer instanceof LocaleAwareTransformerInterface) {
                $this->transformer->setLocale($localeCode);
            }
        } else {
            $this->errors['locale'] = sprintf(static::INVALID_LOCALE_STRING, $localeCode);
        }
    }

    /**
     * Processes the given 'with' model relation key
     *
     * @param string|array $relations
     */
    protected function checkIncludes($relations)
    {
        if (!is_array($relations)) {
            $relations = explode(',', $relations);
        }
        $relations = array_map('trim', array_map('camel_case', $relations));

        foreach ($relations as $relation) {
            if (in_array($relation, static::VALID_RELATIONS)) {
                $this->validRelations[] = $relation;
            } else {
                $this->errors['with'][] = sprintf(static::INVALID_RELATION_STRING, snake_case($relation));
            }
        }
    }

    /**
     * Processes the 'limit' Request-Parameter
     */
    private function processLimit()
    {
        if ($this->request->has('limit')) {
            $limit = (int) $this->request->get('limit');

            if ($limit > 0) {
                $this->limit = $limit;
            } elseif (0 === $limit) {
                $this->limit = 0;
            } else {
                $this->errors['limit'] = static::INVALID_LIMIT_STRING;
            }
        }
    }

    /**
     * Processes the 'with' Model Relations Request-Parameter
     */
    private function processIncludes()
    {
        if ($this->request->has('with')) {
            $this->checkIncludes($this->request->get('with', []));
        }
    }

    /**
     * Processes the 'locale' Request-Parameter
     */
    private function processLocale()
    {
        if ($this->request->has('locale')) {
            $this->setLocale($this->request->get('locale'));
        }
    }
}
