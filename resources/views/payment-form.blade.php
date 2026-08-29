<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Redirecting to {{ $label }}…</title>
    <style>
        body{font-family:system-ui,-apple-system,sans-serif;display:flex;align-items:center;
             justify-content:center;min-height:100vh;margin:0;background:#fafafa;color:#1a1a1a}
        .box{text-align:center}
        .spin{width:26px;height:26px;margin:0 auto 16px;border:2px solid #ddd;
              border-top-color:#1a1a1a;border-radius:50%;animation:s .7s linear infinite}
        @keyframes s{to{transform:rotate(360deg)}}
        button{margin-top:14px;padding:9px 18px;font-size:14px;cursor:pointer}
    </style>
</head>
<body>
    <div class="box">
        <div class="spin"></div>
        <p>Taking you to {{ $label }} to complete payment…</p>

        <form id="gw" method="POST" action="{{ $action }}">
            @foreach($fields as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <noscript><button type="submit">Continue to {{ $label }}</button></noscript>
        </form>
    </div>
    <script>document.getElementById('gw').submit();</script>
</body>
</html>
