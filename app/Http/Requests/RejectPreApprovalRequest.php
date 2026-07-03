<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RejectPreApprovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'decision_reason' => ['required', 'string', 'max:2000'],
        ];
    }
}
