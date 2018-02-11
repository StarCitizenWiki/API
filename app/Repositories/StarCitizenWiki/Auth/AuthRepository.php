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
 */
class AuthRepository extends AbstractStarCitizenWikiRepository implements AuthRepositoryInterface
{
    /**
     * @param string $username
     * @param string $password
     *
     * @return bool
     *
     * @throws \App\Exceptions\InvalidDataException
     */
    public function authenticateUsingCredentials($username, $password): bool
    {
        $this->request(
            'POST',
            '/api.php?action=verifyuser&format=json',
            [
                'form_params' => [
                    'username' => $username,
                    'password' => $password,
                ],
            ]
        );

        $response = json_decode((string) $this->response->getBody(), true);

        if (!is_null($response) && array_key_exists('status', $response) && 200 === (int) $response['status']) {
            return true;
        }

        return false;
    }
}
