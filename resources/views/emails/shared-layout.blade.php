<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectLine ?? 'AHHC Portal' }}</title>
    <style>
        /* Reset */
        body,table,td,a{ -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; }
        table,td{ mso-table-lspace:0pt; mso-table-rspace:0pt; }
        img{ -ms-interpolation-mode:bicubic; display:block; border:0; outline:none; text-decoration:none; }
        body{ margin:0; padding:0; background-color:#f6f7f9; }

        /* Container */
        .email-wrap{ width:100%; background:#f6f7f9; padding:20px 0; }
        .email-body{ width:100%; max-width:650px; margin:0 auto; background:#ffffff; }

        /* Header */
        .email-header{ text-align:center; padding:28px 24px; background:linear-gradient(90deg,#356991 0%,#19B0A5 50%,#72CEAC 100%); color:#ffffff; }
        .email-badge{ display:inline-block; padding:6px 14px; background:rgba(255,255,255,0.18); font-size:12px; font-weight:700; letter-spacing:1px; text-transform:uppercase; margin-bottom:12px; }
        .email-headline{ font-family: Arial, Helvetica, sans-serif; font-size:24px; line-height:1.15; margin:8px 0 4px; font-weight:700; }
        .email-subtitle{ font-family: Arial, Helvetica, sans-serif; font-size:14px; color:rgba(255,255,255,0.95); margin:0; }

        /* Logo */
        .email-logo{ margin:0 auto 14px; }
        .email-logo img{ width:auto; height:auto; max-width:220px; max-height:64px; display:block; margin:0 auto; }

        /* Content */
        .content{ padding:32px 40px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color:#374151; font-size:15px; line-height:1.6; }
        .content p{ margin:0 0 16px 0; }

        /* Panels */
        .panel{ background:#f8fafc; border-left:5px solid #356991; padding:18px; margin:18px 0; }
        .panel h4{ margin:0 0 8px 0; color:#356991; font-size:16px; }

        /* Details table */
        .detail-table{ width:100%; border-collapse:collapse; margin:8px 0 0 0; }
        .detail-table td{ padding:6px 0; vertical-align:top; }
        .detail-table .label{ width:35%; font-weight:700; color:#111827; }

        /* Support / footer note */
        .support{ border:1px solid #e5e7eb; background:#fbfdfe; padding:18px; margin-top:18px; }

        /* Button - use table+anchor for email clients + MSO fallback */
        .btn-td{ text-align:center; padding:22px 0; }
        .btn{ display:inline-block; text-decoration:none; -webkit-text-size-adjust:none; mso-hide:all; background:#19B0A5; color:#ffffff; padding:12px 26px; border-radius:6px; font-weight:700; font-size:15px; white-space:nowrap; }

        /* Warning */
        .warning{ background:#fff8f8; border-left:5px solid #eb3035; padding:14px; margin:16px 0; color:#7f1d1d; }

        /* Footer */
        .email-footer{ text-align:center; padding:18px; background:#356991; color:#d8eef3; font-size:13px; }

        /* Responsive */
        @media screen and (max-width:600px){
            .content{ padding:18px 20px !important; }
            .email-headline{ font-size:20px !important; }
            .email-body{ width:100% !important; }
            .btn{ display:block; width:100% !important; box-sizing:border-box; }
        }
    </style>
</head>
<body>
    <table role="presentation" class="email-wrap" width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td align="center">
                <table role="presentation" class="email-body" width="650" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td class="email-header" style="padding:28px 24px; text-align:center;">
                            @if(!empty($logo))
                                <div class="email-logo" style="margin:0 auto 12px;">
                                    <img src="{{ $logo }}" alt="{{ $badge ?? 'AHHC Portal' }}" width="200" height="60" style="max-width:220px; height:auto; display:block; border:0;" />
                                </div>
                            @endif
                            <div class="email-badge">{{ $badge ?? 'AHHC Portal' }}</div>
                            <h1 class="email-headline">{{ $headline }}</h1>
                            <p class="email-subtitle">{{ $subtitle }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="content">
                            @php
                                $bodyIntro = null;
                                if (! empty($introHtml)) {
                                    $bodyIntro = $introHtml;
                                } elseif (! empty($intro)) {
                                    $bodyIntro = preg_match('/<[^>]+>/', $intro) ? $intro : nl2br(e($intro));
                                }
                            @endphp

                            @if(! empty($bodyIntro))
                                {!! $bodyIntro !!}
                            @endif

                            @if(!empty($warning))
                                <div class="warning"><strong>{{ $warning }}</strong></div>
                            @endif

                            @if(!empty($highlightPanel))
                                <div class="panel">{!! $highlightPanel !!}</div>
                            @endif

                            @if(!empty($details))
                                <div class="panel">
                                    <h4>Details</h4>
                                    <table class="detail-table" cellpadding="0" cellspacing="0" border="0">
                                        @foreach($details as $label => $value)
                                            <tr>
                                                <td class="label">{{ $label }}</td>
                                                <td>{!! $value !!}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                            @endif

                            @if($actionUrl)
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                    <tr>
                                        <td class="btn-td">
                                            <!--[if mso]>
                                            <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $actionUrl }}" style="height:44px;v-text-anchor:middle;width:220px;" arcsize="8%" strokecolor="#19B0A5" fillcolor="#19B0A5">
                                                <w:anchorlock/>
                                                <center style="color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:15px;font-weight:700;">{{ $actionText ?? 'View details' }}</center>
                                            </v:roundrect>
                                            <![endif]-->

                                            <!--[if !mso]><!-- -->
                                            <a href="{{ $actionUrl }}" class="btn" style="background:#19B0A5;color:#ffffff;padding:12px 26px;border-radius:6px;display:inline-block;font-weight:700;">{{ $actionText ?? 'View details' }}</a>
                                            <!--<![endif]-->
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            @if($supportText)
                                <div class="support">
                                    <h4 style="margin:0 0 8px 0;color:#356991;">Need help?</h4>
                                    <p style="margin:0;">{{ $supportText }}</p>
                                </div>
                            @endif

                            @if($footerNote)
                                <p style="margin-top:18px;color:#6b7280;">{{ $footerNote }}</p>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td class="email-footer">
                            <p style="margin:0 0 8px 0;">This is an automated message from AHHC Portal.</p>
                            <p style="margin:0;color:#ffffff;">Please do not reply to this email.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
