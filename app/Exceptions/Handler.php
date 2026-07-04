<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    protected $levels = [
        // Add custom log levels for specific exception types if needed.
    ];

    protected $dontReport = [
        // Add exceptions that should not be reported here.
    ];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->renderable(function (PostTooLargeException $e, Request $request) {
            if ($request->is('portal/admin/documents') || $request->is('portal/participant/documents')) {
                return Redirect::back()
                    ->withInput($request->except('file'))
                    ->withErrors(['file' => 'Uploaded file is too large. Maximum allowed size is 10MB.']);
            }

            return Redirect::back()
                ->with('error', 'Uploaded content is too large. Please choose a smaller file and try again.');
        });

        $this->renderable(function (AuthorizationException|AccessDeniedHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }

            if (Auth::check()) {
                return redirect()->route('portal.dashboard')
                    ->with('error', 'You do not have access to that page.');
            }

            return redirect()->route('portal.login');
        });

        $this->renderable(function (NotFoundHttpException|ModelNotFoundException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Not found.'], 404);
            }

            if (Auth::check()) {
                return redirect()->route('portal.dashboard')
                    ->with('error', 'The page you requested could not be found.');
            }

            return redirect()->route('public.home');
        });
    }
}
