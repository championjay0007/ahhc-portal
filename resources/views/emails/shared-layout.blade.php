<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subjectLine ?? 'AHHC Portal' }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f6f7f9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #1f2937;
        }
        .wrapper { width: 100%; padding: 20px; }
        .card { max-width: 650px; margin: 0 auto; background: #ffffff; border-radius: 18px; overflow: hidden; box-shadow: 0 12px 35px rgba(0,0,0,.08); }
        .header { background: linear-gradient(135deg,#356991,#19B0A5,#72CEAC); padding: 45px 30px; text-align: center; color: #ffffff; }
        .badge { display: inline-block; padding: 6px 16px; background: rgba(255,255,255,.18); border-radius: 50px; color: #ffffff; font-size: 12px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 20px; }
        .title { margin: 20px 0 10px; font-size: 32px; line-height: 1.1; }
        .subtitle { margin: 0; font-size: 16px; line-height: 1.75; color: #eafdfc; }
        .body { padding: 45px 40px; }
        .body p { margin: 0 0 24px; font-size: 16px; line-height: 1.85; color: #4b5563; }
        .body p strong { color: #356991; }
        .button-wrap { text-align: center; margin: 40px 0; }
        .button { display: inline-block; padding: 16px 42px; background: #19B0A5; color: #ffffff; text-decoration: none; border-radius: 10px; font-size: 16px; font-weight: 700; }
        .panel { background: #f8fafc; border-left: 5px solid #356991; border-radius: 12px; margin-bottom: 30px; }
        .panel-inner { padding: 28px; }
        .panel-heading { margin: 0 0 18px; font-size: 18px; font-weight: 700; color: #356991; }
        .panel-table { width: 100%; border-collapse: collapse; color: #374151; }
        .panel-table td { padding: 8px 0; vertical-align: top; }
        .panel-table td.label { width: 35%; font-weight: 700; }
        .note-panel { margin-top: 30px; background: #fffdf7; border-left: 5px solid #f59e0b; border-radius: 12px; }
        .warning-panel { margin-top: 30px; background: #fff8f8; border-left: 5px solid #eb3035; border-radius: 12px; }
        .support-panel { margin-top: 30px; background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 12px; }
        .panel h3 { margin: 0 0 15px; font-size: 18px; font-weight: 700; }
        .panel p { margin: 0; font-size: 15px; line-height: 1.8; color: #4b5563; }
        .footer { padding: 28px; background: #356991; text-align: center; color: #d8eef3; }
        .footer p { margin: 0; font-size: 13px; line-height: 1.85; }
        .footer-brand { margin-top: 12px; color: #ffffff; font-size: 13px; }
        @media screen and (max-width:600px) { .header, .body { padding-left: 20px; padding-right: 20px; } .button { width: 100%; box-sizing: border-box; } }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">
        <div class="header">
            @if(!empty($logo))
                <div style="margin-bottom:12px;"><img src="{{ $logo }}" alt="logo" style="max-height:60px; max-width:280px; object-fit:contain; display:block; margin:0 auto;" /></div>
            @endif
            <div class="badge">{{ $badge ?? 'AHHC Portal' }}</div>
            <h1 class="title">{{ $headline }}</h1>
            <p class="subtitle">{{ $subtitle }}</p>
        </div>
        <div class="body">
            @if(!empty($introHtml))
                {!! $introHtml !!}
            @else
                <p>{!! nl2br(e($intro)) !!}</p>
            @endif
            @if(!empty($warning))
                <div class="warning-panel"><div class="panel-inner"><p><strong>{{ $warning }}</strong></p></div></div>
            @endif
            @if(!empty($highlightPanel))
                <div class="panel"><div class="panel-inner">{!! $highlightPanel !!}</div></div>
            @endif
            @if(!empty($details))
                <div class="panel">
                    <div class="panel-inner">
                        <p class="panel-heading">Details</p>
                        <table class="panel-table">
                            @foreach($details as $label => $value)
                                <tr>
                                    <td class="label">{{ $label }}</td>
                                    <td>{!! $value !!}</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            @endif
            @if($actionUrl)
                <div class="button-wrap">
                    <a class="button" href="{{ $actionUrl }}">{{ $actionText ?? 'View details' }}</a>
                </div>
            @endif
            @if($supportText)
                <div class="support-panel">
                    <div class="panel-inner">
                        <h3>Need help?</h3>
                        <p>{{ $supportText }}</p>
                    </div>
                </div>
            @endif
            @if($footerNote)
                <p>{{ $footerNote }}</p>
            @endif
        </div>
        <div class="footer">
            <p>This is an automated message from AHHC Portal.</p>
            <p class="footer-brand">Please do not reply to this email.</p>
        </div>
    </div>
</div>
</body>
</html>
