(function(){
  function removeExisting(){ document.querySelectorAll('.sobat-confirm-backdrop').forEach(el=>el.remove()); }
  window.sobatCuteConfirm = function(options){
    if (typeof options === 'string') options = { title: options };
    const cfg = Object.assign({
      title: 'Yakin mau lanjut?',
      detail: 'Aksi ini tidak bisa dibatalkan.',
      confirmText: 'Ya, lanjut',
      cancelText: 'Batal',
      emoji: '🧸'
    }, options || {});
    removeExisting();
    return new Promise(resolve => {
      const wrap = document.createElement('div');
      wrap.className = 'sobat-confirm-backdrop';
      wrap.innerHTML = `
        <div class="sobat-confirm-card" role="dialog" aria-modal="true" aria-label="Konfirmasi SobatAnak">
          <div class="sobat-confirm-emoji">${cfg.emoji}</div>
          <h3 class="sobat-confirm-title">${cfg.title}</h3>
          <p class="sobat-confirm-detail">${cfg.detail}</p>
          <div class="sobat-confirm-actions">
            <button type="button" class="sobat-confirm-btn sobat-confirm-cancel" data-confirm-cancel>${cfg.cancelText}</button>
            <button type="button" class="sobat-confirm-btn sobat-confirm-ok" data-confirm-ok>${cfg.confirmText}</button>
          </div>
        </div>`;
      document.body.appendChild(wrap);
      const done = (value) => { wrap.remove(); resolve(value); };
      wrap.querySelector('[data-confirm-cancel]').addEventListener('click', () => done(false));
      wrap.querySelector('[data-confirm-ok]').addEventListener('click', () => done(true));
      wrap.addEventListener('click', (e) => { if (e.target === wrap) done(false); });
      document.addEventListener('keydown', function esc(e){ if(e.key === 'Escape'){ document.removeEventListener('keydown', esc); done(false); } }, { once:true });
    });
  };
  document.addEventListener('submit', async function(e){
    const form = e.target.closest('form[data-cute-confirm]');
    if(!form || form.dataset.confirmed === '1') return;
    e.preventDefault();
    const ok = await window.sobatCuteConfirm({
      title: form.dataset.cuteConfirm || 'Hapus data ini?',
      detail: form.dataset.cuteDetail || 'Data akan dihapus permanen.',
      confirmText: form.dataset.cuteOk || 'Ya, hapus',
      cancelText: form.dataset.cuteCancel || 'Batal',
      emoji: form.dataset.cuteEmoji || '🧹'
    });
    if(ok){ form.dataset.confirmed = '1'; form.submit(); }
  }, true);
})();
