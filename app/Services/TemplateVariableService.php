<?php

namespace App\Services;

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
        ];

        $samples = [];

        foreach ($variables as $variable) {
            $samples[$variable] = $defaults[$variable] ?? 'Sample '.Str::title(str_replace('_', ' ', $variable));
        }

        return $samples;
    }
}
