<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 03.03.2017
 * Time: 18:16
 */

namespace App\Repositories\StarCitizenWiki\Auth;

use App\Repositories\StarCitizenWiki\AbstractStarCitizenWikiRepository;
use App\Repositories\StarCitizenWiki\Interfaces\AuthRepositoryInterface;

/**
 * Class ShipsRepository
 * @package App\Repositories\StarCitizenWiki\ApiV1\Ships
 */
class AuthRepository extends AbstractStarCitizenWikiRepository implements AuthRepositoryInterface
{
    /**
     * AuthRepository constructor.
     */
    public function __construct()
    {
        $this->apiUrl = config('api.wiki_url').'?action=verifyuser&format=json';

        parent::__construct();
    }

    /**
     * @param string $username
     * @param string $password
     *
     * @return bool
     * @throws \App\Exceptions\InvalidDataException
     */
    public function authenticateUsingCredentials($username, $password): bool
    {
        $this->request(
            'POST',
            '',
            [
                'form_params' => [
                    'username' => $username,
                    'password' => $password,
                ],
            ]
        );

        $response = json_decode((string) $this->response->getBody(), true);

        if (!is_null($response) && array_key_exists('status', $response) && 200 == $response['status']) {
            return true;
        }

        return false;
    }
}
