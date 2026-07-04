<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

        // Handle generic HTTP exceptions (403, 404) to provide friendlier redirects
        $this->renderable(function (HttpExceptionInterface $e, Request $request) {
            $status = $e->getStatusCode();

            // Log details for remote debugging
            $user = Auth::user();
            $context = [
                'status' => $status,
                'path' => $request->path(),
                'method' => $request->method(),
                'user_id' => $user?->id,
                'user_role' => $user?->role,
                'ip' => $request->ip(),
            ];

            Log::warning('HTTP exception intercepted', $context);

            if ($status === 403) {
                // If admin or system_admin, send to admin dashboard instead of showing 403
                if ($user && in_array($user->role, ['admin', 'system_admin'], true)) {
                    return redirect()->route('portal.dashboard');
                }

                // If authenticated participant or worker, redirect to their dashboard
                if ($user) {
                    return redirect()->route('portal.dashboard')
                        ->withErrors(['access' => 'You do not have permission to access that page.']);
                }

                // Unauthenticated: redirect to login
                return redirect()->route('portal.login')
                    ->withErrors(['access' => 'Please sign in to access that page.']);
            }

            if ($status === 404) {
                // Redirect authenticated users to dashboard on missing pages to reduce dead-ends
                if ($user) {
                    return redirect()->route('portal.dashboard')
                        ->with('status', 'Page not found; redirected to your dashboard.');
                }

                // For guests, redirect to public home
                return redirect()->route('public.home');
            }

            return null; // let default handling proceed for other statuses
        });
    }
}
