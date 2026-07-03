<?php

namespace Database\Seeders;

use App\Models\MessageTemplate;
use Illuminate\Database\Seeder;

class MessageTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Welcome Message',
                'subject' => 'Welcome to {{organization}}',
                'body' => "Dear {{first_name}},\n\nWelcome to our portal! We're excited to have you join us.\n\nThis message is to confirm that your account has been successfully created.\n\nIf you have any questions, please don't hesitate to reach out to our support team.\n\nBest regards,\n{{organization}} Team",
                'type' => 'notification',
                'category' => 'Onboarding',
                'is_active' => true,
            ],
            [
                'name' => 'Important Update',
                'subject' => 'Important Update Notification',
                'body' => "Dear {{first_name}},\n\nWe wanted to inform you about an important update regarding your account and services.\n\nPlease review the details below and let us know if you have any questions.\n\nThank you for your attention to this matter.\n\nBest regards,\n{{organization}} Team",
                'type' => 'alert',
                'category' => 'Notifications',
                'is_active' => true,
            ],
            [
                'name' => 'Compliance Document Due',
                'subject' => 'Compliance Document Expiring Soon',
                'body' => "Dear {{first_name}},\n\nThis is a reminder that your compliance documentation will expire on {{date}}.\n\nPlease ensure that you submit the required documents in a timely manner to avoid any disruption to your services.\n\nThank you,\n{{organization}} Team",
                'type' => 'compliance',
                'category' => 'Compliance',
                'is_active' => true,
            ],
            [
                'name' => 'Care Review Reminder',
                'subject' => 'Monthly Care Review Due',
                'body' => "Dear {{first_name}},\n\nThis is a reminder that your monthly care review is due on {{date}}.\n\nPlease log into your account and complete the review at your earliest convenience.\n\nThank you,\n{{organization}} Team",
                'type' => 'care_review',
                'category' => 'Care Reviews',
                'is_active' => true,
            ],
            [
                'name' => 'Account Security Notice',
                'subject' => 'Account Security Information',
                'body' => "Dear {{first_name}},\n\nFor your security, we recommend that you update your password regularly.\n\nIf you notice any unusual activity on your account, please contact us immediately.\n\nYour security is our priority.\n\nBest regards,\n{{organization}} Security Team",
                'type' => 'alert',
                'category' => 'Security',
                'is_active' => true,
            ],
            [
                'name' => 'General Announcement',
                'subject' => 'Important Announcement',
                'body' => "Dear {{first_name}},\n\nWe have an important announcement to share with you:\n\n[Add your announcement content here]\n\nPlease feel free to contact our support team if you have any questions.\n\nBest regards,\n{{organization}} Team",
                'type' => 'general',
                'category' => 'Announcements',
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            MessageTemplate::updateOrCreate(
                ['name' => $template['name']],
                $template
            );
        }
    }
}
