<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 03.03.2017
 * Time: 18:16
 */

namespace App\Repositories\StarCitizenWiki\Auth;

use App\Repositories\StarCitizenWiki\AbstractStarCitizenWikiRepository;
use App\Repositories\StarCitizenWiki\Interfaces\AuthRepositoryInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;

/**
 * Class ShipsRepository
 * @package App\Repositories\StarCitizenWiki\ApiV1\Ships
 */
class AuthRepository extends AbstractStarCitizenWikiRepository implements AuthRepositoryInterface
{
    const API_URI = 'api.php?action=usercheck&format=json';

    /**
     * @param string $username
     * @param string $password
     *
     * @return bool
     */
    public function authenticateUsingCredentials($username, $password): bool
    {
        try {
            $response = $this->request(
                'POST',
                self::API_URI,
                [
                    'form_params' => [
                        'username' => $username,
                        'password' => $password,
                    ],
                ]
            );
        } catch (ConnectException | RequestException $e) {
            return false;
        }

        $response = json_decode($response->getBody()->getContents(), true);

        if (!is_null($response) && array_key_exists('usercheck', $response) && 'ok' === $response['usercheck']['status']) {
            return true;
        }

        return false;
    }
}
