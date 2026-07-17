<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AHHC Portal - Worker Onboarding Invitation</title>
    @php
        $portalSettings = $portalSettings ?? [];
        $logo = $logo ?? \App\Services\EmailBrandingService::logoUrl();
        $organization = $organization ?? config('app.name', 'AHHC Portal');
        $year = $year ?? now()->year;
    @endphp
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f6f7f9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #1f2937;
        }

        .email-wrapper {
            width: 100%;
            padding: 20px;
        }

        .email-card {
            width: 100%;
            max-width: 650px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.08);
        }

        .email-header {
            background: linear-gradient(135deg, #356991, #19B0A5, #72CEAC);
            padding: 45px 30px;
            text-align: center;
            color: #ffffff;
        }

        .email-header img {
            width: 90px;
            margin-bottom: 20px;
        }

        .email-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 50px;
            background: rgba(255, 255, 255, 0.18);
            color: #ffffff;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        .email-title {
            margin: 20px 0 10px;
            font-size: 32px;
            line-height: 1.1;
        }

        .email-intro {
            margin: 0;
            font-size: 16px;
            line-height: 1.75;
            color: #eafdfc;
        }

        .email-body {
            padding: 45px 40px;
        }

        .email-body p {
            margin: 0 0 24px;
            font-size: 16px;
            line-height: 1.85;
            color: #4b5563;
        }

        .email-body strong {
            color: #356991;
        }

        .button-wrap {
            text-align: center;
            margin: 40px 0;
        }

        .button-link {
            display: inline-block;
            padding: 16px 42px;
            background: #19B0A5;
            color: #ffffff;
            text-decoration: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
        }

        .panel {
            width: 100%;
            background: #f8fafc;
            border-radius: 12px;
            border-left: 5px solid #356991;
            margin-bottom: 30px;
        }

        .panel-inner {
            padding: 28px;
        }

        .panel-heading {
            margin: 0 0 18px;
            font-size: 18px;
            font-weight: 700;
            color: #356991;
        }

        .panel-table {
            width: 100%;
            border-collapse: collapse;
            color: #374151;
        }

        .panel-table td {
            padding: 8px 0;
            vertical-align: top;
        }

        .panel-table td.label {
            width: 35%;
            font-weight: 700;
        }

        .list-table {
            width: 100%;
            border-collapse: collapse;
            color: #374151;
        }

        .list-table td {
            padding: 6px 0;
            vertical-align: top;
            font-size: 15px;
        }

        .note-panel {
            width: 100%;
            background: #fffdf7;
            border-radius: 12px;
            border-left: 5px solid #f59e0b;
            margin-top: 30px;
        }

        .note-panel .panel-inner {
            padding: 28px;
        }

        .note-panel p,
        .warning-panel p,
        .support-panel p {
            margin: 0;
            font-size: 15px;
            line-height: 1.8;
            color: #4b5563;
        }

        .warning-panel {
            width: 100%;
            background: #fff8f8;
            border-radius: 12px;
            border-left: 5px solid #eb3035;
            margin-top: 30px;
        }

        .support-panel {
            width: 100%;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            margin-top: 30px;
        }

        .support-panel h3,
        .note-panel h3,
        .warning-panel h3 {
            margin: 0 0 15px;
            font-size: 18px;
            font-weight: 700;
        }

        .footer-section {
            padding: 28px;
            background: #356991;
            text-align: center;
            color: #d8eef3;
        }

        .footer-section p {
            margin: 0;
            font-size: 13px;
            line-height: 1.85;
        }

        .footer-brand {
            margin-top: 12px;
            color: #ffffff;
            font-size: 13px;
        }

        @media screen and (max-width: 600px) {
            .email-body,
            .email-header {
                padding-left: 20px;
                padding-right: 20px;
            }

            .button-link {
                width: 100%;
                box-sizing: border-box;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <table role="presentation" class="email-card">
            <tbody>
                <tr>
                    <td class="email-header">
                        <a href="{{ url('/') }}" style="display:inline-block;">
                            <img src="{{ $logo ?? asset('images/branding/logo.jpg') }}" alt="{{ $badge ?? ($organization ?? config('app.name', 'AHHC Portal')) }}" width="200" height="60" style="display:block;margin:0 auto;max-width:220px;height:auto;border:0;" />
                        </a>
                        <div class="email-badge">Worker Invitation</div>
                        <h1 class="email-title">Welcome to AHHC</h1>
                        <p class="email-intro">You've been invited to join the AHHC Care Team.</p>
                    </td>
                </tr>
                <tr>
                    <td class="email-body">
                        <p style="font-size:18px;color:#374151;margin-top:0;">Hello <strong>{{ trim(($worker->first_name ?? '') . ' ' . ($worker->last_name ?? '')) ?: 'Worker' }}</strong>,</p>
                        <p>Congratulations! You have been nominated to join <strong style="color:#19B0A5;">{{ $organization ?? config('app.name', 'AHHC Portal') }}</strong> as a support worker.</p>
                        <p>To begin providing services, please complete your secure onboarding through the AHHC Portal.</p>

                        <div class="button-wrap">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" style="margin:0 auto;">
                                <tr>
                                    <td align="center">
                                        <!--[if mso]>
                                        <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $onboardingUrl ?? '#' }}" style="height:44px;v-text-anchor:middle;width:240px;" arcsize="8%" strokecolor="#19B0A5" fillcolor="#19B0A5">
                                            <w:anchorlock/>
                                            <center style="color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:15px;font-weight:700;">Start My Onboarding</center>
                                        </v:roundrect>
                                        <![endif]-->
                                        <!--[if !mso]><!-- -->
                                        <a class="button-link" href="{{ $onboardingUrl ?? '#' }}" style="background:#19B0A5;color:#ffffff;padding:12px 26px;border-radius:8px;font-weight:700;display:inline-block;white-space:nowrap;text-decoration:none;">Start My Onboarding</a>
                                        <!--<![endif]-->
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="panel">
                            <div class="panel-inner">
                                <p class="panel-heading">Your Invitation Details</p>
                                <table class="panel-table">
                                    <tbody>
                                        <tr>
                                            <td class="label">Worker ID</td>
                                            <td>{{ $worker->worker_number ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="label">Email</td>
                                            <td>{{ $worker->email ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="label">Phone</td>
                                            <td>{{ $worker->phone ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="label">Service Type</td>
                                            <td>{{ $worker->role_type ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="label">Invitation Expires</td>
                                            <td style="color:#EB3035;font-weight:700;">{{ optional($expiresAt)->format('M d, Y') ?? now()->addDays(30)->format('M d, Y') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="panel note-panel">
                            <div class="panel-inner">
                                <p class="panel-heading">Your Onboarding Journey</p>
                                <table class="list-table">
                                    <tbody>
                                        <tr><td width="32">✅</td><td>Create your AHHC Portal account</td></tr>
                                        <tr><td>🔐</td><td>Set up Multi-Factor Authentication (MFA)</td></tr>
                                        <tr><td>📄</td><td>Upload required compliance documents</td></tr>
                                        <tr><td>📝</td><td>Complete declarations and agreements</td></tr>
                                        <tr><td>👨‍⚕️</td><td>AHHC reviews your documents</td></tr>
                                        <tr><td>✔️</td><td>Receive approval and assigned service categories</td></tr>
                                        <tr><td>🤝</td><td>Start supporting participants</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="note-panel">
                            <div class="panel-inner">
                                <h3>Documents You May Need</h3>
                                <p>• Police Check<br>• NDIS Worker Screening (if applicable)<br>• Professional Qualifications<br>• Insurance Certificates<br>• First Aid / CPR Certificates<br>• ABN (where applicable)<br>• Identification Documents</p>
                            </div>
                        </div>

                        <div class="warning-panel">
                            <div class="panel-inner">
                                <h3>Invitation Expiry</h3>
                                <p>Your invitation expires on <strong>{{ optional($expiresAt)->format('M d, Y') ?? now()->addDays(30)->format('M d, Y') }}</strong>. If you do not complete your onboarding before this date, you may need to request a new invitation.</p>
                            </div>
                        </div>

                        <div class="support-panel">
                            <div class="panel-inner">
                                <h3>Need Assistance?</h3>
                                <p>If you experience any issues during onboarding or have questions about the required documents, please contact the AHHC Support Team. We're here to help every step of the way.</p>
                            </div>
                        </div>

                        <hr style="border:none;border-top:1px solid #E5E7EB;margin:40px 0;">

                        <p style="margin:0;font-size:16px;font-weight:700;color:#356991;">{{ $organization ?? config('app.name', 'AHHC Portal') }}</p>
                        <p style="margin-top:8px;font-size:14px;color:#6b7280;">Compassion you can Trust.<br>Care you deserve.</p>
                    </td>
                </tr>
                <tr>
                    <td class="footer-section">
                        <p>This is an automated invitation from the AHHC Portal.<br>Please do not reply directly to this email.</p>
                        <p class="footer-brand">© {{ date('Y') }} {{ $organization ?? config('app.name', 'AHHC Portal') }}. All rights reserved.</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
