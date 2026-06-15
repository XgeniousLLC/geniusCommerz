@php
  $hasChildren = !empty($item['children']);
  $isDropdown  = $hasChildren;
@endphp

<li class="menu-item-row" data-id="{{ $item['id'] }}">
  <div x-data="{ editing: false, expanded: true }" class="group">

    <div style="display:flex;align-items:center;gap:6px;padding:8px 10px;border-radius:8px;border:1px solid;border-color:{{ $isDropdown ? 'color-mix(in srgb,var(--info) 20%,transparent)' : 'var(--border)' }};background:{{ $isDropdown ? 'color-mix(in srgb,var(--info) 5%,transparent)' : 'var(--surface)' }};transition:background .12s">

      {{-- Expand/collapse --}}
      @if($isDropdown)
      <button type="button" @click="expanded = !expanded"
        style="width:20px;height:20px;display:flex;align-items:center;justify-content:center;border:none;background:none;cursor:pointer;color:var(--info);flex-shrink:0">
        <span class="ico" data-ico="chevronDown" style="width:13px;height:13px;transition:transform .2s" :style="expanded ? '' : 'transform:rotate(-90deg)'"></span>
      </button>
      @else
      <span style="width:20px;height:20px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
        <span style="width:6px;height:6px;border-radius:50%;background:var(--border);display:inline-block"></span>
      </span>
      @endif

      {{-- Drag handle --}}
      <span class="drag-handle" title="Drag to reorder" style="cursor:grab;color:var(--text-muted);flex-shrink:0;display:flex;align-items:center">
        <span class="ico" data-ico="gripVertical" style="width:15px;height:15px"></span>
      </span>

      {{-- View mode --}}
      <div x-show="!editing" style="flex:1;min-width:0;display:flex;align-items:center;gap:8px">
        <span style="font-weight:600;font-size:13.5px;color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $item['label'] }}</span>
        @if($item['url'])
          <span class="faint mono" style="font-size:11px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:140px">{{ $item['url'] }}</span>
        @endif
        @if($item['target'] === '_blank')
          <span class="faint" style="font-size:11px;flex-shrink:0" title="Opens in new tab">↗</span>
        @endif
        @if($isDropdown)
          <span class="pill sm info" style="flex-shrink:0">Dropdown · {{ count($item['children']) }}</span>
        @else
          <span class="faint" style="font-size:11px;text-transform:capitalize">{{ $item['type'] }}</span>
        @endif
      </div>

      {{-- Edit form (hidden until editing) --}}
      <form x-show="editing" x-cloak method="POST" action="{{ route('admin.menus.items.update', $item['id']) }}"
        style="flex:1;display:flex;flex-wrap:wrap;align-items:center;gap:6px">
        @csrf @method('PATCH')
        <input name="label" value="{{ $item['label'] }}" required
          class="input" style="flex:1;min-width:100px;height:32px;padding:4px 8px;font-size:13px">
        <input name="url" value="{{ $item['url'] ?? '' }}" placeholder="URL (optional)"
          class="input" style="flex:1;min-width:120px;height:32px;padding:4px 8px;font-size:13px">
        <select name="target" class="input" style="height:32px;font-size:12px;padding:4px 8px">
          <option value="_self"  @selected($item['target'] === '_self')>Same tab</option>
          <option value="_blank" @selected($item['target'] === '_blank')>New tab</option>
        </select>
        <button type="submit" class="btn btn-primary btn-sm" style="height:32px;font-size:12px">Save</button>
      </form>

      {{-- Actions --}}
      <div style="display:flex;align-items:center;gap:2px;flex-shrink:0;opacity:0;transition:opacity .15s" class="item-actions">
        <button type="button" @click="editing = !editing"
          class="btn-sm" style="font-size:11px;color:var(--info);padding:4px 6px;border-radius:6px;border:none;background:none;cursor:pointer"
          x-text="editing ? 'Cancel' : 'Edit'"></button>
        <button type="button"
          onclick="setSubItemParent({{ $item['id'] }}, '{{ addslashes($item['label']) }}')"
          class="icon-btn" style="width:24px;height:24px" title="Add sub-item">
          <span class="ico" data-ico="plus" style="width:13px;height:13px"></span>
        </button>
        <form method="POST" action="{{ route('admin.menus.items.destroy', $item['id']) }}"
          onsubmit="return confirm('Remove «{{ addslashes($item['label']) }}»{{ $isDropdown ? ' and its '.count($item['children']).' sub-item(s)' : '' }}?')">
          @csrf @method('DELETE')
          <button type="submit" class="icon-btn" style="width:24px;height:24px;color:var(--danger)" title="Delete">
            <span class="ico" data-ico="trash" style="width:13px;height:13px"></span>
          </button>
        </form>
      </div>
    </div>

    {{-- Children --}}
    <div x-show="expanded" style="position:relative;margin-top:2px;margin-left:24px">
      <div style="position:absolute;left:-10px;top:0;bottom:0;width:1px;background:color-mix(in srgb,var(--info) 25%,transparent)"></div>
      <ul class="children-list" style="display:flex;flex-direction:column;gap:2px;{{ $hasChildren ? '' : 'min-height:32px' }}">
        @if($hasChildren)
          @foreach($item['children'] as $child)
            @include('admin.menus._item', ['item' => $child, 'depth' => $depth + 1])
          @endforeach
        @endif
        @if(!$hasChildren)
        <li class="drop-placeholder" style="padding:8px 12px;border-radius:8px;border:1px dashed var(--border);font-size:11.5px;color:var(--text-muted);text-align:center;user-select:none">
          drag items here to nest
        </li>
        @endif
      </ul>
    </div>

  </div>
</li>

<style>
.group:hover .item-actions { opacity: 1 !important; }
[x-cloak] { display: none !important; }
</style>
