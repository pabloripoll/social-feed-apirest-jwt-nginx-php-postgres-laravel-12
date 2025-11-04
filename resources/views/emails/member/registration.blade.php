<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>{{ $payload['subject'] ?? 'Welcome to ' . config('app.name') }}</title>
  <style>
    body { background: #f4f6f8; margin:0; padding:0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial;}
    .container { max-width:600px; margin:24px auto; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 4px 18px rgba(0,0,0,0.06); }
    .header { background:#0ea5a5; color:#fff; padding:20px; text-align:center; }
    .body { padding:24px; color:#333; line-height:1.6; }
    .btn { display:inline-block; background:#0ea5a5; color:#fff; padding:12px 20px; border-radius:6px; text-decoration:none; }
    .footer { padding:16px; font-size:13px; color:#8b8f94; text-align:center; }
    @media (max-width:480px){ .body{padding:16px;} }
  </style>
</head>
<body>
  <div class="container" role="article" aria-roledescription="email">
    <div class="header">
      <h2 style="margin:0; font-size:18px;">{{ $payload['title'] ?? 'Welcome to Social Feed!' }}</h2>
    </div>

    <div class="body">
      @if(!empty($payload['nickname']))
        <p>Hi {{ '@'.e($payload['nickname']) }}, your registration code is <b>{{ $payload['activation_code'] }}</b></p>
      @endif

      <p>{!! nl2br(e($payload['message'] ?? 'Thanks for registering.')) !!}</p>

      @if(!empty($payload['cta_url']))
        <p style="margin-top:18px;">
          <a href="{{ $payload['cta_url'] }}" class="btn" target="_blank" rel="noopener noreferrer">{{ $payload['cta_text'] ?? 'Get started' }}</a>
        </p>
      @endif

      <p style="margin-top:18px; color:#666; font-size:13px;">
        If you didn't expect this email, you can safely ignore it.
      </p>
    </div>

    <div class="footer">
      &copy; {{ date('Y') }} {{ config('app.name') }}
    </div>
  </div>
</body>
</html>