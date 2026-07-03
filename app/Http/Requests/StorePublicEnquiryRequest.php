<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePublicEnquiryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'role' => ['required', 'string', 'in:participant,family_member,representative,support_coordinator,worker,other'],
            'support_status' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
            'consent' => ['required', 'accepted'],
        ];
    }

    public function attributes()
    {
        return [
            'support_status' => 'support at home status',
            'consent' => 'consent to contact',
        ];
    }
}
