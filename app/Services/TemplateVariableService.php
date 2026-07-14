<?php

namespace App\Services;

use App\Models\PortalSetting;
use Illuminate\Support\Str;

class TemplateVariableService
{
    /**
     * Return a map of available variables -> description.
     * Extend this list to include DB-backed variables as needed.
     *
     * @return array<string,string>
     */
    public static function getAvailableVariables(): array
    {
        return [
            'first_name' => 'Recipient first name',
            'last_name' => 'Recipient last name',
            'name' => 'Recipient full name',
            'email' => 'Recipient email address',
            'organization' => 'Organization or site name',
            'company' => 'Company name',
            'date' => 'Current date',
            'unsubscribe_url' => 'Link to unsubscribe',
            'reset_link' => 'Password reset link',
            'profile_url' => 'Link to recipient profile',
            'action' => 'Call-to-action label',
            'action_url' => 'Primary action button URL',
            'body' => 'Main email body text',
            'ctaUrl' => 'Secondary CTA URL',
            'dashboard_url' => 'Dashboard link',
            'expires_at' => 'Invitation or link expiry date',
            'greeting' => 'Intro greeting text',
            'incident_id' => 'Incident reference identifier',
            'intro' => 'Introductory paragraph text',
            'login_url' => 'Login or sign in URL',
            'message' => 'Notification or message content',
            'nomination_id' => 'Worker nomination identifier',
            'onboarding_url' => 'Onboarding link',
            'participant_first_name' => 'Participant first name',
            'participant_name' => 'Participant full name',
            'secondaryUrl' => 'Secondary action URL',
            'service_type' => 'Type of service',
            'title' => 'Email title or subject text',
            'url' => 'Generic action URL',
            'user_name' => 'User display name',
            'variable_name' => 'Template variable name placeholder',
            'worker_email' => 'Worker email address',
            'worker_first_name' => 'Worker first name',
            'worker_full_name' => 'Worker full name',
            'worker_name' => 'Worker name',
            'worker_type' => 'Worker role or category',
            'worker_id' => 'Worker identifier',
            'document_type' => 'Compliance document type',
            'expiry_date' => 'Document expiry date',
            'due_date' => 'Due date for action',
            'days_overdue' => 'Number of days overdue',
            'missing_documents' => 'Missing documents list or details',
            'logo' => 'Path or HTML for the portal logo',
            'year' => 'Current year',
        ];
    }

    /**
     * Generate sample values for the requested variables.
     *
     * @param array $variables
     * @return array<string,string>
     */
    public static function sampleValuesFor(array $variables): array
    {
        $customLogoPath = PortalSetting::where('key', 'logo_path')->value('value');
        $logoUrl = ! empty($customLogoPath)
            ? asset('storage/' . ltrim($customLogoPath, '/'))
            : 'https://via.placeholder.com/160x90.png?text=Logo';

        $appName = htmlspecialchars(config('app.name', 'Logo'), ENT_QUOTES, 'UTF-8');

        $defaults = [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'name' => 'Jane Doe',
            'email' => 'jane.doe@example.com',
            'organization' => config('app.name', 'Website Name'),
            'company' => 'Acme Corp',
            'date' => now()->toFormattedDateString(),
            'unsubscribe_url' => url('/unsubscribe'),
            'reset_link' => url('/password/reset'),
            'profile_url' => url('/profile'),
            'action' => 'Take action',
            'action_url' => url('/'),
            'body' => 'Your message goes here.',
            'ctaUrl' => url('/'),
            'dashboard_url' => url('/dashboard'),
            'expires_at' => now()->addDays(7)->toFormattedDateString(),
            'greeting' => 'Hello there,',
            'incident_id' => 'INC-0000',
            'intro' => 'Here is an important update.',
            'login_url' => url('/login'),
            'message' => 'This is an important message.',
            'nomination_id' => 'NOM-0000',
            'onboarding_url' => url('/onboarding'),
            'participant_first_name' => 'Jane',
            'participant_name' => 'Jane Doe',
            'secondaryUrl' => url('/'),
            'service_type' => 'Home Care',
            'title' => 'Important update',
            'url' => url('/'),
            'user_name' => 'Jane Doe',
            'variable_name' => 'variable_name',
            'worker_email' => 'worker@example.com',
            'worker_first_name' => 'John',
            'worker_full_name' => 'John Doe',
            'worker_name' => 'John Doe',
            'worker_type' => 'Support Worker',
            'worker_id' => 'W-1234',
            'document_type' => 'Police Check',
            'expiry_date' => now()->addDays(30)->toFormattedDateString(),
            'due_date' => now()->addDays(7)->toFormattedDateString(),
            'days_overdue' => '1',
            'missing_documents' => 'Proof of identity, First aid certificate',
            'year' => now()->year,
            'logo' => $logoUrl,
        ];

        $samples = [];

        foreach ($variables as $variable) {
            $samples[$variable] = $defaults[$variable] ?? 'Sample '.Str::title(str_replace('_', ' ', $variable));
        }

        return $samples;
    }
}
