<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Media Picker</title>
    <script>(function(){try{var t=JSON.parse(localStorage.getItem('cobalt_theme')||'{}');if(t.mode)document.documentElement.setAttribute('data-theme',t.mode);}catch(e){}})()</script>
    <link rel="stylesheet" href="{{ asset('admin/css/cobalt.css') }}">
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        html, body { height: 100%; margin: 0; background: var(--bg); }
        body { padding: 12px; box-sizing: border-box; }
    </style>
</head>
<body>
    @yield('content')
    <script src="{{ asset('admin/js/cobalt-icons.js') }}"></script>
    <script src="{{ asset('admin/js/cobalt-ui.js') }}"></script>
    @stack('scripts')
</body>
</html>
