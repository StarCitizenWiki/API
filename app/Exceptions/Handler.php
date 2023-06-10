<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Class Handler
 */
class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param Throwable $exception The thrown Exception
     *
     * @return void
     *
     * @throws Exception|Throwable
     */
    public function report(Throwable $exception): void
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request   The HTTP Request
     * @param Throwable               $exception The Exception to render
     *
     * @return \Illuminate\Http\Response | \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $exception)
    {
        if ($request->is('api*')) {
            return $this->returnApiResponse($request, $exception);
        }

        return parent::render($request, $exception);
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

        return redirect()->guest('login');
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function wantsJson($request): bool
    {
        return $request->wantsJson() || $request->query('format', null) === 'json';
    }

    /**
     * Get the status code from the exception.
     *
     * @param Throwable $exception
     * @return int
     */
    protected function getStatusCode(Throwable $exception): int
    {
        $statusCode = null;

        if ($exception instanceof ValidationException) {
            $statusCode = $exception->status;
        } elseif ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
        } else {
            // By default throw 500
            $statusCode = 500;
        }

        // Be extra defensive
        if ($statusCode < 100 || $statusCode > 599) {
            $statusCode = 500;
        }

        return $statusCode;
    }

    private function returnApiResponse($request, Throwable $exception)
    {
        $request->headers->set('Accept', 'application/json');

        if (config('app.debug')) {
            return parent::render($request, $exception);
        }

        return new Response([
            'code' => $this->getStatusCode($exception),
            'message' => $exception->getMessage(),
        ], $this->getStatusCode($exception));
    }
}
