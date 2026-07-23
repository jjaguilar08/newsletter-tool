<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: sans-serif; color: #1a1a1a;">
    <div>{!! nl2br(e($content)) !!}</div>

    <hr style="margin: 32px 0; border: none; border-top: 1px solid #ddd;">

    <p style="font-size: 12px; color: #666;">
        <a href="{{ $unsubscribeUrl }}">Unsubscribe</a> from this newsletter.
    </p>
</body>
</html>
