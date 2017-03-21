<?php
/**
 * User: Hannes
 * Date: 21.03.2017
 * Time: 14:38
 */

namespace App\Traits;

use Carbon\Carbon;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

trait RestExceptionHandlerTrait
{

    /**
     * Creates a new JSON response based on exception type.
     *
     * @param Request $request
     * @param Exception $exception
     * @return \Illuminate\Http\Response
     */
    protected function getJsonResponseForException(Request $request, Exception $exception)
    {
        $exception = $this->prepareException($exception);

        $response = [
            'errors' => [
                $exception->getMessage()
            ],
            'meta' => [
                'status' => 400,
                'processed_at' => Carbon::now()
            ]
        ];

        if (config('app.debug')) {
            $response['meta'] += [
                'exception' => get_class($exception),
                'trace' => $exception->getTrace()
            ];
        }

        switch(true) {
            case $exception instanceof ModelNotFoundException:
                $response['meta']['status'] = 404;
                break;

            case $exception instanceof ValidationException:
                $response['errors'] = $exception->validator->errors()->getMessages();
                break;

            case $exception instanceof AuthenticationException:
                $response['meta']['status'] = 401;
                break;

            default:
                break;
        }

        return $this->jsonResponse($response);
    }

    /**
     * Returns json response.
     *
     * @param array|null $payload
     * @return \Illuminate\Http\Response
     */
    protected function jsonResponse(array $payload)
    {
        $payload = $payload ?: [];

        return response()->json($payload, $payload['meta']['status']);
    }

}