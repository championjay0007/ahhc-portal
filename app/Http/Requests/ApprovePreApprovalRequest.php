<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ApprovePreApprovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'committed_amount' => ['required', 'numeric', 'min:0'],
            'decision_type' => ['required', 'string', 'in:approve,approve_with_conditions'],
            'condition_notes' => ['nullable', 'string', 'max:2000', 'required_if:decision_type,approve_with_conditions'],
        ];
    }
}
