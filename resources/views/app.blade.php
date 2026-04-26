<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $faviconMediaId = \App\Models\SiteSetting::get('general.favicon_media_id');
        $faviconUrl = $faviconMediaId ? \App\Models\Media::find((int)$faviconMediaId)?->getUrl() : null;
    @endphp
    @if($faviconUrl)
    <link rel="icon" href="{{ $faviconUrl }}">
    @endif
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;500;700&display=swap" rel="stylesheet">
    @viteReactRefresh
    @vite(['resources/js/storefront/main.tsx', 'resources/css/storefront.css'])
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
