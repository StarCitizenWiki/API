<?php declare(strict_types = 1);

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

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
     * @param \Throwable $exception The thrown Exception
     *
     * @return void
     *
     * @throws Exception
     */
    public function report(\Throwable $exception): void
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request   The HTTP Request
     * @param \Throwable               $exception The Exception to render
     *
     * @return \Illuminate\Http\Response | \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, \Throwable $exception)
    {
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
}
