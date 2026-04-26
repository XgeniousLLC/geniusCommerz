@props(['text'])
<span x-data="{ open: false }"
      @mouseenter="open = true"
      @mouseleave="open = false"
      class="relative inline-flex items-center ml-1">
    <span class="w-4 h-4 rounded-full bg-gray-200 text-gray-500 hover:bg-gray-300 cursor-default flex items-center justify-center flex-shrink-0 select-none"
          style="font-size:10px; font-weight:700; line-height:1;">?</span>
    <div x-show="open"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute bottom-6 left-1/2 -translate-x-1/2 z-50 w-56 bg-gray-800 text-white text-xs rounded-lg px-3 py-2 shadow-xl pointer-events-none"
         style="display:none;">
        {{ $text }}
        <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-800"></div>
    </div>
</span>
