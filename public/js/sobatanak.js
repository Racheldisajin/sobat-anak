function rupiah(n){return 'Rp '+Number(n).toLocaleString('id-ID')}
function csrf(){return document.querySelector('meta[name="csrf-token"]')?.content || ''}
async function postJson(url, data={}){
  const res = await fetch(url,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf(),'Accept':'application/json'},body:JSON.stringify(data)});
  const json = await res.json().catch(()=>({ok:false,message:'Terjadi error.'}));
  if(!res.ok){ if(json.redirect){ toast(json.message || 'Silakan login dulu'); setTimeout(()=>location.href=json.redirect,900); return null; } toast(json.message || 'Terjadi error.'); return null; }
  return json;
}
document.addEventListener('DOMContentLoaded',()=>{
  document.querySelectorAll('[data-mobile-toggle]').forEach(b=>b.onclick=()=>document.querySelector('[data-mobile-menu]').classList.toggle('show'));
  document.querySelectorAll('[data-profile-toggle]').forEach(b=>b.onclick=()=>document.querySelector('[data-profile-menu]').classList.toggle('show'));
  function sync(data){ if(!data) return; if(data.cart_count!==undefined) document.querySelectorAll('[data-cart-count]').forEach(e=>e.textContent=data.cart_count); if(data.points!==undefined) document.querySelectorAll('[data-points]').forEach(e=>e.textContent=Number(data.points).toLocaleString('id-ID')) }

  function flyCartAnimation(source){
    const cart=document.querySelector('[data-cart-link]');
    if(!source||!cart) return;
    const start=source.getBoundingClientRect();
    const end=cart.getBoundingClientRect();
    const flyer=document.createElement('div');
    flyer.className='cart-flyer';
    flyer.innerHTML='🛍️';
    const x1=start.left+start.width/2-18;
    const y1=start.top+start.height/2-18;
    const x2=end.left+end.width/2-18;
    const y2=end.top+end.height/2-18;
    flyer.style.left=x1+'px';
    flyer.style.top=y1+'px';
    flyer.style.setProperty('--fly-x',(x2-x1)+'px');
    flyer.style.setProperty('--fly-y',(y2-y1)+'px');
    document.body.appendChild(flyer);
    cart.classList.remove('cart-pop');
    window.requestAnimationFrame(()=>flyer.classList.add('is-flying'));
    setTimeout(()=>{flyer.remove();cart.classList.add('cart-pop');setTimeout(()=>cart.classList.remove('cart-pop'),1100)},1280);
  }


  // Header product search dropdown
  const headerSearchWrap=document.querySelector('[data-site-search-wrap]');
  const headerSearch=document.querySelector('[data-site-search]');
  const headerSearchResults=document.querySelector('[data-site-search-results]');
  const headerSearchEmpty=document.querySelector('[data-site-search-empty]');
  let headerProducts=[];
  try{headerProducts=JSON.parse(document.getElementById('site-products-json')?.textContent||'[]')}catch(e){headerProducts=[]}
  function highlightName(name,q){
    const safe=String(name||''); if(!q) return safe;
    const idx=safe.toLowerCase().indexOf(q.toLowerCase());
    if(idx<0) return safe;
    return safe.slice(0,idx)+'<span class="search-highlight">'+safe.slice(idx,idx+q.length)+'</span>'+safe.slice(idx+q.length);
  }
  function openHeaderSearch(){headerSearchWrap?.classList.add('is-open')}
  function closeHeaderSearch(){setTimeout(()=>headerSearchWrap?.classList.remove('is-open'),120)}
  function renderHeaderSearch(){
    if(!headerSearch||!headerSearchResults) return;
    const q=headerSearch.value.trim().toLowerCase();
    headerSearchResults.innerHTML='';
    if(!q){headerSearchEmpty.style.display='block';openHeaderSearch();return;}
    const byBestRating=(a,b)=>(Number(b.rating||0)-Number(a.rating||0))||(Number(b.sold||0)-Number(a.sold||0));
    const starts=headerProducts.filter(p=>String(p.name||'').toLowerCase().startsWith(q)).sort(byBestRating);
    const contains=headerProducts.filter(p=>!String(p.name||'').toLowerCase().startsWith(q)&&String(p.name||'').toLowerCase().includes(q)).sort(byBestRating);
    const results=[...starts,...contains].slice(0,6);
    headerSearchEmpty.style.display=results.length?'none':'block';
    if(!results.length){headerSearchEmpty.textContent='Produk tidak ditemukan. Coba kata lain ya.';openHeaderSearch();return;}
    headerSearchEmpty.textContent='Ketik nama produk, contoh: susu, popok, botol.';
    results.forEach(p=>{
      const a=document.createElement('a');
      a.href=p.url||'/products';
      a.className='search-item';
      const headerStock=Number(p.stock||0);
      const stockText=headerStock<=0?'Stok habis':(headerStock<=5?'Stok tinggal sedikit':'Stok tersedia');
      a.innerHTML=`<img src="${p.image||''}" alt=""><span><b>${highlightName(p.name,q)}</b><small>${p.category||'Produk'} · ⭐ ${p.rating||'-'} · ${stockText}</small></span><span class="search-price">${rupiah(p.price||0)}</span>`;
      headerSearchResults.appendChild(a);
    });
    openHeaderSearch();
  }
  headerSearch?.addEventListener('input',renderHeaderSearch);
  headerSearch?.addEventListener('focus',renderHeaderSearch);
  headerSearch?.addEventListener('keydown',e=>{if(e.key==='Enter'){const first=headerSearchResults?.querySelector('.search-item');if(first){e.preventDefault();location.href=first.href}}if(e.key==='Escape')headerSearchWrap?.classList.remove('is-open')});
  headerSearch?.addEventListener('blur',closeHeaderSearch);
  document.addEventListener('click',e=>{if(headerSearchWrap&&!headerSearchWrap.contains(e.target))headerSearchWrap.classList.remove('is-open')});


  document.querySelectorAll('[data-buy]').forEach(b=>b.onclick=async(e)=>{ e?.stopPropagation(); if(b.disabled) return; const r=await postJson('/cart/add',{product_id:b.dataset.productId}); sync(r); if(r){flyCartAnimation(b)} if(r?.message) toast(r.message); });


  const modalState={productId:null};
  function closeModals(){document.querySelectorAll('.info-modal.is-open').forEach(m=>{m.classList.remove('is-open');m.setAttribute('aria-hidden','true')});document.body.classList.remove('modal-lock')}
  function openModal(m){if(!m)return;m.classList.add('is-open');m.setAttribute('aria-hidden','false');document.body.classList.add('modal-lock')}
  function productDesc(d){return `${d.name} dari kategori ${d.category} adalah pilihan praktis untuk melengkapi kebutuhan si kecil. Produk ini cocok untuk orang tua yang mencari barang aman, nyaman, dan mudah digunakan dalam rutinitas harian.`}
  document.querySelectorAll('[data-open-product]').forEach(card=>card.addEventListener('click',(e)=>{
    if(e.target.closest('[data-buy]')) return;
    const d=card.dataset; modalState.productId=d.id; modalState.stock=Number(d.stock||0);
    const m=document.querySelector('[data-product-modal]'); if(!m)return;
    m.querySelector('[data-modal-product-image]').src=d.image||'';
    m.querySelector('[data-modal-product-category]').textContent=d.category||'Produk';
    m.querySelector('[data-modal-product-name]').textContent=d.name||'Detail Produk';
    m.querySelector('[data-modal-product-rating]').textContent=`⭐ ${d.rating||'-'} rating`;
    m.querySelector('[data-modal-product-sold]').textContent=`🛒 ${Number(d.sold||0).toLocaleString('id-ID')} terjual`;
    const stock=Number(d.stock||0);
    const stockChip=m.querySelector('[data-modal-product-stock]'); if(stockChip) stockChip.textContent=stock<=0?'⛔ Stok Habis':(stock<=5?'⚠️ Stok tinggal sedikit':'📦 Stok tersedia');
    const stockOverlay=m.querySelector('[data-modal-product-stock-overlay]'); if(stockOverlay) stockOverlay.classList.toggle('hidden',stock>0);
    const buyBtn=m.querySelector('[data-modal-product-buy]'); if(buyBtn){buyBtn.disabled=stock<=0;buyBtn.textContent=stock>0?'+ Masukkan Cart':'Stok Habis';buyBtn.classList.toggle('btn-disabled',stock<=0);}
    const badge=m.querySelector('[data-modal-product-badge]'); badge.textContent=d.badge?`🏷️ ${d.badge}`:'SobatAnak Choice';
    m.querySelector('[data-modal-product-price]').textContent=rupiah(d.price||0);
    m.querySelector('[data-modal-product-desc]').textContent=productDesc(d);
    openModal(m);
  }));
  document.querySelectorAll('[data-open-article]').forEach(card=>card.addEventListener('click',()=>{
    const d=card.dataset; const m=document.querySelector('[data-article-modal]'); if(!m)return;
    m.querySelector('[data-modal-article-image]').src=d.image||'';
    m.querySelector('[data-modal-article-category]').textContent=d.category||'Artikel';
    m.querySelector('[data-modal-article-title]').textContent=d.title||'Detail Artikel';
    m.querySelector('[data-modal-article-date]').textContent=d.date||'SobatAnak';
    m.querySelector('[data-modal-article-excerpt]').textContent=d.excerpt||'';
    m.querySelector('[data-modal-article-summary]').textContent=(d.excerpt||'Artikel ini berisi panduan singkat yang bisa membantu orang tua mengambil keputusan lebih nyaman untuk kebutuhan bayi dan anak.')+' Bacalah poin-poin pentingnya, lalu terapkan sesuai usia dan kebutuhan si kecil.';
    openModal(m);
  }));
  document.querySelectorAll('[data-modal-close]').forEach(x=>x.addEventListener('click',closeModals));
  document.addEventListener('keydown',e=>{if(e.key==='Escape')closeModals()});
  document.querySelectorAll('[data-modal-product-buy]').forEach(b=>b.addEventListener('click',async(e)=>{e.stopPropagation();if(!modalState.productId||b.disabled)return;const r=await postJson('/cart/add',{product_id:modalState.productId});sync(r);if(r){flyCartAnimation(b)}if(r?.message)toast(r.message)}));

  const search=document.querySelector('[data-product-search]'), sort=document.querySelector('[data-product-sort]'), cats=document.querySelectorAll('[data-filter-cat]'), cards=document.querySelectorAll('[data-product-card]');let cat='Semua';function filter(){let q=(search?.value||'').toLowerCase();let arr=[...cards];arr.forEach(c=>{let show=(cat==='Semua'||c.dataset.category===cat)&&c.dataset.name.toLowerCase().includes(q);c.style.display=show?'':'none'});let vis=arr.filter(c=>c.style.display!=='none');if(sort){vis.sort((a,b)=>{let s=sort.value;if(s==='Harga Terendah')return +a.dataset.price- +b.dataset.price;if(s==='Harga Tertinggi')return +b.dataset.price- +a.dataset.price;if(s==='Rating Tertinggi')return +b.dataset.rating- +a.dataset.rating;if(s==='Terbaru')return +b.dataset.id- +a.dataset.id;return +b.dataset.sold- +a.dataset.sold}).forEach(c=>c.parentNode.appendChild(c))}let count=document.querySelector('[data-product-count]'); if(count) count.textContent=vis.length}
  search&&search.addEventListener('input',filter);sort&&sort.addEventListener('change',filter);cats.forEach(b=>b.onclick=()=>{cat=b.dataset.filterCat;cats.forEach(x=>x.classList.remove('btn-coral'));b.classList.add('btn-coral');filter()});filter();
  let slide=0;let slides=[...document.querySelectorAll('[data-slide]')];let dots=[...document.querySelectorAll('[data-dot]')];let timer=null;function showSlide(i){if(!slides.length)return;slide=(i+slides.length)%slides.length;slides.forEach((s,idx)=>s.classList.toggle('is-active',idx===slide));dots.forEach((d,idx)=>d.classList.toggle('is-active',idx===slide))}function restartSlider(){clearInterval(timer);timer=setInterval(()=>showSlide(slide+1),5600)}if(slides.length){showSlide(0);restartSlider();document.querySelector('[data-next]')?.addEventListener('click',()=>{showSlide(slide+1);restartSlider()});document.querySelector('[data-prev]')?.addEventListener('click',()=>{showSlide(slide-1);restartSlider()});dots.forEach(d=>d.addEventListener('click',()=>{showSlide(Number(d.dataset.dot||0));restartSlider()}))}
  document.querySelectorAll('[data-play-game]').forEach(b=>b.onclick=async()=>{ const r=await postJson('/game/play'); sync(r); if(r?.message) toast(r.message); });
  document.querySelectorAll('[data-redeem]').forEach(b=>b.onclick=async()=>{ const r=await postJson('/reward/redeem',{reward_id:b.dataset.rewardId}); sync(r); if(r?.message) toast(r.message); });
});
function toast(t){let x=document.createElement('div');x.textContent=t;x.style.cssText='position:fixed;right:22px;bottom:22px;z-index:999;background:#E8756A;color:white;padding:14px 20px;border-radius:999px;font-weight:900;box-shadow:0 14px 34px #0002';document.body.appendChild(x);setTimeout(()=>x.remove(),2300)}

