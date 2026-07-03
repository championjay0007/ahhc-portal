<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $agreement->title ?? 'Agreement' }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #222; margin: 24px; }
        h1 { margin-bottom: 16px; }
        .content { white-space: pre-wrap; line-height: 1.5; }
    </style>
</head>
<body>
    <h1>{{ $agreement->title ?? 'Agreement' }}</h1>
    @if($agreement->description)
        <p><strong>Description:</strong> {{ $agreement->description }}</p>
    @endif
    <div class="content">
        {!! nl2br(e($agreement->content)) !!}
    </div>
</body>
</html>
