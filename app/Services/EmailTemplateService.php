<?php

namespace App\Services;

use App\Models\EmailTemplate;
use App\Models\PortalSetting;
use Illuminate\Support\Str;

class EmailTemplateService
{
    public static function normalizeSlug(string $slug): string
    {
        return Str::slug($slug, '-');
    }

    public static function useDatabaseTemplates(): bool
    {
        $source = PortalSetting::query()->where('key', 'email_template_source')->value('value');
        $source = is_string($source) ? strtolower($source) : 'database';

        return $source === 'database';
    }

    public static function defaultHtmlFromText(string $text, ?string $url = null): string
    {
        $html = '<div style="font-family: system-ui, sans-serif; color: #111827; line-height: 1.7; padding: 1rem;">'.nl2br(e($text)).'</div>';

        if ($url) {
            $html .= '<div style="padding: 0 1rem 1rem;">'
                .'<a href="'.e($url).'" style="display: inline-block; background: #0d6efd; color: #ffffff; text-decoration: none; padding: 10px 16px; border-radius: 8px;">View details</a>'
                .'</div>';
        }

        return $html;
    }

    public static function renderTemplate(
        string $slug,
        array $variables,
        string $defaultSubject,
        string $defaultHtml,
        ?string $defaultText = null,
        ?string $name = null,
        ?string $category = null
    ): array {
        $slug = self::normalizeSlug($slug);

        self::ensureBuiltInTemplates();

        if (! self::useDatabaseTemplates()) {
            return [
                'subject' => $defaultSubject,
                'html' => $defaultHtml,
                'text' => $defaultText ?? strip_tags($defaultHtml),
            ];
        }

        try {
            $template = EmailTemplate::where('slug', $slug)->first();

            if (! $template) {
                $template = EmailTemplate::create([
                    'name' => $name ?? Str::title(str_replace(['-', '_'], ' ', $slug)),
                    'slug' => $slug,
                    'subject' => $defaultSubject,
                    'html_body' => $defaultHtml,
                    'text_body' => $defaultText ?? strip_tags($defaultHtml),
                    'category' => $category ?? 'Automated Emails',
                    'is_active' => true,
                ]);
            }

            if (! $template->is_active) {
                return [
                    'subject' => $defaultSubject,
                    'html' => $defaultHtml,
                    'text' => $defaultText ?? strip_tags($defaultHtml),
                ];
            }

            return $template->render($variables);
        } catch (\Throwable $e) {
            return [
                'subject' => $defaultSubject,
                'html' => $defaultHtml,
                'text' => $defaultText ?? strip_tags($defaultHtml),
            ];
        }
    }

    public static function ensureBuiltInTemplates(): void
    {
        foreach (self::getBuiltInTemplateDefinitions() as $definition) {
            $template = EmailTemplate::where('slug', $definition['slug'])->first();

            $htmlBody = self::resolveBuiltInTemplateHtml($definition);
            $textBody = $definition['text'] ?? self::convertHtmlToText($htmlBody);

            $payload = [
                'name' => $definition['name'],
                'slug' => $definition['slug'],
                'subject' => $definition['subject'],
                'html_body' => $htmlBody,
                'text_body' => $textBody,
                'category' => $definition['category'],
                'is_active' => true,
            ];

            if ($template) {
                $needsRefresh = empty(trim((string) $template->html_body)) || empty(trim((string) $template->text_body));
                if ($needsRefresh) {
                    $template->fill($payload);
                    $template->save();
                }

                continue;
            }

            EmailTemplate::create($payload);
        }
    }

