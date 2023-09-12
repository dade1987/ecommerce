<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Http\Request;
use Spatie\LaravelIgnition\Exceptions\ViewException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
        });

        $this->renderable(function (Throwable $e) {
            if ($e instanceof ViewException) {
                abort(404);
            }
            if ($e instanceof ModelNotFoundException) {
                abort(404);
            }
        });
    }
}
