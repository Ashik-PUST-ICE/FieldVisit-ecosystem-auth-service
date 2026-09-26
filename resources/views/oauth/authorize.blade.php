<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Authorize {{ $client->name ?? 'Application' }}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; margin: 0; padding: 24px; background: #f9fafb; }
    .card { max-width: 640px; margin: 0 auto; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; }
    h1 { font-size: 20px; margin: 0 0 16px; }
    p { color: #374151; margin: 8px 0; }
    .scopes { background: #f3f4f6; padding: 12px; border-radius: 8px; margin: 12px 0; }
    .actions { display:flex; gap:12px; margin-top: 16px; }
    button { padding: 10px 16px; border-radius: 10px; border: 0; cursor: pointer; font-weight: 600; }
    .approve { background: #16a34a; color: #fff; }
    .deny { background: #ef4444; color: #fff; }
  </style>
</head>
<body>
  <div class="card">
    <h1>Authorize <strong>{{ $client->name }}</strong>?</h1>
    <p>Signed in as <strong>{{ $user->email }}</strong></p>

    @if (!empty($scopes) && count($scopes))
      <div class="scopes">
        <p>This app is requesting:</p>
        <ul>
          @foreach ($scopes as $scope)
            <li>{{ $scope->id }} — {{ $scope->description }}</li>
          @endforeach
        </ul>
      </div>
    @else
      <p>This app is not requesting additional permissions.</p>
    @endif

    <div class="actions">
      {{-- Approve --}}
      <form method="POST" action="{{ route('passport.authorizations.approve') }}">
        @csrf
        <input type="hidden" name="state" value="{{ $request->state }}">
        <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
        <input type="hidden" name="auth_token" value="{{ $authToken }}">
        <button type="submit" class="approve">Authorize</button>
      </form>

      {{-- Deny --}}
      <form method="POST" action="{{ route('passport.authorizations.deny') }}">
        @csrf
        @method('DELETE')
        <input type="hidden" name="state" value="{{ $request->state }}">
        <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
        <input type="hidden" name="auth_token" value="{{ $authToken }}">
        <button type="submit" class="deny">Deny</button>
      </form>
    </div>
  </div>
</body>
</html>
