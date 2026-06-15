/* ============================================================
   Cobalt Admin — Shared UI behaviours (vanilla, attribute-driven)
   • Dropdown menus  : [data-menu-toggle] + sibling .menu
   • Tabs            : [data-tabs] > [data-tab="x"] ↔ [data-panel="x"]
   • Modals          : [data-open-modal="id"] / [data-close-modal] / #id.modal-scrim
   • Toggles         : .toggle  (click flips .on)
   • Segmented       : .seg button (click sets .active among siblings)
   • Toasts          : window.toast("Saved", "success"|"danger"|"info")
   ============================================================ */
(function () {
  /* ---- Dropdown menus ---- */
  function closeMenus(except) {
    document.querySelectorAll('.menu-wrap.open').forEach(function (w) {
      if (w !== except) { w.classList.remove('open'); const m = w.querySelector('.menu'); if (m) m.hidden = true; }
    });
  }
  document.addEventListener('click', function (e) {
    const toggle = e.target.closest('[data-menu-toggle]');
    if (toggle) {
      e.preventDefault();
      const wrap = toggle.closest('.menu-wrap');
      const menu = wrap.querySelector('.menu');
      const open = wrap.classList.contains('open');
      closeMenus(open ? null : wrap);
      wrap.classList.toggle('open', !open);
      if (menu) menu.hidden = open;
      return;
    }
    if (!e.target.closest('.menu')) closeMenus(null);
  });

  /* ---- Tabs ---- */
  document.querySelectorAll('[data-tabs]').forEach(function (group) {
    const scope = group.dataset.tabs;
    group.addEventListener('click', function (e) {
      const btn = e.target.closest('[data-tab]');
      if (!btn) return;
      const val = btn.dataset.tab;
      group.querySelectorAll('[data-tab]').forEach(function (b) { b.classList.toggle('active', b === btn); });
      document.querySelectorAll('[data-panel]').forEach(function (p) {
        if (p.dataset.scope && p.dataset.scope !== scope) return;
        if (p.closest('[data-tabs]') && p.closest('[data-tabs]') !== group && !p.dataset.scope) return;
        p.hidden = (p.dataset.panel !== val);
      });
    });
  });

  /* ---- Modals ---- */
  function openModal(id) { const m = document.getElementById(id); if (m) { m.hidden = false; if (window.Icons) Icons.render(m); } }
  function closeModal(m) { if (m) m.hidden = true; }
  document.addEventListener('click', function (e) {
    const opener = e.target.closest('[data-open-modal]');
    if (opener) { e.preventDefault(); openModal(opener.dataset.openModal); return; }
    const closer = e.target.closest('[data-close-modal]');
    if (closer) { e.preventDefault(); closeModal(closer.closest('.modal-scrim')); return; }
    if (e.target.classList && e.target.classList.contains('modal-scrim')) closeModal(e.target);
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') { document.querySelectorAll('.modal-scrim:not([hidden])').forEach(closeModal); closeMenus(null); }
  });
  window.Modal = { open: openModal, close: function (id) { closeModal(document.getElementById(id)); } };

  /* ---- Toggles ---- */
  document.addEventListener('click', function (e) {
    const t = e.target.closest('.toggle');
    if (!t) return;
    t.classList.toggle('on');
    t.setAttribute('aria-pressed', t.classList.contains('on'));
    if (t.dataset.toast) window.toast(t.dataset.toast + (t.classList.contains('on') ? ' enabled' : ' disabled'));
  });

  /* ---- Segmented ---- */
  document.querySelectorAll('.seg').forEach(function (seg) {
    seg.addEventListener('click', function (e) {
      const b = e.target.closest('button'); if (!b) return;
      seg.querySelectorAll('button').forEach(function (x) { x.classList.toggle('active', x === b); });
    });
  });

  /* ---- Toasts ---- */
  window.toast = function (msg, tone) {
    let host = document.getElementById('toastHost');
    if (!host) { host = document.createElement('div'); host.id = 'toastHost'; host.className = 'toast-host'; document.body.appendChild(host); }
    const el = document.createElement('div');
    el.className = 'toast' + (tone && tone !== 'success' ? ' ' + tone : '');
    el.innerHTML = '<span class="ti"><span class="ico" data-ico="' + (tone === 'danger' ? 'x' : 'check') + '" style="width:16px;height:16px"></span></span><span>' + msg + '</span>';
    host.appendChild(el);
    if (window.Icons) Icons.render(el);
    setTimeout(function () { el.style.opacity = '0'; el.style.transform = 'translateX(40px)'; el.style.transition = 'all .25s'; }, 2600);
    setTimeout(function () { el.remove(); }, 2900);
  };

  /* ---- Generic [data-toast] click (buttons that just confirm) ---- */
  document.addEventListener('click', function (e) {
    const b = e.target.closest('[data-toast]:not(.toggle)');
    if (b) window.toast(b.dataset.toast, b.dataset.toastTone || 'success');
  });
})();
