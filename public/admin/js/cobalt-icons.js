/* ============================================================
   Cobalt Admin — Icon set + injector (no dependencies)
   Usage:  <span class="ico" data-ico="cart"></span>
           optional: data-stroke="2.4"  •  size via CSS width/height on .ico
   Call Icons.render(root?) after injecting dynamic HTML.
   ============================================================ */
(function () {
  const P = {
    dashboard: '<rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>',
    box: '<path d="M21 8 12 3 3 8v8l9 5 9-5V8z"/><path d="m3 8 9 5 9-5"/><path d="M12 13v8"/>',
    tag: '<path d="M3 11.5V4a1 1 0 0 1 1-1h7.5a1 1 0 0 1 .7.3l8 8a1 1 0 0 1 0 1.4l-6.5 6.5a1 1 0 0 1-1.4 0l-8-8a1 1 0 0 1-.3-.7z"/><circle cx="7.5" cy="7.5" r="1.4"/>',
    bookmark: '<path d="M6 3h12a1 1 0 0 1 1 1v16l-7-4-7 4V4a1 1 0 0 1 1-1z"/>',
    sliders: '<path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3"/><path d="M1.5 14h5M9.5 8h5M17.5 16h5"/>',
    cart: '<circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/><path d="M2.5 3h2.2l2 12.5a1.5 1.5 0 0 0 1.5 1.2h8.6a1.5 1.5 0 0 0 1.5-1.2L21 7H6"/>',
    receipt: '<path d="M5 3v18l2-1.2L9 21l2-1.2L13 21l2-1.2L17 21l2-1.2V3l-2 1.2L15 3l-2 1.2L11 3 9 4.2 7 3 5 4.2z"/><path d="M8.5 8h7M8.5 12h7M8.5 16h4"/>',
    users: '<circle cx="9" cy="8" r="3.2"/><path d="M3 20a6 6 0 0 1 12 0"/><path d="M16 5.2a3.2 3.2 0 0 1 0 6.1M21 20a6 6 0 0 0-4.5-5.8"/>',
    store: '<path d="M3.5 9 5 4h14l1.5 5"/><path d="M4 9.5a2.5 2.5 0 0 0 5 0 2.5 2.5 0 0 0 5 0 2.5 2.5 0 0 0 5 0"/><path d="M5 11v9h14v-9"/><path d="M9.5 20v-5h5v5"/>',
    doc: '<path d="M6 2.5h8l4 4V21a.5.5 0 0 1-.5.5H6A.5.5 0 0 1 5.5 21V3a.5.5 0 0 1 .5-.5z"/><path d="M14 2.5V6.5h4"/><path d="M8.5 13h7M8.5 16.5h7M8.5 9.5h2"/>',
    chart: '<path d="M3 3v17a1 1 0 0 0 1 1h17"/><path d="M7 15l3.5-4 3 2.5L20 7"/>',
    wallet: '<rect x="3" y="6" width="18" height="14" rx="2.5"/><path d="M3 10h18"/><circle cx="16.5" cy="13.5" r="1.3"/>',
    team: '<circle cx="12" cy="7" r="3"/><path d="M6 21a6 6 0 0 1 12 0"/>',
    gear: '<circle cx="12" cy="12" r="3.2"/><path d="M12 2.5v2.2M12 19.3v2.2M21.5 12h-2.2M4.7 12H2.5M18.7 5.3l-1.6 1.6M6.9 17.1l-1.6 1.6M18.7 18.7l-1.6-1.6M6.9 6.9 5.3 5.3"/>',
    system: '<rect x="3" y="4" width="18" height="12" rx="2"/><path d="M8 20h8M12 16v4"/>',
    search: '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.2-3.2"/>',
    filter: '<path d="M3 5h18l-7 8v6l-4 2v-8L3 5z"/>',
    plus: '<path d="M12 5v14M5 12h14"/>',
    edit: '<path d="M4 20h4L19 9l-4-4L4 16v4z"/><path d="M14.5 6.5 17.5 9.5"/>',
    trash: '<path d="M4 7h16M9 7V4.5h6V7M6 7l1 13h10l1-13"/><path d="M10 11v6M14 11v6"/>',
    chevDown: '<path d="m6 9 6 6 6-6"/>',
    chevRight: '<path d="m9 6 6 6-6 6"/>',
    chevLeft: '<path d="m15 6-6 6 6 6"/>',
    external: '<path d="M14 4h6v6"/><path d="M20 4 10 14"/><path d="M19 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h5"/>',
    bell: '<path d="M6 9a6 6 0 0 1 12 0c0 5 2 6 2 6H4s2-1 2-6z"/><path d="M10 20a2 2 0 0 0 4 0"/>',
    sun: '<circle cx="12" cy="12" r="4"/><path d="M12 2v2.5M12 19.5V22M22 12h-2.5M4.5 12H2M19 5l-1.8 1.8M6.8 17.2 5 19M19 19l-1.8-1.8M6.8 6.8 5 5"/>',
    moon: '<path d="M21 13A8.5 8.5 0 1 1 11 3a6.5 6.5 0 0 0 10 10z"/>',
    menu: '<path d="M4 7h16M4 12h16M4 17h16"/>',
    logout: '<path d="M15 4h3a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1h-3"/><path d="M10 17l-5-5 5-5M5 12h11"/>',
    check: '<path d="m5 12 5 5L20 6"/>',
    x: '<path d="M6 6l12 12M18 6 6 18"/>',
    truck: '<path d="M2 5.5h11v10H2zM13 8.5h4l3 3.5v3.5h-7z"/><circle cx="6" cy="18" r="1.6"/><circle cx="17" cy="18" r="1.6"/>',
    dollar: '<path d="M12 2v20"/><path d="M17 6.5C17 4.6 14.8 3.5 12 3.5S7 4.6 7 6.8s2.4 2.7 5 3.2 5 1 5 3.2-2.2 3.3-5 3.3-5-1.1-5-3"/>',
    package: '<path d="M3 8.5 12 3l9 5.5v7L12 21l-9-5.5z"/><path d="M3 8.5 12 14l9-5.5M12 14v7"/>',
    spark: '<path d="M12 2l2.4 6.5L21 11l-6.6 2.5L12 20l-2.4-6.5L3 11l6.6-2.5z"/>',
    clock: '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/>',
    arrowUp: '<path d="M12 19V5M6 11l6-6 6 6"/>',
    arrowDown: '<path d="M12 5v14M6 13l6 6 6-6"/>',
    arrowRight: '<path d="M5 12h14M13 6l6 6-6 6"/>',
    eye: '<path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
    copy: '<rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v1"/>',
    download: '<path d="M12 3v12M7 11l5 5 5-5"/><path d="M5 21h14"/>',
    upload: '<path d="M12 16V4M7 9l5-5 5 5"/><path d="M5 20h14"/>',
    image: '<rect x="3" y="3" width="18" height="18" rx="2.5"/><circle cx="8.5" cy="9" r="1.6"/><path d="m4 17 4.5-4.5 3 3L16 11l4 4.5"/>',
    star: '<path d="M12 3.5l2.6 5.6 6 .7-4.5 4.1 1.2 6L12 17.1 6.7 20l1.2-6L3.4 9.8l6-.7z"/>',
    pin: '<path d="M12 21s7-6.3 7-11a7 7 0 1 0-14 0c0 4.7 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>',
    mail: '<rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="m3.5 7 8.5 6 8.5-6"/>',
    dots: '<circle cx="6" cy="12" r="1.4"/><circle cx="12" cy="12" r="1.4"/><circle cx="18" cy="12" r="1.4"/>',
    grid: '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/>',
    list: '<path d="M8 6h13M8 12h13M8 18h13M3.5 6h.01M3.5 12h.01M3.5 18h.01"/>',
    refresh: '<path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M21 3v5h-5M21 12a9 9 0 0 1-15 6.7L3 16"/><path d="M3 21v-5h5"/>',
    bolt: '<path d="M13 2 4 14h7l-1 8 9-12h-7z"/>',
    globe: '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.5 2.5 15 0 18M12 3c-2.5 2.5-2.5 15 0 18"/>',
    shield: '<path d="M12 3 5 6v5c0 4.5 3 7.5 7 9 4-1.5 7-4.5 7-9V6l-7-3z"/><path d="m9 12 2 2 4-4"/>',
    card: '<rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="M3 9.5h18"/>',
    percent: '<path d="M19 5 5 19"/><circle cx="7.5" cy="7.5" r="2.2"/><circle cx="16.5" cy="16.5" r="2.2"/>',
    layers: '<path d="m12 3 9 5-9 5-9-5 9-5z"/><path d="m3 13 9 5 9-5M3 16l9 5 9-5"/>',
    lock: '<rect x="4.5" y="10.5" width="15" height="10" rx="2"/><path d="M8 10.5V7a4 4 0 0 1 8 0v3.5"/>',
    key: '<circle cx="8" cy="15" r="4.5"/><path d="m11 12 9-9M17 3l2.5 2.5M14 6l2.5 2.5"/>',
    minus: '<path d="M5 12h14"/>',
  };

  function svg(name, stroke) {
    const body = P[name];
    if (!body) return "";
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="' + (stroke || 2) +
      '" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + body + '</svg>';
  }

  function render(root) {
    (root || document).querySelectorAll('.ico[data-ico]').forEach(function (el) {
      if (el.dataset.rendered === "1") return;
      el.innerHTML = svg(el.dataset.ico, el.dataset.stroke);
      el.dataset.rendered = "1";
    });
  }

  window.Icons = { render: render, svg: svg, names: Object.keys(P) };
  document.addEventListener('DOMContentLoaded', function () { render(); });
})();
