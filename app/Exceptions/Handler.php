<?php declare(strict_types = 1);

namespace App\Exceptions;

use App\Traits\ChecksIfRequestWantsJsonTrait as ChecksIfRequestWantsJson;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

/**
 * Class Handler
 */
class Handler extends ExceptionHandler
{
    use ChecksIfRequestWantsJson;

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
     * @param \Exception $exception The thrown Exception
     *
     * @return void
     *
     * @throws Exception
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

        if (array_search('admin', $exception->guards()) !== false) {
            $path = 'admin/login';
        } else {
            $path = 'login';
        }

        return redirect()->guest($path);
    }
}