    /*

    public static function getBuiltInTemplateDefinitions(): array
    {
        return [
            [
                'name' => 'Participant Onboarding Invitation',
                'slug' => 'participant-onboarding-invitation',
                'subject' => 'Complete your AHHC portal onboarding',
                'category' => 'Onboarding',
                'view_path' => resource_path('views/mail/participant-onboarding-invitation.blade.php'),
                'sample_data' => [
                    'participant' => (object) [
                        'first_name' => 'Jane',
                        'onboarding_token' => 'sample-token',
                        'onboarding_expires_at' => now()->addDays(7),
                    ],
                ],
            ],
            [
                'name' => 'Worker Onboarding Invitation',
                'slug' => 'worker-onboarding-invitation',
                'subject' => 'AHHC Portal - Worker Onboarding Invitation',
                'category' => 'Onboarding',
                'view_path' => resource_path('views/mail/worker_onboarding_invitation.blade.php'),
                'sample_data' => [
                    'worker' => (object) [
                        'first_name' => 'John',
                        'worker_number' => 'W-1001',
                        'email' => 'john@example.com',
                        'phone' => '555-0100',
                    ],
                    'onboardingUrl' => url('/onboarding'),
                    'expiresAt' => now()->addDays(7),
                ],
            ],
            [
                'name' => 'Worker Nomination Invitation',
                'slug' => 'worker-nomination-invitation',
                'subject' => 'You have been nominated to join AHHC Care Portal',
                'category' => 'Onboarding',
                'view_path' => resource_path('views/mail/worker-nomination-invitation.blade.php'),
                'sample_data' => [
                    'nomination' => (object) [
                        'worker_full_name' => 'John Smith',
                        'service_type' => 'Home Care',
                        'start_date' => now()->addDays(10),
                        'participant' => (object) [
                            'first_name' => 'Sarah',
                            'last_name' => 'Brown',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Portal Test Email',
                'slug' => 'portal-test-email',
                'subject' => '{{organization}} — Test Email',
                'category' => 'System',
                'view_path' => resource_path('views/emails/portal_test.blade.php'),
                'sample_data' => [
                    'settings' => [
                        'website_name' => 'AHHC Portal',
                    ],
                ],
            ],
            [
                'name' => 'Worker Nomination Submitted',
                'slug' => 'worker-nomination-submitted',
                'subject' => 'New Worker Nomination Submitted (#{{nomination_id}})',
                'category' => 'Nominations',
                'html' => '<div style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', sans-serif; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); padding: 40px 20px;"><table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto;"><tr><td style="background: white; border-radius: 16px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);"><div style="border-left: 4px solid #0d6efd; padding-left: 16px; margin-bottom: 24px;"><h2 style="color: #1e293b; margin: 0 0 8px; font-size: 24px; font-weight: 700;">New Nomination Received</h2><p style="color: #64748b; margin: 0; font-size: 14px;">Nomination #{{nomination_id}}</p></div><p style="color: #475569; margin: 0 0 20px; line-height: 1.6;">A new worker nomination has been submitted by <strong>{{participant_name}}</strong>.</p><div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin: 24px 0;"><h4 style="color: #1e293b; margin: 0 0 16px; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Worker Details</h4><table width="100%" style="color: #475569; font-size: 14px;"><tr style="border-bottom: 1px solid #e2e8f0;"><td style="padding: 12px 0;"><strong>Name:</strong></td><td style="text-align: right;">{{worker_full_name}}</td></tr><tr style="border-bottom: 1px solid #e2e8f0;"><td style="padding: 12px 0;"><strong>Email:</strong></td><td style="text-align: right;">{{worker_email}}</td></tr><tr style="border-bottom: 1px solid #e2e8f0;"><td style="padding: 12px 0;"><strong>Phone:</strong></td><td style="text-align: right;">{{worker_phone}}</td></tr><tr style="border-bottom: 1px solid #e2e8f0;"><td style="padding: 12px 0;"><strong>Type:</strong></td><td style="text-align: right;">{{worker_type}}</td></tr><tr><td style="padding: 12px 0;"><strong>Service:</strong></td><td style="text-align: right;">{{service_type}}</td></tr></table></div><p style="color: #64748b; margin: 20px 0; line-height: 1.6;">Please review this nomination and take appropriate action.</p><div style="text-align: center; margin: 32px 0;"><a href="{{action_url}}" style="display: inline-block; padding: 12px 32px; background: linear-gradient(135deg, #0d6efd 0%, #0056b3 100%); color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px;">Review Nomination</a></div><div style="border-top: 1px solid #e2e8f0; padding-top: 20px; margin-top: 32px;"><p style="color: #94a3b8; margin: 0; font-size: 12px; text-align: center;">This is an automated message from AHHC Portal. Please do not reply to this email.</p></div></td></tr></table></div>',
            ],
            [
                'name' => 'Worker Nomination Approved',
                'slug' => 'worker-nomination-approved',
                'subject' => 'Worker Nomination Approved - {{worker_full_name}}',
                'category' => 'Nominations',
                'html' => '<div style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', sans-serif; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); padding: 40px 20px;"><table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto;"><tr><td style="background: white; border-radius: 16px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);"><div style="text-align: center; margin-bottom: 24px;"><div style="display: inline-block; width: 64px; height: 64px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 50%; line-height: 64px; color: white; font-size: 32px;">✓</div></div><h2 style="color: #059669; text-align: center; margin: 0 0 8px; font-size: 28px; font-weight: 700;">Nomination Approved!</h2><p style="color: #64748b; text-align: center; margin: 0 0 24px; font-size: 16px;">Great news! Your nomination has been accepted.</p><div style="background: linear-gradient(135deg, #f0fdf4 0%, #dbeafe 100%); border-left: 4px solid #10b981; border-radius: 8px; padding: 20px; margin: 24px 0;"><p style="color: #475569; margin: 0 0 12px; line-height: 1.6;"><strong>Nomination Details:</strong></p><table width="100%" style="color: #475569; font-size: 14px;"><tr style="border-bottom: 1px solid #e2e8f0;"><td style="padding: 8px 0;"><strong>Worker Name:</strong></td><td style="text-align: right;">{{worker_full_name}}</td></tr><tr style="border-bottom: 1px solid #e2e8f0;"><td style="padding: 8px 0;"><strong>Service Type:</strong></td><td style="text-align: right;">{{service_type}}</td></tr><tr><td style="padding: 8px 0;"><strong>Status:</strong></td><td style="text-align: right;"><span style="background: #dcfce7; color: #065f46; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">Approved</span></td></tr></table></div><p style="color: #475569; margin: 20px 0; line-height: 1.6;">The next step is to invite the worker to join. AHHC will send an invitation to <strong>{{worker_email}}</strong>.</p><div style="text-align: center; margin: 32px 0;"><a href="{{action_url}}" style="display: inline-block; padding: 12px 32px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px;">View Nomination</a></div><div style="border-top: 1px solid #e2e8f0; padding-top: 20px; margin-top: 32px;"><p style="color: #94a3b8; margin: 0; font-size: 12px; text-align: center;">This is an automated message from AHHC Portal. Please do not reply to this email.</p></div></td></tr></table></div>',
            ],
            [
                'name' => 'Worker Nomination Rejected',
                'slug' => 'worker-nomination-rejected',
                'subject' => 'Worker Nomination - Unable to Proceed - {{worker_full_name}}',
                'category' => 'Nominations',
                'html' => '<div style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', sans-serif; background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); padding: 40px 20px;"><table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto;"><tr><td style="background: white; border-radius: 16px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);"><div style="text-align: center; margin-bottom: 24px;"><div style="display: inline-block; width: 64px; height: 64px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border-radius: 50%; line-height: 64px; color: white; font-size: 32px;">✕</div></div><h2 style="color: #dc2626; text-align: center; margin: 0 0 8px; font-size: 28px; font-weight: 700;">Unable to Proceed</h2><p style="color: #64748b; text-align: center; margin: 0 0 24px; font-size: 16px;">Your nomination could not be approved at this time.</p><div style="background: linear-gradient(135deg, #fef2f2 0%, #fecaca 100%); border-left: 4px solid #ef4444; border-radius: 8px; padding: 20px; margin: 24px 0;"><p style="color: #475569; margin: 0 0 12px; line-height: 1.6;"><strong>Nomination Details:</strong></p><table width="100%" style="color: #475569; font-size: 14px;"><tr style="border-bottom: 1px solid #fecaca;"><td style="padding: 8px 0;"><strong>Worker Name:</strong></td><td style="text-align: right;">{{worker_full_name}}</td></tr><tr style="border-bottom: 1px solid #fecaca;"><td style="padding: 8px 0;"><strong>Service Type:</strong></td><td style="text-align: right;">{{service_type}}</td></tr><tr><td style="padding: 8px 0;"><strong>Status:</strong></td><td style="text-align: right;"><span style="background: #fecaca; color: #7f1d1d; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">Rejected</span></td></tr></table></div><div style="background: #faf5ff; border: 1px solid #f3e8ff; border-radius: 12px; padding: 20px; margin: 20px 0;"><h4 style="color: #6d28d9; margin: 0 0 12px; font-size: 14px; font-weight: 600;">Reason for Rejection:</h4><p style="color: #5b21b6; margin: 0; line-height: 1.6;">{{rejection_reason}}</p></div><p style="color: #64748b; margin: 20px 0; line-height: 1.6;">If you have any questions or would like to discuss this decision, please contact AHHC support.</p><div style="text-align: center; margin: 32px 0;"><a href="{{action_url}}" style="display: inline-block; padding: 12px 32px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px;">View Details</a></div><div style="border-top: 1px solid #e2e8f0; padding-top: 20px; margin-top: 32px;"><p style="color: #94a3b8; margin: 0; font-size: 12px; text-align: center;">This is an automated message from AHHC Portal. Please do not reply to this email.</p></div></td></tr></table></div>',
            ],
            [
                'name' => 'Worker Invitation Sent',
                'slug' => 'worker-invitation-sent',
                'subject' => 'Worker Invitation Sent - {{worker_full_name}}',
                'category' => 'Nominations',
                'html' => '<div style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', sans-serif; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); padding: 40px 20px;"><table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto;"><tr><td style="background: white; border-radius: 16px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);"><div style="text-align: center; margin-bottom: 24px;"><div style="display: inline-block; width: 64px; height: 64px; background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); border-radius: 50%; line-height: 64px; color: white; font-size: 32px;">✉</div></div><h2 style="color: #0284c7; text-align: center; margin: 0 0 8px; font-size: 28px; font-weight: 700;">Invitation Sent</h2><p style="color: #64748b; text-align: center; margin: 0 0 24px; font-size: 16px;">The worker invitation has been successfully sent.</p><div style="background: #f0f9ff; border-left: 4px solid #0ea5e9; border-radius: 8px; padding: 20px; margin: 24px 0;"><p style="color: #475569; margin: 0 0 12px; line-height: 1.6;"><strong>Invitation Details:</strong></p><table width="100%" style="color: #475569; font-size: 14px;"><tr style="border-bottom: 1px solid #e0f2fe;"><td style="padding: 8px 0;"><strong>Worker Name:</strong></td><td style="text-align: right;">{{worker_full_name}}</td></tr><tr style="border-bottom: 1px solid #e0f2fe;"><td style="padding: 8px 0;"><strong>Email Address:</strong></td><td style="text-align: right;">{{worker_email}}</td></tr><tr style="border-bottom: 1px solid #e0f2fe;"><td style="padding: 8px 0;"><strong>Service Type:</strong></td><td style="text-align: right;">{{service_type}}</td></tr><tr><td style="padding: 8px 0;"><strong>Status:</strong></td><td style="text-align: right;"><span style="background: #e0f2fe; color: #0c4a6e; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">Sent</span></td></tr></table></div><div style="background: #f0fdf4; border-left: 4px solid #10b981; border-radius: 8px; padding: 16px; margin: 20px 0;"><p style="color: #166534; margin: 0; font-size: 14px;"><strong>Next Step:</strong> The worker will receive an invitation email with instructions to join the AHHC platform.</p></div><div style="text-align: center; margin: 32px 0;"><a href="{{action_url}}" style="display: inline-block; padding: 12px 32px; background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px;">View Nomination</a></div><div style="border-top: 1px solid #e2e8f0; padding-top: 20px; margin-top: 32px;"><p style="color: #94a3b8; margin: 0; font-size: 12px; text-align: center;">This is an automated message from AHHC Portal. Please do not reply to this email.</p></div></td></tr></table></div>',
            ],
            [
                'name' => 'Worker Missing Compliance Documents',
                'slug' => 'worker-missing-compliance-documents',
                'subject' => 'Worker Missing Compliance Documents - {{worker_first_name}} {{worker_last_name}}',
                'category' => 'Compliance',
                'html' => '<div style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', sans-serif; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); padding: 40px 20px;"><table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto;"><tr><td style="background: white; border-radius: 16px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);"><div style="border-left: 4px solid #f59e0b; padding-left: 16px; margin-bottom: 24px;"><h2 style="color: #d97706; margin: 0 0 8px; font-size: 24px; font-weight: 700;">Missing Documents</h2><p style="color: #92400e; margin: 0; font-size: 14px;">Action Required for {{worker_first_name}} {{worker_last_name}}</p></div><p style="color: #475569; margin: 0 0 20px; line-height: 1.6;">The following compliance documents are <strong>missing</strong> for this worker:</p><div style="background: #fffbeb; border: 1px solid #fcd34d; border-radius: 12px; padding: 20px; margin: 24px 0;"><ul style="color: #92400e; margin: 0; padding-left: 20px;"><li style="margin-bottom: 8px;">{{missing_documents}}</li></ul></div><div style="background: #f5f3ff; border-left: 4px solid #d97706; border-radius: 8px; padding: 16px; margin: 20px 0;"><p style="color: #744210; margin: 0; font-size: 14px;"><strong>Important:</strong> These documents are required before the worker can be assigned to tasks.</p></div><div style="text-align: center; margin: 32px 0;"><a href="{{action_url}}" style="display: inline-block; padding: 12px 32px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px;">Review Compliance</a></div><div style="border-top: 1px solid #e2e8f0; padding-top: 20px; margin-top: 32px;"><p style="color: #94a3b8; margin: 0; font-size: 12px; text-align: center;">This is an automated message from AHHC Portal. Please do not reply to this email.</p></div></td></tr></table></div>',
            ],
            ],
            [
                'name' => 'Incident Reported',
                'slug' => 'incident-reported',
                'subject' => 'High severity incident reported (#{{incident_id}})',
                'category' => 'Alerts',
                'html' => '<div style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', sans-serif; background: linear-gradient(135deg, #7f1d1d 0%, #991b1b 100%); padding: 40px 20px;"><table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto;"><tr><td style="background: white; border-radius: 16px; padding: 40px; box-shadow: 0 20px 40px rgba(0,0,0,0.2);"><div style="text-align: center; margin-bottom: 24px;"><div style="display: inline-block; width: 64px; height: 64px; background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); border-radius: 50%; line-height: 64px; color: white; font-size: 32px;">!</div></div><h2 style="color: #dc2626; text-align: center; margin: 0 0 8px; font-size: 28px; font-weight: 700;">INCIDENT ALERT</h2><p style="color: #64748b; text-align: center; margin: 0 0 24px; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">High Severity - Immediate Action Required</p><div style="background: #fef2f2; border-left: 4px solid #dc2626; border-radius: 8px; padding: 20px; margin: 24px 0;"><p style="color: #475569; margin: 0 0 16px; line-height: 1.6;">An incident has been reported for participant: <strong style="color: #dc2626;">{{participant_first_name}}</strong></p><table width="100%" style="color: #475569; font-size: 14px;"><tr style="border-bottom: 1px solid #fee2e2;"><td style="padding: 12px 0;"><strong>Incident ID:</strong></td><td style="text-align: right;">{{incident_id}}</td></tr><tr style="border-bottom: 1px solid #fee2e2;"><td style="padding: 12px 0;"><strong>Type:</strong></td><td style="text-align: right;">{{incident_type}}</td></tr><tr style="border-bottom: 1px solid #fee2e2;"><td style="padding: 12px 0;"><strong>Severity:</strong></td><td style="text-align: right;"><span style="background: #fee2e2; color: #7f1d1d; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">{{severity}}</span></td></tr><tr><td style="padding: 12px 0; vertical-align: top;"><strong>Description:</strong></td><td style="text-align: right; color: #1e293b;">{{description}}</td></tr></table></div><p style="color: #64748b; margin: 20px 0; line-height: 1.6;">Please review this incident immediately and take appropriate action.</p><div style="text-align: center; margin: 32px 0;"><a href="{{action_url}}" style="display: inline-block; padding: 12px 32px; background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px;">VIEW INCIDENT</a></div><div style="border-top: 1px solid #e2e8f0; padding-top: 20px; margin-top: 32px;"><p style="color: #94a3b8; margin: 0; font-size: 12px; text-align: center;">This is an urgent automated message from AHHC Portal. Please do not reply to this email.</p></div></td></tr></table></div>',
            ],
            ],
            [
                'name' => 'Compliance Document Expiring Reminder - 30 Days',
                'slug' => 'compliance-document-expiring-reminder-30-days',
                'subject' => 'Worker Compliance Reminder: {{document_type}} Expiring',
                'category' => 'Compliance',
                'html' => '<div style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', sans-serif; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); padding: 40px 20px;"><table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto;"><tr><td style="background: white; border-radius: 16px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);"><div style="border-left: 4px solid #3b82f6; padding-left: 16px; margin-bottom: 24px;"><h2 style="color: #1e40af; margin: 0 0 8px; font-size: 24px; font-weight: 700;">Compliance Document Expiring</h2><p style="color: #3b82f6; margin: 0; font-size: 14px;">30-Day Reminder • {{document_type}}</p></div><p style="color: #475569; margin: 0 0 20px; line-height: 1.6;">Reminder: The <strong>{{document_type}}</strong> for worker <strong>{{worker_first_name}} {{worker_last_name}}</strong> will expire in <strong>30 days</strong>.</p><div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; padding: 20px; margin: 24px 0;"><h4 style="color: #1e40af; margin: 0 0 12px; font-size: 14px; font-weight: 600;">Expiration Details:</h4><table width="100%" style="color: #475569; font-size: 14px;"><tr style="border-bottom: 1px solid #bfdbfe;"><td style="padding: 8px 0;"><strong>Document Type:</strong></td><td style="text-align: right;">{{document_type}}</td></tr><tr><td style="padding: 8px 0;"><strong>Expiry Date:</strong></td><td style="text-align: right;"><span style="background: #fef3c7; color: #92400e; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">{{expiry_date}}</span></td></tr></table></div><p style="color: #64748b; margin: 20px 0; line-height: 1.6;">Please arrange for renewal before the expiration date to avoid service interruption.</p><div style="text-align: center; margin: 32px 0;"><a href="{{action_url}}" style="display: inline-block; padding: 12px 32px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px;">View Dashboard</a></div><div style="border-top: 1px solid #e2e8f0; padding-top: 20px; margin-top: 32px;"><p style="color: #94a3b8; margin: 0; font-size: 12px; text-align: center;">This is an automated message from AHHC Portal. Please do not reply to this email.</p></div></td></tr></table></div>',
            ],
            ],
            [
                'name' => 'Compliance Document Expiring Reminder - 14 Days',
                'slug' => 'compliance-document-expiring-reminder-14-days',
                'subject' => 'Worker Compliance Reminder: {{document_type}} Expiring',
                'category' => 'Compliance',
                'html' => '<div style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', sans-serif; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); padding: 40px 20px;"><table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto;"><tr><td style="background: white; border-radius: 16px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);"><div style="border-left: 4px solid #f59e0b; padding-left: 16px; margin-bottom: 24px;"><h2 style="color: #d97706; margin: 0 0 8px; font-size: 24px; font-weight: 700;">Compliance Document Expiring</h2><p style="color: #b45309; margin: 0; font-size: 14px;">URGENT: 14-Day Reminder • {{document_type}}</p></div><p style="color: #dc2626; margin: 0 0 20px; line-height: 1.6; font-weight: 600;">URGENT: The <strong>{{document_type}}</strong> for worker <strong>{{worker_first_name}} {{worker_last_name}}</strong> will expire in just <strong>14 days</strong>.</p><div style="background: #fffbeb; border: 1px solid #fcd34d; border-radius: 12px; padding: 20px; margin: 24px 0;"><h4 style="color: #b45309; margin: 0 0 12px; font-size: 14px; font-weight: 600;">Action Required:</h4><table width="100%" style="color: #78350f; font-size: 14px;"><tr style="border-bottom: 1px solid #fcd34d;"><td style="padding: 8px 0;"><strong>Document Type:</strong></td><td style="text-align: right;">{{document_type}}</td></tr><tr style="border-bottom: 1px solid #fcd34d;"><td style="padding: 8px 0;"><strong>Expiry Date:</strong></td><td style="text-align: right;">{{expiry_date}}</td></tr><tr><td style="padding: 8px 0;"><strong>Days Remaining:</strong></td><td style="text-align: right;"><span style="background: #fed7aa; color: #92400e; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">14 Days</span></td></tr></table></div><p style="color: #dc2626; margin: 20px 0; line-height: 1.6; font-weight: 600;">Please prioritize renewal immediately to avoid service interruption.</p><div style="text-align: center; margin: 32px 0;"><a href="{{action_url}}" style="display: inline-block; padding: 12px 32px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px;">View Dashboard</a></div><div style="border-top: 1px solid #e2e8f0; padding-top: 20px; margin-top: 32px;"><p style="color: #94a3b8; margin: 0; font-size: 12px; text-align: center;">This is an automated message from AHHC Portal. Please do not reply to this email.</p></div></td></tr></table></div>',
            ],
            ],
            [
                'name' => 'Compliance Document Expiring Reminder - 7 Days',
                'slug' => 'compliance-document-expiring-reminder-7-days',
                'subject' => 'Worker Compliance Reminder: {{document_type}} Expiring',
                'category' => 'Compliance',
                'html' => '<div style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', sans-serif; background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); padding: 40px 20px;"><table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto;"><tr><td style="background: white; border-radius: 16px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);"><div style="text-align: center; margin-bottom: 24px;"><div style="display: inline-block; width: 64px; height: 64px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border-radius: 50%; line-height: 64px; color: white; font-size: 28px;">!</div></div><h2 style="color: #dc2626; text-align: center; margin: 0 0 8px; font-size: 28px; font-weight: 700;">CRITICAL: 7-DAY WARNING</h2><p style="color: #b91c1c; text-align: center; margin: 0 0 24px; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Document Expiring in 7 Days</p><div style="background: #fef2f2; border-left: 4px solid #dc2626; border-radius: 8px; padding: 20px; margin: 24px 0;"><p style="color: #7f1d1d; margin: 0 0 16px; line-height: 1.6; font-weight: 600;">{{document_type}} for {{worker_first_name}} {{worker_last_name}} expires in only <strong>7 days</strong>!</p><table width="100%" style="color: #7f1d1d; font-size: 14px;"><tr style="border-bottom: 1px solid #fecaca;"><td style="padding: 8px 0;"><strong>Document:</strong></td><td style="text-align: right;">{{document_type}}</td></tr><tr style="border-bottom: 1px solid #fecaca;"><td style="padding: 8px 0;"><strong>Expires:</strong></td><td style="text-align: right;">{{expiry_date}}</td></tr><tr><td style="padding: 8px 0;"><strong>Action:</strong></td><td style="text-align: right;"><span style="background: #fecaca; color: #7f1d1d; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700;">IMMEDIATE RENEWAL REQUIRED</span></td></tr></table></div><p style="color: #dc2626; margin: 20px 0; line-height: 1.6; font-weight: 700; font-size: 16px;">URGENT: Renew immediately to prevent service suspension.</p><div style="text-align: center; margin: 32px 0;"><a href="{{action_url}}" style="display: inline-block; padding: 12px 32px; background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px;">RENEW NOW</a></div><div style="border-top: 1px solid #e2e8f0; padding-top: 20px; margin-top: 32px;"><p style="color: #94a3b8; margin: 0; font-size: 12px; text-align: center;">This is a critical automated message from AHHC Portal. Please do not reply to this email.</p></div></td></tr></table></div>',
            ],
            ],
            [
                'name' => 'Compliance Document Expired',
                'slug' => 'compliance-document-expired',
                'subject' => 'CRITICAL: Worker Compliance Document Expired - {{document_type}}',
                'category' => 'Compliance',
                'html' => '<div style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', sans-serif; background: linear-gradient(135deg, #7f1d1d 0%, #991b1b 100%); padding: 40px 20px;"><table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto;"><tr><td style="background: white; border-radius: 16px; padding: 40px; box-shadow: 0 20px 40px rgba(0,0,0,0.2);"><div style="text-align: center; margin-bottom: 24px;"><div style="display: inline-block; width: 64px; height: 64px; background: linear-gradient(135deg, #991b1b 0%, #7f1d1d 100%); border-radius: 50%; line-height: 64px; color: white; font-size: 32px; font-weight: 700;">✕</div></div><h2 style="color: #7f1d1d; text-align: center; margin: 0 0 8px; font-size: 28px; font-weight: 700;">DOCUMENT EXPIRED</h2><p style="color: #b91c1c; text-align: center; margin: 0 0 24px; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Immediate Action Required</p><div style="background: #fef2f2; border: 2px solid #dc2626; border-radius: 8px; padding: 24px; margin: 24px 0;"><h3 style="color: #7f1d1d; margin: 0 0 16px; font-size: 18px; font-weight: 700;">CRITICAL ALERT</h3><p style="color: #7f1d1d; margin: 0 0 16px; line-height: 1.6; font-weight: 600;">The {{document_type}} for {{worker_first_name}} {{worker_last_name}} has <strong>EXPIRED</strong>.</p><table width="100%" style="color: #7f1d1d; font-size: 14px;"><tr style="border-bottom: 2px solid #fecaca;"><td style="padding: 12px 0;"><strong>Document Type:</strong></td><td style="text-align: right;">{{document_type}}</td></tr><tr style="border-bottom: 2px solid #fecaca;"><td style="padding: 12px 0;"><strong>Expiry Date:</strong></td><td style="text-align: right;">{{expiry_date}}</td></tr><tr><td style="padding: 12px 0;"><strong>Status:</strong></td><td style="text-align: right;"><span style="background: #fecaca; color: #7f1d1d; padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: 700;">EXPIRED</span></td></tr></table></div><div style="background: #fef5f5; border-left: 4px solid #dc2626; border-radius: 8px; padding: 16px; margin: 20px 0;"><p style="color: #7f1d1d; margin: 0; font-size: 14px; line-height: 1.6;"><strong>Service Impact:</strong> The worker cannot be assigned to new tasks until this document is renewed.</p></div><div style="text-align: center; margin: 32px 0;"><a href="{{action_url}}" style="display: inline-block; padding: 14px 36px; background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); color: #fff; text-decoration: none; border-radius: 8px; font-weight: 700; font-size: 16px; text-transform: uppercase; letter-spacing: 1px;">RENEW DOCUMENT</a></div><div style="border-top: 2px solid #fecaca; padding-top: 20px; margin-top: 32px;"><p style="color: #7f1d1d; margin: 0; font-size: 12px; text-align: center; font-weight: 600;">This is a CRITICAL automated message from AHHC Portal. Please do not reply to this email.</p></div></td></tr></table></div>',
            ],
            ],
            [
                'name' => 'Care Review Due Reminder - 7 Days',
                'slug' => 'care-review-due-reminder-7-days',
                'subject' => 'Care Review Reminder: {{participant_name}} - Due in 7 Days',
                'category' => 'Reviews',
                'html' => '<div style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', sans-serif; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); padding: 40px 20px;"><table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto;"><tr><td style="background: white; border-radius: 16px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);"><div style="border-left: 4px solid #0ea5e9; padding-left: 16px; margin-bottom: 24px;"><h2 style="color: #0284c7; margin: 0 0 8px; font-size: 24px; font-weight: 700;">Care Review Due</h2><p style="color: #0369a1; margin: 0; font-size: 14px;">Reminder • 7 Days Remaining</p></div><p style="color: #475569; margin: 0 0 20px; line-height: 1.6;">Monthly care review for <strong>{{participant_name}}</strong> is <strong>due in 7 days</strong>.</p><div style="background: #f0f9ff; border: 1px solid #bfdbfe; border-radius: 12px; padding: 20px; margin: 24px 0;"><h4 style="color: #0284c7; margin: 0 0 12px; font-size: 14px; font-weight: 600;">Review Details:</h4><table width="100%" style="color: #475569; font-size: 14px;"><tr style="border-bottom: 1px solid #e0f2fe;"><td style="padding: 8px 0;"><strong>Participant:</strong></td><td style="text-align: right;">{{participant_name}}</td></tr><tr style="border-bottom: 1px solid #e0f2fe;"><td style="padding: 8px 0;"><strong>Review Type:</strong></td><td style="text-align: right;">Monthly Care Review</td></tr><tr><td style="padding: 8px 0;"><strong>Due Date:</strong></td><td style="text-align: right;"><span style="background: #e0f2fe; color: #0c4a6e; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">{{due_date}}</span></td></tr></table></div><p style="color: #64748b; margin: 20px 0; line-height: 1.6;">Please schedule and complete this review at your earliest convenience.</p><div style="text-align: center; margin: 32px 0;"><a href="{{action_url}}" style="display: inline-block; padding: 12px 32px; background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px;">View Review</a></div><div style="border-top: 1px solid #e2e8f0; padding-top: 20px; margin-top: 32px;"><p style="color: #94a3b8; margin: 0; font-size: 12px; text-align: center;">This is an automated message from AHHC Portal. Please do not reply to this email.</p></div></td></tr></table></div>',
            ],
            ],
            [
                'name' => 'Care Review Due Today',
                'slug' => 'care-review-due-reminder-today',
                'subject' => 'URGENT: Care Review Due Today - {{participant_name}}',
                'category' => 'Reviews',
                'html' => '<div style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', sans-serif; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); padding: 40px 20px;"><table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto;"><tr><td style="background: white; border-radius: 16px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);"><div style="text-align: center; margin-bottom: 24px;"><div style="display: inline-block; width: 64px; height: 64px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 50%; line-height: 64px; color: white; font-size: 32px;">⏰</div></div><h2 style="color: #d97706; text-align: center; margin: 0 0 8px; font-size: 28px; font-weight: 700;">URGENT: Due Today</h2><p style="color: #b45309; text-align: center; margin: 0 0 24px; font-size: 16px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Monthly Care Review</p><div style="background: #fffbeb; border-left: 4px solid #f59e0b; border-radius: 8px; padding: 20px; margin: 24px 0;"><p style="color: #78350f; margin: 0 0 16px; line-height: 1.6; font-weight: 600;">Monthly care review for <strong>{{participant_name}}</strong> is <strong>DUE TODAY</strong>.</p><table width="100%" style="color: #78350f; font-size: 14px;"><tr style="border-bottom: 1px solid #fcd34d;"><td style="padding: 8px 0;"><strong>Participant:</strong></td><td style="text-align: right;">{{participant_name}}</td></tr><tr style="border-bottom: 1px solid #fcd34d;"><td style="padding: 8px 0;"><strong>Review Type:</strong></td><td style="text-align: right;">Monthly Care Review</td></tr><tr><td style="padding: 8px 0;"><strong>Status:</strong></td><td style="text-align: right;"><span style="background: #fed7aa; color: #92400e; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">DUE TODAY</span></td></tr></table></div><p style="color: #dc2626; margin: 20px 0; line-height: 1.6; font-weight: 600;">Please complete the review as soon as possible.</p><div style="text-align: center; margin: 32px 0;"><a href="{{action_url}}" style="display: inline-block; padding: 12px 32px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px;">Complete Review</a></div><div style="border-top: 1px solid #e2e8f0; padding-top: 20px; margin-top: 32px;"><p style="color: #94a3b8; margin: 0; font-size: 12px; text-align: center;">This is an automated message from AHHC Portal. Please do not reply to this email.</p></div></td></tr></table></div>',
            ],
            ],
            [
                'name' => 'Care Review Overdue',
                'slug' => 'care-review-overdue',
                'subject' => 'CRITICAL: Care Review Overdue - {{participant_name}}',
                'category' => 'Reviews',
                'html' => '<div style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', sans-serif; background: linear-gradient(135deg, #7f1d1d 0%, #991b1b 100%); padding: 40px 20px;"><table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto;"><tr><td style="background: white; border-radius: 16px; padding: 40px; box-shadow: 0 20px 40px rgba(0,0,0,0.2);"><div style="text-align: center; margin-bottom: 24px;"><div style="display: inline-block; width: 64px; height: 64px; background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); border-radius: 50%; line-height: 64px; color: white; font-size: 32px;">!</div></div><h2 style="color: #dc2626; text-align: center; margin: 0 0 8px; font-size: 28px; font-weight: 700;">CARE REVIEW OVERDUE</h2><p style="color: #b91c1c; text-align: center; margin: 0 0 24px; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Immediate Action Required</p><div style="background: #fef2f2; border-left: 4px solid #dc2626; border-radius: 8px; padding: 20px; margin: 24px 0;"><p style="color: #7f1d1d; margin: 0 0 16px; line-height: 1.6; font-weight: 600;">Monthly care review for <strong>{{participant_name}}</strong> is <strong>OVERDUE</strong>.</p><table width="100%" style="color: #7f1d1d; font-size: 14px;"><tr style="border-bottom: 1px solid #fecaca;"><td style="padding: 12px 0;"><strong>Participant:</strong></td><td style="text-align: right;">{{participant_name}}</td></tr><tr style="border-bottom: 1px solid #fecaca;"><td style="padding: 12px 0;"><strong>Was Due:</strong></td><td style="text-align: right;">{{due_date}}</td></tr><tr style="border-bottom: 1px solid #fecaca;"><td style="padding: 12px 0;"><strong>Days Overdue:</strong></td><td style="text-align: right;"><span style="background: #fecaca; color: #7f1d1d; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">{{days_overdue}}</span></td></tr><tr><td style="padding: 12px 0;"><strong>Status:</strong></td><td style="text-align: right;"><span style="background: #fee2e2; color: #7f1d1d; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">OVERDUE</span></td></tr></table></div><div style="background: #fef5f5; border-left: 4px solid #dc2626; border-radius: 8px; padding: 16px; margin: 20px 0;"><p style="color: #7f1d1d; margin: 0; font-size: 14px; font-weight: 600;">Immediate action required to complete this care review.</p></div><div style="text-align: center; margin: 32px 0;"><a href="{{action_url}}" style="display: inline-block; padding: 14px 36px; background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); color: #fff; text-decoration: none; border-radius: 8px; font-weight: 700; font-size: 16px; text-transform: uppercase; letter-spacing: 1px;">Complete Review</a></div><div style="border-top: 1px solid #e2e8f0; padding-top: 20px; margin-top: 32px;"><p style="color: #94a3b8; margin: 0; font-size: 12px; text-align: center;">This is a critical automated message from AHHC Portal. Please do not reply to this email.</p></div></td></tr></table></div>',
            ],
            ],
            [
                'name' => 'Portal Notification Email',
                'slug' => 'portal-notification-email',
                'subject' => '{{title}}',
                'category' => 'System',
                'html' => '<div style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', sans-serif; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); padding: 40px 20px;"><table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto;"><tr><td style="background: white; border-radius: 16px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);"><div style="border-bottom: 3px solid #667eea; padding-bottom: 20px; margin-bottom: 24px;"><h2 style="color: #1e293b; margin: 0; font-size: 28px; font-weight: 700;">{{title}}</h2></div><div style="color: #475569; line-height: 1.8; margin: 20px 0; font-size: 16px;">{{message}}</div><div style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-left: 4px solid #0ea5e9; border-radius: 8px; padding: 20px; margin: 24px 0;"><p style="color: #0c4a6e; margin: 0; font-size: 14px; line-height: 1.6;">For more details and to take action, please visit the AHHC Portal using the link below.</p></div><div style="text-align: center; margin: 32px 0;"><a href="{{url}}" style="display: inline-block; padding: 12px 32px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px;">View Details</a></div><div style="border-top: 1px solid #e2e8f0; padding-top: 20px; margin-top: 32px;"><p style="color: #94a3b8; margin: 0; font-size: 12px; text-align: center;">This is an automated message from AHHC Portal. Please do not reply to this email.</p></div></td></tr></table></div>',
            ],
        ];
    }

    */

