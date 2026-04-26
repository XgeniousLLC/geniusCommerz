@extends('admin.layouts.admin')

@section('title', 'Sitemap')

@section('breadcrumbs')
    <ol class="flex items-center space-x-2 text-sm text-gray-500">
        <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
        <li><span class="mx-1">/</span></li>
        <li class="text-gray-900 font-medium">Sitemap</li>
    </ol>
@endsection

@section('page-header')
    <h1 class="text-2xl font-bold text-gray-900">Sitemap Generator</h1>
    <p class="text-gray-600 mt-1">Generate <code>sitemap.xml</code> for all active products, categories, blog posts, and pages.</p>
@endsection

@section('content')
<x-admin.card>
    <div class="space-y-5">

        <div class="flex items-start gap-4">
            <div class="flex-1">
                @if($exists)
                <p class="text-sm text-gray-700">
                    Current sitemap: <a href="{{ url('sitemap.xml') }}" target="_blank" class="text-blue-600 hover:underline font-medium">/sitemap.xml</a>
                    &nbsp;·&nbsp; last generated <strong>{{ $lastUpdate }}</strong>
                </p>
                @else
                <p class="text-sm text-gray-500">No sitemap found. Click the button to generate one.</p>
                @endif
                <p class="text-xs text-gray-400 mt-1">
                    The sitemap includes: all active products, root-level categories, published blog posts, published CMS pages, and key static URLs.
                </p>
            </div>

            <form method="POST" action="{{ route('admin.sitemap.generate') }}">
                @csrf
                <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Generate Sitemap
                </button>
            </form>
        </div>

        <hr class="border-gray-100">

        <div>
            <h4 class="text-sm font-semibold text-gray-700 mb-2">Search Engine Submission</h4>
            <p class="text-xs text-gray-500 mb-3">After generating, submit your sitemap URL to search engines:</p>
            <div class="flex flex-wrap gap-3">
                <a href="https://search.google.com/search-console" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-1.5 text-xs text-blue-600 hover:underline">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    Google Search Console
                </a>
                <a href="https://www.bing.com/webmasters" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-1.5 text-xs text-blue-600 hover:underline">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    Bing Webmaster Tools
                </a>
            </div>
            <p class="text-xs text-gray-400 mt-3">
                Sitemap URL: <code class="bg-gray-100 px-1 rounded">{{ url('sitemap.xml') }}</code>
            </p>
        </div>

    </div>
</x-admin.card>
@endsection
