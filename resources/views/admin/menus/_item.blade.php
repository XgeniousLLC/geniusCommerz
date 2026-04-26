@php $hasChildren = !empty($item['children']); @endphp
<li class="menu-item-row" data-id="{{ $item['id'] }}" style="{{ $depth > 0 ? 'margin-left: ' . ($depth * 24) . 'px' : '' }}">
    <div x-data="{ editing: false }" class="group flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-100 bg-white hover:border-gray-200 hover:bg-gray-50">
        {{-- Drag handle --}}
        <span class="drag-handle cursor-grab text-gray-300 hover:text-gray-400 shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
            </svg>
        </span>

        {{-- View mode --}}
        <div x-show="!editing" class="flex-1 min-w-0 flex items-center gap-2">
            <span class="text-sm font-medium text-gray-800 truncate">{{ $item['label'] }}</span>
            @if($item['url'])
                <span class="text-xs text-gray-400 truncate hidden sm:block">{{ $item['url'] }}</span>
            @endif
            @if($item['target'] === '_blank')
                <span class="text-xs text-gray-300">↗</span>
            @endif
            <span class="text-xs px-1.5 py-0.5 rounded bg-gray-100 text-gray-400 capitalize">{{ $item['type'] }}</span>
        </div>

        {{-- Edit form --}}
        <form x-show="editing" method="POST" action="{{ route('admin.menus.items.update', $item['id']) }}" class="flex-1 flex items-center gap-2" x-cloak>
            @csrf @method('PATCH')
            <input name="label" value="{{ $item['label'] }}" class="flex-1 border border-gray-200 rounded px-2 py-1 text-sm focus:ring-1 focus:ring-blue-400" required>
            <input name="url" value="{{ $item['url'] ?? '' }}" class="flex-1 border border-gray-200 rounded px-2 py-1 text-sm focus:ring-1 focus:ring-blue-400" placeholder="URL">
            <select name="target" class="border border-gray-200 rounded px-2 py-1 text-sm">
                <option value="_self" @selected($item['target'] === '_self')>Same tab</option>
                <option value="_blank" @selected($item['target'] === '_blank')>New tab</option>
            </select>
            <button type="submit" class="text-xs bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700">Save</button>
        </form>

        {{-- Actions --}}
        <div class="flex items-center gap-1 shrink-0">
            <button type="button" @click="editing = !editing" class="text-xs text-blue-500 hover:text-blue-700 px-1 py-0.5" x-text="editing ? '✕' : 'Edit'"></button>
            <form method="POST" action="{{ route('admin.menus.items.destroy', $item['id']) }}" onsubmit="return confirm('Remove this item?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-xs text-red-400 hover:text-red-600 px-1 py-0.5">✕</button>
            </form>
        </div>
    </div>

    {{-- Children --}}
    @if($hasChildren)
    <ul class="children-list mt-1 space-y-1">
        @foreach($item['children'] as $child)
            @include('admin.menus._item', ['item' => $child, 'depth' => $depth + 1])
        @endforeach
    </ul>
    @else
    <ul class="children-list mt-1 space-y-1"></ul>
    @endif
</li>
