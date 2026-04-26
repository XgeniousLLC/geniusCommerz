@php
  $hasChildren = !empty($item['children']);
  $isDropdown  = $hasChildren;
@endphp

<li class="menu-item-row" data-id="{{ $item['id'] }}">

  {{-- Item row --}}
  <div x-data="{ editing: false, expanded: true }" class="group">

    <div class="flex items-center gap-1.5 px-2.5 py-2 rounded-lg border bg-white hover:border-gray-200 hover:bg-gray-50 transition-colors
                {{ $isDropdown ? 'border-blue-100 bg-blue-50/30' : 'border-gray-100' }}">

      {{-- Expand/collapse toggle for parents --}}
      @if($isDropdown)
      <button type="button" @click="expanded = !expanded"
              class="shrink-0 w-5 h-5 flex items-center justify-center text-blue-400 hover:text-blue-600 transition-colors rounded">
        <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="expanded ? '' : '-rotate-90'"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
        </svg>
      </button>
      @else
      <span class="shrink-0 w-5 h-5 flex items-center justify-center text-gray-200">
        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
      </span>
      @endif

      {{-- Drag handle --}}
      <span class="drag-handle cursor-grab active:cursor-grabbing text-gray-300 hover:text-gray-500 shrink-0" title="Drag to reorder">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
        </svg>
      </span>

      {{-- View mode --}}
      <div x-show="!editing" class="flex-1 min-w-0 flex items-center gap-2">
        @if($isDropdown)
          <svg class="w-3.5 h-3.5 text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16"/>
          </svg>
        @endif
        <span class="text-sm font-semibold text-gray-800 truncate">{{ $item['label'] }}</span>
        @if($item['url'])
          <span class="text-xs text-gray-400 truncate hidden md:block max-w-[140px]">{{ $item['url'] }}</span>
        @endif
        @if($item['target'] === '_blank')
          <span class="text-xs text-gray-300 shrink-0" title="Opens in new tab">↗</span>
        @endif
        @if($isDropdown)
          <span class="text-[10px] font-semibold uppercase tracking-wide px-1.5 py-0.5 rounded-full bg-blue-100 text-blue-600 shrink-0">
            Dropdown · {{ count($item['children']) }}
          </span>
        @else
          <span class="text-[10px] text-gray-300 capitalize">{{ $item['type'] }}</span>
        @endif
      </div>

      {{-- Edit form --}}
      <form x-show="editing" x-cloak method="POST" action="{{ route('admin.menus.items.update', $item['id']) }}"
            class="flex-1 flex flex-wrap items-center gap-1.5">
        @csrf @method('PATCH')
        <input name="label" value="{{ $item['label'] }}"
               class="border border-gray-200 rounded px-2 py-1 text-sm flex-1 min-w-[100px] focus:ring-1 focus:ring-blue-400 focus:outline-none" required>
        <input name="url" value="{{ $item['url'] ?? '' }}" placeholder="URL (optional)"
               class="border border-gray-200 rounded px-2 py-1 text-sm flex-1 min-w-[120px] focus:ring-1 focus:ring-blue-400 focus:outline-none">
        <select name="target" class="border border-gray-200 rounded px-2 py-1 text-xs">
          <option value="_self"  @selected($item['target'] === '_self')>Same tab</option>
          <option value="_blank" @selected($item['target'] === '_blank')>New tab</option>
        </select>
        <button type="submit" class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded">Save</button>
      </form>

      {{-- Actions --}}
      <div class="flex items-center gap-0.5 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
        <button type="button" @click="editing = !editing"
                class="text-xs text-blue-500 hover:text-blue-700 px-1.5 py-1 rounded hover:bg-blue-50"
                x-text="editing ? 'Cancel' : 'Edit'"></button>
        <button type="button"
                onclick="setSubItemParent({{ $item['id'] }}, '{{ addslashes($item['label']) }}')"
                class="text-xs text-gray-400 hover:text-green-600 px-1.5 py-1 rounded hover:bg-green-50"
                title="Add sub-item under this">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
        </button>
        <form method="POST" action="{{ route('admin.menus.items.destroy', $item['id']) }}"
              onsubmit="return confirm('Remove «{{ addslashes($item['label']) }}»{{ $isDropdown ? ' and its '.count($item['children']).' sub-item(s)' : '' }}?')">
          @csrf @method('DELETE')
          <button type="submit" class="text-xs text-gray-300 hover:text-red-500 px-1.5 py-1 rounded hover:bg-red-50"
                  title="Delete">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </form>
      </div>
    </div>

    {{-- Children list --}}
    <div x-show="expanded" class="relative mt-0.5 ml-6">
      {{-- Vertical connecting line --}}
      <div class="absolute left-0 top-0 bottom-0 w-px bg-blue-100 -ml-2.5"></div>

      <ul class="children-list space-y-0.5 {{ $hasChildren ? '' : 'min-h-[32px]' }}">
        @if($hasChildren)
          @foreach($item['children'] as $child)
            @include('admin.menus._item', ['item' => $child, 'depth' => $depth + 1])
          @endforeach
        @endif

        {{-- Drop zone placeholder (shown when empty) --}}
        @if(!$hasChildren)
        <li class="drop-placeholder px-3 py-2 rounded border border-dashed border-gray-200 text-xs text-gray-300 text-center select-none">
          drag items here to nest
        </li>
        @endif
      </ul>
    </div>

  </div>
</li>
