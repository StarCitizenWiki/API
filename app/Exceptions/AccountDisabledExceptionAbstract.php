<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 21.02.2017
 * Time: 11:35
 */

namespace App\Exceptions;

/**
 * Class UserBlacklistedException
 * Exception to throw if a request is made by an blacklisted user
 */
class AccountDisabledExceptionAbstract extends AbstractBaseException
{
    public const DEFAULT_MESSAGE = 'Account is Disabled.';

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return $this
     */
    public function render($request)
    {
        if (config('app.debug')) {
            return;
        }

        $this->prepareMessage();

        if ($this->wantsJson($request)) {
            return response()->json(
                [
                    'error' => $this->getMessage(),
                ],
                403
            );
        }

        return response()->view('errors.403', ['exception' => $this])->setStatusCode(403);
    }
}
