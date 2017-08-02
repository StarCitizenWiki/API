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
 * @package App\Repositories\StarCitizenWiki\APIv1
 */
class BaseStarCitizenWikiRepository extends AbstractBaseRepository
{
    const URL = 'https://star-citizen.wiki/';
    const API_URL = BaseStarCitizenWikiRepository::URL.'api.php';

    /**
     * BaseStarCitizenWikiAPI constructor.
     */
    public function __construct()
    {
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
