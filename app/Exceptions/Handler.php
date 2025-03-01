<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
    public function render($request, Throwable $e)
    {
        Log::error('Exception occurred', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'exception' => $e
        ]);

        // // Not Found Exception (Model or Route)
        // if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
        //     return $this->formatErrorResponse('The requested model was not found.', 404);
        // }

        if ($e instanceof AuthorizationException) {
            return $this->formatErrorResponse('You do not have permission to access this resource.', 403);
        }

        if ($e instanceof AuthenticationException) {
            return $this->formatErrorResponse('Unauthenticated, please login.', 401);
        }

        if ($e instanceof \Exception) {
            return $this->formatErrorResponse($e->getMessage(), 500);
        }

        // General Unexpected Errors
        if (config('app.debug')) {
            return parent::render($request, $e); // Default Laravel error page for debugging
        }

        return $this->formatErrorResponse('An unexpected error occurred.', 500);

    }
     protected function formatErrorResponse(string $message, int $statusCode, $errors = null)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }
}