// Landing AI search like Google Trends, database-driven and API-ready.
document.addEventListener('DOMContentLoaded', () => {
  const box = document.querySelector('[data-ai-search-box]');
  if (!box) return;
  const form = box.querySelector('[data-ai-search-form]');
  const input = box.querySelector('[data-ai-search-input]');
  const resultBox = box.querySelector('[data-ai-result-box]');
  const title = box.querySelector('[data-ai-result-title]');
  const desc = box.querySelector('[data-ai-result-desc]');
  const intent = box.querySelector('[data-ai-result-intent]');
  const productsWrap = box.querySelector('[data-ai-products]');
  const articlesWrap = box.querySelector('[data-ai-articles]');
  const chipsWrap = box.querySelector('[data-ai-trend-chips]');
  const fmt = (n) => 'Rp ' + Number(n || 0).toLocaleString('id-ID');

  function escapeHtml(value){
    return String(value ?? '').replace(/[&<>'"]/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[char]));
  }

  async function runAiSearch(keyword){
    const q = (keyword || input?.value || '').trim();
    if (q.length < 2) return;
    resultBox?.classList.remove('hidden');
    if (title) title.textContent = 'AI sedang mencari...';
    if (desc) desc.textContent = 'Sebentar ya, SobatAnak sedang mencocokkan produk dan artikel paling relevan.';
    if (intent) intent.textContent = 'AI';
    if (productsWrap) productsWrap.innerHTML = '<div class="ai-empty-hit">Menganalisis kebutuhan...</div>';
    if (articlesWrap) articlesWrap.innerHTML = '';

    try {
      const response = await fetch(form.action, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf(),
          'Accept': 'application/json'
        },
        body: JSON.stringify({ q })
      });
      const data = await response.json();
      if (!response.ok || !data.ok) throw new Error(data.message || 'Gagal mencari.');
      if (title) title.textContent = `Hasil untuk “${data.query}”`;
      if (desc) desc.textContent = data.recommendation || 'Berikut hasil yang cocok untuk kebutuhan kamu.';
      if (intent) intent.textContent = data.intent || 'AI';

      const products = data.products || [];
      if (productsWrap) {
        productsWrap.innerHTML = products.length ? products.map(p => `
          <a class="ai-product-hit" href="${escapeHtml(p.url || '/products')}">
            <img src="${escapeHtml(p.image || '')}" alt="${escapeHtml(p.name)}">
            <span>
              <b>${escapeHtml(p.name)}</b>
              <small>${escapeHtml(p.category || 'Produk')} · ⭐ ${escapeHtml(p.rating || '-')}</small>
              <strong>${fmt(p.price)}</strong>
              <em class="ai-stock-note">${escapeHtml(p.stock_status || 'Stok tersedia')}</em>
            </span>
          </a>
        `).join('') : '<div class="ai-empty-hit">Belum ada produk yang cocok. Coba kata lain seperti popok, botol susu, mainan edukatif, atau MPASI.</div>';
      }

      const articles = data.articles || [];
      if (articlesWrap) {
        articlesWrap.innerHTML = articles.length ? articles.map(a => `
          <a class="ai-article-hit" href="${escapeHtml(a.url || '/artikel')}">
            <small>${escapeHtml(a.category || 'Artikel')}</small>
            <b>${escapeHtml(a.title)}</b>
          </a>
        `).join('') : '';
      }
    } catch (error) {
      if (title) title.textContent = 'Pencarian belum berhasil';
      if (desc) desc.textContent = error.message || 'Coba ulangi beberapa saat lagi.';
      if (productsWrap) productsWrap.innerHTML = '<div class="ai-empty-hit">Terjadi gangguan saat membaca data. Coba ulangi ya.</div>';
      if (articlesWrap) articlesWrap.innerHTML = '';
    }
  }

  form?.addEventListener('submit', (event) => {
    event.preventDefault();
    runAiSearch();
  });

  box.querySelectorAll('[data-ai-chip]').forEach(btn => {
    btn.addEventListener('click', () => {
      if (input) input.value = btn.dataset.aiChip || btn.textContent.trim();
      runAiSearch(input?.value);
    });
  });

  fetch('/ai-search/trends', { headers: { 'Accept': 'application/json' } })
    .then(res => res.json())
    .then(data => {
      if (!data.ok || !chipsWrap || !(data.trends || []).length) return;
      chipsWrap.innerHTML = data.trends.slice(0, 5).map(t => `<button type="button" data-ai-chip="${escapeHtml(t.name)}">${escapeHtml(t.name)}</button>`).join('');
      chipsWrap.querySelectorAll('[data-ai-chip]').forEach(btn => {
        btn.addEventListener('click', () => {
          if (input) input.value = btn.dataset.aiChip || btn.textContent.trim();
          runAiSearch(input?.value);
        });
      });
    })
    .catch(() => {});
});
