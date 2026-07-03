<?php

namespace App\Providers;

use App\Models\Message;
use App\Models\PortalNotification;
use App\Models\PortalSetting;
use App\Models\PreApprovalRequest;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Policies\PreApprovalRequestPolicy;
use App\Services\MessageService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\ViewErrorBag;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $portalNotifications = collect();
            $portalUnreadNotifications = collect();
            $portalMessages = collect();
            $supportConversations = collect();
            $unreadNotificationCount = 0;
            $unreadMessageCount = 0;
            $unreadSupportConversationCount = 0;
            $portalSettings = $this->loadSettings();

            if (Auth::check()) {
                $portalNotifications = PortalNotification::where('user_id', Auth::id())
                    ->orderByDesc('created_at')
                    ->take(8)
                    ->get();

                $portalUnreadNotifications = PortalNotification::where('user_id', Auth::id())
                    ->whereNull('read_at')
                    ->orderByDesc('created_at')
                    ->take(8)
                    ->get();

                $unreadNotificationCount = $portalUnreadNotifications->count();

                $unreadMessageCount = MessageService::getUnreadCount(Auth::id());

                $supportConversations = SupportConversation::with(['user', 'messages'])
                    ->where('status', '!=', 'closed')
                    ->orderByDesc('last_message_at')
                    ->take(5)
                    ->get();

                $unreadSupportConversationCount = SupportMessage::whereHas('conversation', function ($query) {
                    $query->where('status', '!=', 'closed');
                })
                    ->where('is_admin', false)
                    ->whereNull('read_at')
                    ->count();

                // Fetch recent messages
                $portalMessages = Message::where('recipient_id', Auth::id())
                    ->orWhere('sender_id', Auth::id())
                    ->orderByDesc('created_at')
                    ->take(5)
                    ->get();
            }

            $view->with(compact('portalNotifications', 'portalUnreadNotifications', 'portalMessages', 'supportConversations', 'unreadNotificationCount', 'portalSettings', 'unreadMessageCount', 'unreadSupportConversationCount'));
        });

        View::share('portalSettings', $this->loadSettings());
        // Ensure views always have an `$errors` ViewErrorBag available even when
        // middleware that normally shares errors (ShareErrorsFromSession) is
        // disabled during tests.
        View::share('errors', session('errors') ?? new ViewErrorBag);

        Gate::policy(PreApprovalRequest::class, PreApprovalRequestPolicy::class);

        // Apply portal SMTP settings globally for all mail operations
        $this->applyPortalMailConfig();
    }

    protected function applyPortalMailConfig(): void
    {
        $settings = $this->loadSettings();

        if (! empty($settings['smtp_host'])) {
            $currentDefault = config('mail.default');
            if ($currentDefault !== 'failover') {
                config(['mail.default' => 'smtp']);
            }

            config(['mail.mailers.smtp.host' => $settings['smtp_host']]);
            if (! empty($settings['smtp_port'])) {
                config(['mail.mailers.smtp.port' => (int) $settings['smtp_port']]);
            }
            if (! empty($settings['smtp_username'])) {
                config(['mail.mailers.smtp.username' => $settings['smtp_username']]);
            }
            if (! empty($settings['smtp_password'])) {
                config(['mail.mailers.smtp.password' => $settings['smtp_password']]);
            }
            if (! empty($settings['smtp_encryption'])) {
                config(['mail.mailers.smtp.encryption' => $settings['smtp_encryption']]);
            }
        }

        // Apply global mail from address
        if (! empty($settings['email_sender_address'])) {
            config(['mail.from.address' => $settings['email_sender_address']]);
        }
        if (! empty($settings['email_sender_name'])) {
            config(['mail.from.name' => $settings['email_sender_name']]);
        }
    }

    protected function loadSettings(): array
    {
        $defaults = [
            'website_name' => 'AHHC Portal',
            'website_subtitle' => 'Self-service participant and worker portal',
            'website_description' => 'Manage participants, workers, invoices, approvals, documents, and compliance in one secure portal.',
            'primary_color' => '#0d6efd',
            'secondary_color' => '#6610f2',
            'dashboard_primary_color' => '#0E3863',
            'dashboard_secondary_color' => '#1699A1',
            'email_sender_name' => 'AHHC Support',
            'email_sender_address' => 'support@example.com',
            'smtp_host' => null,
            'smtp_port' => null,
            'smtp_encryption' => null,
            'smtp_username' => null,
            'smtp_password' => null,
            'logo_path' => null,
            'favicon_path' => null,
            'organization_name' => 'AHHC Portal',
            'support_email' => 'support@example.com',
            'default_user_role' => 'participant',
            'require_mfa' => false,
            'report_export_emails' => false,
            'incident_alerts' => true,
            'pwa_icon_path' => null,
            'tawk_to_property_id' => null,
            'tawk_to_widget_id' => null,
        ];

        if (! Schema::hasTable('portal_settings')) {
            return $defaults;
        }

        $stored = PortalSetting::query()->pluck('value', 'key')->all();

        return array_replace($defaults, $stored);
    }
}
