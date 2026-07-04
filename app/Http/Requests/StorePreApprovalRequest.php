<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StorePreApprovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === 'participant';
    }

    public function rules(): array
    {
        return [
            'service_type' => ['required_without:service_category', 'string', 'max:100'],
            'service_category' => ['required_without:service_type', 'string', 'max:100'],
            'description' => ['required_without:purpose', 'string', 'max:2000'],
            'purpose' => ['required_without:description', 'string', 'max:2000'],
            'requested_amount' => ['required_without:requested_amount_cents', 'numeric', 'min:0.01'],
            'requested_amount_cents' => ['required_without:requested_amount', 'integer', 'min:1'],
            'supplier_id' => ['nullable', 'integer'],
            'start_date' => ['required', 'date', 'before_or_equal:end_date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'quote' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ];
    }
}
