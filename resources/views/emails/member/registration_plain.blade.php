{{ $payload['title'] ?? 'Welcome to ' . config('app.name') }}

@if(!empty($payload['nickname']))
Hi @{{ $payload['nickname'] }}, your registration code is {{ $payload['activation_code'] }}
@endif

{{ $payload['message'] ?? "Thanks for registering." }}

@if(!empty($payload['cta_url']))
Open: {{ $payload['cta_url'] }}
@endif

--
{{ config('app.name') }}