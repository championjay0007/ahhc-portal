@component('mail::message')
# You've Been Nominated!

Hello {{ $nomination->worker_full_name }},

You have been nominated to join the **Allegiance Heart &amp; Home Care Care Portal** as a care worker or service provider.

## Next Steps

1. **Create Your Account** - You'll need to register on the Allegiance Heart &amp; Home Care Care Portal
2. **Complete Compliance** - Upload required documents (Working With Children Check, insurance, etc.)
3. **Sign Agreements** - Review and sign service agreements

## Your Assignment

**Participant:** {{ $nomination->participant->first_name }} {{ $nomination->participant->last_name }}  
**Service Type:** {{ $nomination->service_type }}  
**Proposed Start Date:** {{ $nomination->start_date?->format('d M Y') ?? 'TBA' }}

## Important Information

- You CANNOT access the portal until you complete all compliance requirements
- Your access will be managed by Allegiance Heart &amp; Home Care administrators
- You will NOT have access to participant budgets, funding information, or other confidential data

## Get Started

If you have been invited by Allegiance Heart &amp; Home Care, please wait for your worker onboarding invitation email with your secure portal access link. If you have not received it, contact our support team.

If you need general information, visit the public website:

@component('mail::button', ['url' => route('public.home')])
Visit Allegiance Heart &amp; Home Care Portal Website
@endcomponent

If you have any questions or need assistance, please contact our support team.

---

**Allegiance Heart &amp; Home Care Care Portal**
{{ config('app.name') }}
@endcomponent
