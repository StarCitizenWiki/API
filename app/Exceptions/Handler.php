<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;

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
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if (strpos($request->getUri(), 'api') > -1) {
            return $this->renderExceptionAsJson($request, $exception);
        }
        return parent::render($request, $exception);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest('login');
    }

    /**
     * Render an exception into a JSON response
     *
     * @param $request
     * @param Exception $exception
     * @return SymfonyResponse
     */
    protected function renderExceptionAsJson($request, Exception $exception)
    {
        $exception = $this->prepareException($exception);

        $response = [
            'message' => $exception->getMessage(),
            'error' => $exception->getMessage()
        ];

        if (config('app.debug')) {
            $response['debug'] = [
                'exception' => get_class($exception),
                'trace' => $exception->getTrace()
            ];
        }

        $status = 400;

        switch ($exception) {
            case $exception instanceof ValidationException:
                $response['error'] = $exception->validator->errors()->getMessages();
                break;
            case $exception instanceof AuthenticationException:
                $status = 401;
                break;
            case $this->isHttpException($exception):
                $status = $exception->getStatusCode();
                break;
            default:
                break;
        }

        return response()->json($response, $status);
    }
}
