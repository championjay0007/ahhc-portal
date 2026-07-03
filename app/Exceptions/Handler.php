<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

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
    }
}
