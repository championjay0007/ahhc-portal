<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdatePreApprovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === 'participant';
    }

    public function rules(): array
    {
        return [
            'description' => ['nullable', 'string', 'max:2000'],
            'requested_amount' => ['nullable', 'numeric', 'min:0.01'],
            'requested_amount_cents' => ['nullable', 'integer', 'min:1'],
            'quote' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'participant_note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
