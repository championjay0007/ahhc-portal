<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectLine ?? 'AHHC Portal' }}</title>
    <style type="text/css">
        /* Base styles */
        body, table, td, div, p, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: nearest-neighbor; display: block; }
        
        /* Email client resets */
        body { margin: 0; padding: 0; background-color: #f6f7f9; }
        table { border-collapse: collapse; width: 100%; }
        
        /* Wrapper */
        .wrapper { background-color: #f6f7f9; padding: 20px 0; }
        
        /* Card container */
        .card-container { max-width: 650px; margin: 0 auto; background-color: #ffffff; }
        
        /* Header */
        .header { background-color: #356991; padding: 45px 30px; text-align: center; color: #ffffff; }
        .header-gradient { background: linear-gradient(to right, #356991 0%, #19B0A5 50%, #72CEAC 100%); }
        
        /* Badge */
        .badge { display: inline-block; padding: 6px 16px; background-color: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 12px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 20px; }
        
        /* Titles */
        .title { margin: 20px 0 10px; font-size: 32px; line-height: 1.1; font-weight: bold; color: #ffffff; }
        .subtitle { margin: 0; font-size: 16px; line-height: 1.75; color: #eafdfc; }
        
        /* Body content */
        .body { padding: 45px 40px; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; color: #4b5563; }
        .body p { margin: 0 0 24px 0; font-size: 16px; line-height: 1.85; }
        .body p strong { color: #356991; font-weight: bold; }
        
        /* Button */
        .button-wrapper { text-align: center; margin: 40px 0; }
        .button { display: inline-block; padding: 16px 42px; background-color: #19B0A5; color: #ffffff; text-decoration: none; font-size: 16px; font-weight: bold; text-align: center; line-height: 1.2; }
        .button:hover { background-color: #138a82; }
        
        /* Panels */
        .panel { background-color: #f8fafc; border-left: 5px solid #356991; margin-bottom: 30px; padding: 28px; }
        .panel-heading { margin: 0 0 18px 0; font-size: 18px; font-weight: bold; color: #356991; }
        .panel-text { margin: 0; font-size: 15px; line-height: 1.8; color: #4b5563; }
        
        /* Detail table */
        .detail-table { width: 100%; border-collapse: collapse; color: #374151; }
        .detail-table td { padding: 8px 0; vertical-align: top; font-size: 15px; line-height: 1.6; }
        .detail-table td.label { font-weight: bold; width: 35%; }
        
        /* Warning panel */
        .warning-panel { background-color: #fff8f8; border-left: 5px solid #eb3035; margin-top: 30px; padding: 28px; }
        .warning-panel strong { color: #eb3035; font-weight: bold; }
        
        /* Support panel */
        .support-panel { background-color: #f8fafc; border: 1px solid #e5e7eb; margin-top: 30px; padding: 28px; }
        .support-panel h3 { margin: 0 0 15px 0; font-size: 18px; font-weight: bold; color: #356991; }
        .support-panel p { margin: 0; font-size: 15px; line-height: 1.8; color: #4b5563; }
        
        /* Footer */
        .footer { background-color: #356991; padding: 28px; text-align: center; color: #d8eef3; }
        .footer p { margin: 0 0 8px 0; font-size: 13px; line-height: 1.85; }
        .footer-brand { margin-top: 12px; color: #ffffff; font-size: 13px; }
        
        /* Logo */
        .logo { margin-bottom: 12px; }
        .logo img { max-height: 60px; max-width: 280px; display: block; margin: 0 auto; }
        
        /* Responsive */
        @media screen and (max-width: 600px) {
            .body, .header { padding-left: 20px !important; padding-right: 20px !important; }
            .title { font-size: 24px; }
            .button { width: 100% !important; display: block !important; }
        }
    </style>
</head>
<body>
<table class="wrapper" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin:0;padding:0;background-color:#f6f7f9;">
    <tr>
        <td align="center" style="padding:20px 0;background-color:#f6f7f9;">
            <table class="card-container" cellpadding="0" cellspacing="0" border="0" width="650" style="width:100%;max-width:650px;margin:0 auto;background-color:#ffffff;">
                <!-- Header -->
                <tr>
                    <td class="header-gradient header" bgcolor="#356991" style="background: linear-gradient(to right, #356991 0%, #19B0A5 50%, #72CEAC 100%); padding:45px 30px; text-align:center; color:#ffffff;">
                        @if(!empty($logo))
                            <div class="logo" style="margin-bottom:12px;"><img src="{{ $logo }}" width="200" height="60" alt="{{ $badge ?? 'AHHC Portal' }} logo" style="display:block;margin:0 auto;max-width:200px;width:auto;height:auto;border:none;outline:none;text-decoration:none;" /></div>
                        @endif
                        <div class="badge" style="display:inline-block;padding:6px 16px;background-color:rgba(255,255,255,0.2);color:#ffffff;font-size:12px;font-weight:bold;letter-spacing:1px;text-transform:uppercase;margin-bottom:20px;">{{ $badge ?? 'AHHC Portal' }}</div>
                        <h1 class="title" style="margin:20px 0 10px;font-size:32px;line-height:1.1;font-weight:bold;color:#ffffff;">{{ $headline }}</h1>
                        <p class="subtitle" style="margin:0;font-size:16px;line-height:1.75;color:rgba(255,255,255,0.95);">{{ $subtitle }}</p>
                    </td>
                </tr>
                
                <!-- Body -->
                <tr>
                    <td class="body" style="padding:45px 40px;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;color:#4b5563;">
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
                            <div class="warning-panel">
                                <p><strong>{{ $warning }}</strong></p>
                            </div>
                        @endif
                        
                        @if(!empty($highlightPanel))
                            <div class="panel">{!! $highlightPanel !!}</div>
                        @endif
                        
                        @if(!empty($details))
                            <div class="panel">
                                <p class="panel-heading">Details</p>
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
                            <div class="button-wrapper">
                                <a href="{{ $actionUrl }}" class="button">{{ $actionText ?? 'View details' }}</a>
                            </div>
                        @endif
                        
                        @if($supportText)
                            <div class="support-panel">
                                <h3>Need help?</h3>
                                <p>{{ $supportText }}</p>
                            </div>
                        @endif
                        
                        @if($footerNote)
                            <p>{{ $footerNote }}</p>
                        @endif
                    </td>
                </tr>
                
                <!-- Footer -->
                <tr>
                    <td class="footer">
                        <p>This is an automated message from AHHC Portal.</p>
                        <p class="footer-brand">Please do not reply to this email.</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
