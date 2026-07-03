<?php

namespace App\Http\Controllers;

use App\Models\ParticipantApplication;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    /**
     * Show public application form
     */
    public function show(): View
    {
        return view('public.application.form', [
            'disabilityCategories' => [
                'physical_disability' => 'Physical Disability',
                'sensory_disability' => 'Sensory Disability',
                'intellectual_disability' => 'Intellectual Disability',
                'psychosocial_disability' => 'Psychosocial Disability',
                'multiple_disabilities' => 'Multiple Disabilities',
            ],
            'fundingSources' => [
                'ndis' => 'NDIS',
                'state_funded' => 'State Funded',
                'private' => 'Private',
                'other' => 'Other',
            ],
        ]);
    }

    /**
     * Submit public application
     */
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:participant_applications,email'],
            'phone' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:50'],
            'postcode' => ['required', 'string', 'max:20'],
            'disability_category' => ['nullable', 'string', 'max:100'],
            'support_needs' => ['nullable', 'string', 'max:1000'],
            'funding_source' => ['nullable', 'string', 'max:100'],
        ]);

        $application = ParticipantApplication::create([
            ...$validated,
            'status' => 'new_application',
            'submitted_at' => now(),
        ]);

        // TODO: Send notification to admin
        // TODO: Log audit trail

        return redirect()->route('public.application.success')
            ->with('message', 'Your application has been submitted successfully. You will receive an email from AHHC shortly.');
    }

    /**
     * Show success page
     */
    public function success(): View
    {
        return view('public.application.success');
    }
}
