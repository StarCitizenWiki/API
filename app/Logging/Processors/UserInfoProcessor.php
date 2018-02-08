<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 17.07.2017
 * Time: 16:54
 */

namespace App\Logging\Processors;

/**
 * Class UserProcessor
 *
 * @package App\Processors
 */
class UserInfoProcessor
{
    /**
     * @param array $record
     *
     * @return array
     */
    public function __invoke(array $record): array
    {
        $auth = app('Auth');
        $userData = [
            'id'   => '',
            'name' => '',
        ];

        $userData['name'] = $auth::user()->name ?? $auth::user()->email ?? null;

        if (is_null($userData['name'])) {
            if (app()->environment(['local', 'testing'])) {
                $userData['name'] = 'localhost';
            } elseif (app()->environment('production')) {
                $userData['name'] = 'Visitor';
            }
        }

        if ('localhost' !== $userData['name']) {
            $userData['id'] = $auth::user()->id;
        } else {
            unset($userData['id']);
        }

        $record['extra']['user'] = $userData;

        return $record;
    }
}