    public static function getBuiltInTemplateDefinitions(): array
    {
        return [
            [
                'name' => 'Participant Onboarding Invitation',
                'slug' => 'participant-onboarding-invitation',
                'subject' => 'Complete your AHHC portal onboarding',
                'category' => 'Onboarding',
                'html' => <<<'HTML'
<div style="margin:0;padding:0;background:#f5f7fb;font-family:Arial,sans-serif;color:#172033;">
  <div style="max-width:640px;margin:32px auto;padding:24px;background:#ffffff;border:1px solid #e5e7eb;border-radius:16px;">
    <div style="margin-bottom:20px;">
      <div style="display:inline-block;padding:6px 10px;border-radius:999px;background:#e8f1ff;color:#0d6efd;font-size:12px;font-weight:bold;letter-spacing:0.04em;text-transform:uppercase;">AHHC Portal</div>
    </div>
    <h1 style="margin:0 0 12px;font-size:24px;color:#0E3863;">Welcome, {{participant_first_name}}</h1>
    <p style="margin:0 0 12px;font-size:16px;line-height:1.6;">Hello {{participant_first_name}},</p>
    <p style="margin:0 0 12px;font-size:16px;line-height:1.6;">You’re invited to begin your onboarding with Allegiance Heart &amp; Home Care. This first step helps us confirm your information, review your documents, and prepare your portal access.</p>
    <p style="margin:0 0 18px;font-size:16px;line-height:1.6;">Please use the secure link below to continue. The link remains active until {{expires_at}}.</p>
    <p style="margin:0 0 16px;"><a href="{{onboarding_url}}" style="display:inline-block;padding:12px 20px;background:#0d6efd;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:bold;">Continue onboarding</a></p>
    <p style="margin:0 0 8px;font-size:14px;line-height:1.6;">If you have any questions or did not expect this invitation, please contact our support team for assistance.</p>
    <p style="margin:24px 0 0;font-size:13px;color:#6b7280;line-height:1.6;">This is an automated message from AHHC Portal. Please do not reply directly to this email.</p>
  </div>
</div>
HTML,
            ],
            [
                'name' => 'Onboarding Status Update',
                'slug' => 'onboarding-status',
                'subject' => '{{title}}',
                'category' => 'Onboarding',
                'html' => <<<'HTML'
<div style="margin:0;padding:0;background:#f5f7fb;font-family:Arial,sans-serif;color:#172033;">
  <div style="max-width:640px;margin:32px auto;padding:24px;background:#ffffff;border:1px solid #e5e7eb;border-radius:16px;">
    <div style="margin-bottom:20px;">
      <div style="display:inline-block;padding:6px 10px;border-radius:999px;background:#e8f1ff;color:#0d6efd;font-size:12px;font-weight:bold;letter-spacing:0.04em;text-transform:uppercase;">AHHC Portal</div>
    </div>
    <h1 style="margin:0 0 12px;font-size:24px;color:#0E3863;">{{title}}</h1>
    <p style="margin:0 0 12px;font-size:16px;line-height:1.6;">{{greeting}}</p>
    <p style="margin:0 0 12px;font-size:16px;line-height:1.6;">{{intro}}</p>
    <p style="margin:0 0 18px;font-size:16px;line-height:1.6;">{{body}}</p>
    <p style="margin:0 0 16px;"><a href="{{ctaUrl}}" style="display:inline-block;padding:12px 20px;background:#0d6efd;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:bold;">{{ctaLabel}}</a></p>
    <p style="margin:0 0 8px;font-size:14px;line-height:1.6;"><a href="{{secondaryUrl}}" style="color:#0d6efd;text-decoration:none;">{{secondaryLabel}}</a></p>
    <p style="margin:24px 0 0;font-size:13px;color:#6b7280;line-height:1.6;">This is an automated message from AHHC Portal. Please do not reply directly to this email.</p>
  </div>
</div>
HTML,
            ],
            [
                'name' => 'Account Activated',
                'slug' => 'account-activated',
                'subject' => 'Your account is now active',
                'category' => 'Account',
                'html' => <<<'HTML'
<div style="font-family: Arial, sans-serif; color: #172033; background:#f5f7fb; padding:24px;">
  <div style="max-width:640px;margin:0 auto;padding:24px;background:#ffffff;border:1px solid #e5e7eb;border-radius:16px;">
    <div style="margin-bottom:20px;">
      <div style="display:inline-block;padding:6px 10px;border-radius:999px;background:#e8f1ff;color:#0d6efd;font-size:12px;font-weight:bold;letter-spacing:0.04em;text-transform:uppercase;">AHHC Portal</div>
    </div>
    <h2 style="margin:0 0 12px;color:#0E3863;font-size:24px;">Your account is now active</h2>
    <p style="margin:0 0 12px;font-size:16px;line-height:1.6;">Hi {{name}},</p>
    <p style="margin:0 0 12px;font-size:16px;line-height:1.6;">Your account has been activated successfully. You can now sign in and access your portal dashboard whenever you’re ready.</p>
    <p style="margin:0 0 16px;font-size:16px;line-height:1.6;">Use the button below to sign in and begin using the portal.</p>
    <p style="margin:0 0 16px;"><a href="{{login_url}}" style="display:inline-block;padding:12px 20px;background:#0d6efd;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:bold;">Sign in to the portal</a></p>
    <p style="margin:0 0 8px;font-size:14px;line-height:1.6;">If you prefer, you can also go directly to your dashboard after signing in: <a href="{{dashboard_url}}" style="color:#0d6efd;text-decoration:none;">Open dashboard</a>.</p>
    <p style="margin:24px 0 0;font-size:13px;color:#6b7280;line-height:1.6;">If you did not expect this message, please contact our support team right away.</p>
  </div>
</div>
HTML,
            ],
            [
                'name' => 'Worker Nomination Submitted',
                'slug' => 'worker-nomination-submitted',
                'subject' => 'New Worker Nomination Submitted (#{{nomination_id}})',
                'category' => 'Nominations',
                'html' => <<<'HTML'
<div style="font-family: Arial, Helvetica, sans-serif; background: #eef2ff; padding: 24px 12px;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 640px; margin: 0 auto; border-collapse: collapse;">
    <tr>
      <td style="padding: 0 0 16px; text-align: center; color: #64748b; font-size: 12px;">New worker nomination submitted</td>
    </tr>
    <tr>
      <td style="background: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
          <tr>
            <td style="background: linear-gradient(135deg, #0f766e 0%, #2563eb 100%); padding: 28px 32px; color: #ffffff;">
              <h1 style="margin: 0 0 8px; font-size: 26px; line-height: 1.1;">New nomination received</h1>
              <p style="margin: 0; font-size: 15px; opacity: 0.92;">Nomination #{{nomination_id}}</p>
            </td>
          </tr>
          <tr>
            <td style="padding: 30px 32px 36px; color: #334155; line-height: 1.7;">
              <p style="margin: 0 0 16px;">A new worker nomination has been submitted by {{participant_name}}.</p>
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px;">
                <tr>
                  <td style="padding: 18px; color: #334155; font-size: 14px; line-height: 1.6;">
                    <p style="margin: 0 0 8px;"><strong>Worker:</strong> {{worker_full_name}}</p>
                    <p style="margin: 0 0 8px;"><strong>Email:</strong> {{worker_email}}</p>
                    <p style="margin: 0 0 8px;"><strong>Type:</strong> {{worker_type}}</p>
                    <p style="margin: 0;"><strong>Service:</strong> {{service_type}}</p>
                  </td>
                </tr>
              </table>
              <div style="text-align: center; margin: 26px 0 0;">
                <a href="{{action_url}}" style="display: inline-block; background: #0f766e; color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 999px; font-weight: 700; font-size: 15px;">Review nomination</a>
              </div>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</div>
HTML,
            ],
            [
                'name' => 'Worker Nomination Approved',
                'slug' => 'worker-nomination-approved',
                'subject' => 'Worker Nomination Approved - {{worker_full_name}}',
                'category' => 'Nominations',
                'html' => <<<'HTML'
<div style="font-family: Arial, Helvetica, sans-serif; background: #f0fdf4; padding: 24px 12px;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 640px; margin: 0 auto; border-collapse: collapse;">
    <tr>
      <td style="padding: 0 0 16px; text-align: center; color: #4d7c0f; font-size: 12px;">Worker nomination approved</td>
    </tr>
    <tr>
      <td style="background: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
          <tr>
            <td style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); padding: 28px 32px; color: #ffffff;">
              <h1 style="margin: 0 0 8px; font-size: 26px; line-height: 1.1;">Nomination approved</h1>
              <p style="margin: 0; font-size: 15px; opacity: 0.92;">Great news for {{worker_full_name}}</p>
            </td>
          </tr>
          <tr>
            <td style="padding: 30px 32px 36px; color: #334155; line-height: 1.7;">
              <p style="margin: 0 0 16px;">Your nomination has been approved and the next step is to send the invitation.</p>
              <div style="background: #f0fdf4; border-left: 4px solid #16a34a; border-radius: 12px; padding: 16px 18px; margin: 20px 0; color: #166534;">
                <p style="margin: 0;"><strong>Status:</strong> Approved</p>
              </div>
              <div style="text-align: center; margin: 24px 0 0;">
                <a href="{{action_url}}" style="display: inline-block; background: #16a34a; color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 999px; font-weight: 700; font-size: 15px;">View nomination</a>
              </div>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</div>
HTML,
            ],
            [
                'name' => 'Worker Nomination Rejected',
                'slug' => 'worker-nomination-rejected',
                'subject' => 'Worker Nomination - Unable to Proceed - {{worker_full_name}}',
                'category' => 'Nominations',
                'html' => <<<'HTML'
<div style="font-family: Arial, Helvetica, sans-serif; background: #fff1f2; padding: 24px 12px;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 640px; margin: 0 auto; border-collapse: collapse;">
    <tr>
      <td style="padding: 0 0 16px; text-align: center; color: #9f1239; font-size: 12px;">Worker nomination status update</td>
    </tr>
    <tr>
      <td style="background: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
          <tr>
            <td style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); padding: 28px 32px; color: #ffffff;">
              <h1 style="margin: 0 0 8px; font-size: 26px; line-height: 1.1;">Unable to proceed</h1>
              <p style="margin: 0; font-size: 15px; opacity: 0.92;">The nomination could not be approved at this time</p>
            </td>
          </tr>
          <tr>
            <td style="padding: 30px 32px 36px; color: #334155; line-height: 1.7;">
              <p style="margin: 0 0 16px;">The nomination for {{worker_full_name}} requires further review.</p>
              <div style="background: #fef2f2; border-left: 4px solid #dc2626; border-radius: 12px; padding: 16px 18px; margin: 20px 0; color: #991b1b;">
                <p style="margin: 0;"><strong>Status:</strong> Rejected</p>
              </div>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</div>
HTML,
            ],
            [
                'name' => 'Worker Invitation Sent',
                'slug' => 'worker-invitation-sent',
                'subject' => 'Worker Invitation Sent - {{worker_full_name}}',
                'category' => 'Nominations',
                'html' => <<<'HTML'
<div style="font-family: Arial, Helvetica, sans-serif; background: #eef8ff; padding: 24px 12px;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 640px; margin: 0 auto; border-collapse: collapse;">
    <tr>
      <td style="padding: 0 0 16px; text-align: center; color: #0f172a; font-size: 12px;">Worker invitation update</td>
    </tr>
    <tr>
      <td style="background: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
          <tr>
            <td style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); padding: 28px 32px; color: #ffffff;">
              <h1 style="margin: 0 0 8px; font-size: 26px; line-height: 1.1;">Invitation sent</h1>
              <p style="margin: 0; font-size: 15px; opacity: 0.92;">The worker invitation has been successfully dispatched</p>
            </td>
          </tr>
          <tr>
            <td style="padding: 30px 32px 36px; color: #334155; line-height: 1.7;">
              <p style="margin: 0 0 16px;">The invitation for {{worker_full_name}} is now on its way.</p>
              <div style="background: #eff6ff; border-left: 4px solid #0284c7; border-radius: 12px; padding: 16px 18px; margin: 20px 0; color: #075985;">
                <p style="margin: 0;"><strong>Service type:</strong> {{service_type}}</p>
              </div>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</div>
HTML,
            ],
            [
                'name' => 'Portal Notification Email',
                'slug' => 'portal-notification-email',
                'subject' => '{{title}}',
                'category' => 'System',
                'html' => <<<'HTML'
<div style="font-family: Arial, Helvetica, sans-serif; background: #f5f5ff; padding: 24px 12px;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 640px; margin: 0 auto; border-collapse: collapse;">
    <tr>
      <td style="padding: 0 0 16px; text-align: center; color: #64748b; font-size: 12px;">Portal notification from AHHC</td>
    </tr>
    <tr>
      <td style="background: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
          <tr>
            <td style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); padding: 28px 32px; color: #ffffff;">
              <h1 style="margin: 0 0 8px; font-size: 26px; line-height: 1.1;">{{title}}</h1>
            </td>
          </tr>
          <tr>
            <td style="padding: 30px 32px 36px; color: #334155; line-height: 1.7;">
              <p style="margin: 0 0 24px;">{{message}}</p>
              <div style="text-align: center;">
                <a href="{{url}}" style="display: inline-block; background: #6366f1; color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 999px; font-weight: 700; font-size: 15px;">View details</a>
              </div>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</div>
HTML,
            ],
        ];
    }

    public static function resolveBuiltInTemplateHtml(array $definition): string
    {
        if (! empty($definition['view_path']) && file_exists($definition['view_path'])) {
            return self::renderViewTemplate($definition['view_path'], $definition['sample_data'] ?? []);
        }

        return $definition['html'] ?? '';
    }

    protected static function renderViewTemplate(string $viewPath, array $data): string
    {
        if (! file_exists($viewPath)) {
            return '';
        }

        try {
            return (string) view()->file($viewPath, $data)->render();
        } catch (\Throwable) {
            return file_get_contents($viewPath) ?: '';
        }
    }

    protected static function convertHtmlToText(string $html): string
    {
        if ($html === '') {
            return '';
        }

        $text = preg_replace('/<br\s*\/?>/i', "\n", $html);
        $text = strip_tags($text);
        $text = preg_replace('/\R{3,}/u', "\n\n", $text);

        return trim((string) $text);
    }
}
