<?php declare(strict_types = 1);

namespace App\Exceptions;

use Carbon\Carbon;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class Handler
 *
 * @package App\Exceptions
 */
class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param \Exception $exception The thrown Exception
     *
     * @return void
     */
    public function report(Exception $exception): void
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request   The HTTP Request
     * @param \Exception               $exception The Exception to render
     *
     * @return \Illuminate\Http\Response | \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Exception $exception)
    {
        if ($this->isApiCall($request) || $request->expectsJson()) {
            return $this->getJsonResponseForException($request, $exception);
        } else {
            return parent::render($request, $exception);
        }
    }

    /**
     * Determines if request is an api call.
     *
     * If the request URI contains '/api/v'.
     *
     * @param \Illuminate\Http\Request $request Request to check
     *
     * @return bool
     */
    protected function isApiCall(Request $request)
    {
        return str_contains($request->getUri(), '/api/v');
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param \Illuminate\Http\Request                 $request   The HTTP Request
     * @param \Illuminate\Auth\AuthenticationException $exception The Auth Exception
     *
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        if (array_search('admin', $exception->guards()) !== false) {
            $path = 'admin/login';
        } else {
            $path = 'login';
        }

        return redirect()->guest($path);
    }

    /**
     * Creates a new JSON response based on exception type.
     *
     * @param \Illuminate\Http\Request $request   Request
     * @param \Exception               $exception Exception to handle
     *
     * @return \Illuminate\Http\Response
     */
    protected function getJsonResponseForException(Request $request, Exception $exception)
    {
        /** @var Exception $exception */
        $exception = $this->prepareException($exception);

        $response = [
            'errors' => [
                'Something went wrong',
            ],
            'meta'   => [
                'status'       => 400,
                'processed_at' => Carbon::now(),
            ],
        ];

        if (config('app.debug')) {
            $response['meta'] += [
                'message'   => $exception->getMessage(),
                'exception' => get_class($exception),
                'trace'     => $exception->getTrace(),
            ];
        }

        switch (true) {
            case $exception instanceof NotFoundHttpException:
            case $exception instanceof ModelNotFoundException:
                $response['meta']['status'] = 404;
                $response['errors'] = ['Resource not found'];
                break;

            case $exception instanceof ValidationException:
                $response['errors'] = $exception->validator->errors()->getMessages();
                break;

            case $exception instanceof AuthenticationException:
                $response['meta']['status'] = 401;
                $response['errors'] = ['No permission'];
                break;

            case $exception instanceof InvalidDataException:
                $response['meta']['status'] = 500;
                $response['errors'] = ['Backend returned bad data'];
                break;

            default:
                break;
        }

        return $this->jsonResponse($response);
    }

    /**
     * Returns json response.
     *
     * @param array|null $payload Payload
     *
     * @return \Illuminate\Http\Response
     */
    protected function jsonResponse(array $payload)
    {
        $payload = $payload ?: [];

        return response()->json(
            $payload,
            $payload['meta']['status'],
            [],
            JSON_PRETTY_PRINT
        );
    }
}
