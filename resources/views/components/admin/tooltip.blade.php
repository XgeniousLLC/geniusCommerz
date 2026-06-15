@props(['text'])

<span x-data="{ open: false }"
      @mouseenter="open = true"
      @mouseleave="open = false"
      style="position:relative;display:inline-flex;align-items:center;margin-left:4px">
    <span class="ico faint" data-ico="info" style="width:15px;height:15px;cursor:help"></span>
    <span x-show="open"
          x-transition
          style="position:absolute;bottom:calc(100% + 6px);left:50%;transform:translateX(-50%);background:var(--text);color:#fff;font-size:12px;font-weight:500;padding:5px 9px;border-radius:7px;white-space:nowrap;pointer-events:none;z-index:100;line-height:1.4">
        {{ $text }}
    </span>
</span>
