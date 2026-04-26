@php $isActive = request('folder_id') == $folder->id; @endphp
<div style="padding-left: {{ $depth * 12 }}px">
    <a href="{{ route('admin.media.index', array_merge(request()->only(['picker','accept','multiple','search','type']), ['folder_id' => $folder->id])) }}"
       class="flex items-center gap-1.5 px-2 py-1.5 rounded-md text-sm {{ $isActive ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
        </svg>
        <span class="truncate">{{ $folder->name }}</span>
        <span class="ml-auto text-xs text-gray-400">{{ $folder->media_count ?? '' }}</span>
    </a>
    @foreach($folder->children as $child)
        @include('admin.media._folder-item', ['folder' => $child, 'depth' => $depth + 1])
    @endforeach
</div>
