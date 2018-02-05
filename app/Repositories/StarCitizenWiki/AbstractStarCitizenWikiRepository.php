<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hannes
 * Date: 01.02.2017
 * Time: 22:58
 */

namespace App\Repositories\StarCitizenWiki;

use App\Repositories\AbstractBaseRepository;

/**
 * Class BaseStarCitizenWikiAPI
 *
 * @package App\Repositories\StarCitizenWiki\ApiV1
 */
abstract class AbstractStarCitizenWikiRepository extends AbstractBaseRepository
{
    /**
     * BaseStarCitizenWikiAPI constructor.
     */
    public function __construct()
    {
        $this->apiUrl = config('api.wiki_url');

        parent::__construct();
    }

    /**
     * JSON aus Interfaces enthält (bis jetzt) immer ein success field
     *
     * @return bool
     */
    protected function checkIfResponseDataIsValid(): bool
    {
        if (!empty($this->response->getHeader('MediaWiki-Interfaces-Error'))) {
            app('Log')::warning('Response Data is not valid', ['response' => (string) $this->response->getBody()]);

            return false;
        }

        return true;
    }
}
