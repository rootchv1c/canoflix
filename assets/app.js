/* =========================================
   STREAMFLIX — APP.JS
   ========================================= */

// ========= NAVBAR SCROLL =========
const navbar = document.getElementById('navbar');
if (navbar) {
  window.addEventListener('scroll', () => {
    navbar.classList.toggle('scrolled', window.scrollY > 20);
  }, { passive: true });
  navbar.classList.toggle('scrolled', window.scrollY > 20);
}

// ========= MOBILE MENU =========
function toggleMobileMenu() {
  const links = document.getElementById('navLinks');
  const ham = document.getElementById('hamburger');
  if (links) links.classList.toggle('open');
  if (ham) ham.classList.toggle('open');
}

function toggleNavSearch() {
  const bar = document.getElementById('navSearchBar');
  if (bar) {
    bar.classList.toggle('open');
    if (bar.classList.contains('open')) {
      const inp = document.getElementById('navSearchInput');
      if (inp) inp.focus();
    }
  }
}

function toggleUserMenu() {
  // handled by CSS hover, JS fallback for mobile
}

// Nav search handler
const navSearchInp = document.getElementById('navSearchInput');
if (navSearchInp) {
  navSearchInp.addEventListener('input', debounce(async function() {
    const q = this.value.trim();
    const resultsEl = document.getElementById('navSearchResults');
    if (!resultsEl) return;
    if (q.length < 2) { resultsEl.innerHTML = ''; return; }
    const results = await fetchSearch(q);
    renderSearchResults(results, resultsEl);
  }, 300));
}

// ========= HERO =========
let heroIndex = 0;
let heroInterval = null;
let heroData = [];

function initHero(films) {
  heroData = films;
  if (!films || films.length === 0) return;
  
  const pag = document.getElementById('heroPagination');
  if (pag) {
    pag.innerHTML = films.map((_, i) => 
      `<button class="hero-dot ${i===0?'active':''}" onclick="setHero(${i})"></button>`
    ).join('');
  }
  
  setHero(0);
  heroInterval = setInterval(() => setHero((heroIndex + 1) % films.length), 6000);
}

function setHero(idx) {
  heroIndex = idx;
  const film = heroData[idx];
  if (!film) return;
  
  const bg = document.getElementById('heroBg');
  const title = document.getElementById('heroTitle');
  const desc = document.getElementById('heroDesc');
  
  if (bg) {
    const img = film.tmdb?.backdrop || film.logo || '';
    bg.style.backgroundImage = img ? `url('${img}')` : '';
  }
  if (title) title.textContent = film.tmdb?.title || film.name || '';
  if (desc) {
    desc.textContent = film.tmdb?.overview || 
      (film.genres ? film.genres.join(' · ') : '');
  }
  
  document.querySelectorAll('.hero-dot').forEach((d, i) => {
    d.classList.toggle('active', i === idx);
  });
}

window.handleHeroPlay = function() {
  const film = heroData[heroIndex];
  if (!film) return;
  if (film.type === 'dizi') {
    openShow(film.name);
  } else {
    openFilm(film.name);
  }
};

window.handleHeroInfo = function() {
  const film = heroData[heroIndex];
  if (!film) return;
  if (film.type === 'dizi') openShow(film.name);
  else openFilm(film.name);
};

// ========= SEARCH =========
async function fetchSearch(q) {
  try {
    const r = await fetch(`api/search.php?q=${encodeURIComponent(q)}`);
    return await r.json();
  } catch { return []; }
}

function renderSearchResults(results, container) {
  if (!results || results.length === 0) {
    container.innerHTML = '<div style="padding:1rem;color:#7a8599;text-align:center;font-size:0.9rem">Sonuç bulunamadı</div>';
    container.classList.add('open');
    return;
  }
  container.innerHTML = results.map(r => `
    <div class="search-result-item" onclick="${r.type === 'film' ? `openFilm(${JSON.stringify(r.name)})` : `openShow(${JSON.stringify(r.name)})`}">
      <img class="search-result-img" src="${escHtml(r.logo || '')}" 
           onerror="this.src='assets/placeholder.svg'" alt="${escHtml(r.name)}">
      <div class="search-result-info">
        <div class="search-result-name">${escHtml(r.name)}</div>
        <div class="search-result-meta">
          ${r.type === 'dizi' ? `${r.season_count||''} Sezon · ${r.episode_count||''} Bölüm` : 
            (r.genres ? r.genres.slice(0,2).join(' · ') : '')}
        </div>
      </div>
      <span class="search-result-type type-${r.type}">${r.type === 'film' ? 'Film' : 'Dizi'}</span>
    </div>
  `).join('');
  container.classList.add('open');
}

window.handleSearch = async function() {
  const inp = document.getElementById('searchInput');
  const clear = document.getElementById('searchClear');
  const results = document.getElementById('searchResults');
  if (!inp) return;
  
  const q = inp.value.trim();
  if (clear) clear.classList.toggle('visible', q.length > 0);
  
  if (q.length < 2) {
    if (results) { results.classList.remove('open'); results.innerHTML = ''; }
    return;
  }
  
  const data = await fetchSearch(q);
  if (results) renderSearchResults(data, results);
};

window.clearSearch = function() {
  const inp = document.getElementById('searchInput');
  const clear = document.getElementById('searchClear');
  const results = document.getElementById('searchResults');
  if (inp) inp.value = '';
  if (clear) clear.classList.remove('visible');
  if (results) { results.classList.remove('open'); results.innerHTML = ''; }
};

// Close search on outside click
document.addEventListener('click', function(e) {
  const sc = document.querySelector('.search-container');
  const navBar = document.getElementById('navSearchBar');
  if (sc && !sc.contains(e.target)) {
    const r = document.getElementById('searchResults');
    if (r) r.classList.remove('open');
  }
  if (navBar && !navBar.contains(e.target) && !e.target.closest('.nav-search-btn')) {
    navBar.classList.remove('open');
  }
});

// ========= MODAL =========
let currentFilmData = null;
let currentSelectedAudio = null;

window.openFilm = async function(name) {
  openModal();
  try {
    const r = await fetch(`api/content.php?type=film&name=${encodeURIComponent(name)}`);
    const data = await r.json();
    if (data.error) { renderModalError(); return; }
    currentFilmData = data;
    currentSelectedAudio = data.versions?.[0]?.audio || '';
    renderFilmModal(data);
  } catch {
    renderModalError();
  }
};

window.openShow = async function(name) {
  openModal();
  try {
    const r = await fetch(`api/content.php?type=dizi&name=${encodeURIComponent(name)}`);
    const data = await r.json();
    if (data.error) { renderModalError(); return; }
    currentFilmData = data;
    renderShowModal(data);
  } catch {
    renderModalError();
  }
};

function openModal() {
  const m = document.getElementById('filmModal');
  const c = document.getElementById('filmModalContent');
  if (m) m.classList.add('open');
  if (c) c.innerHTML = '<div class="modal-loading"><div class="spinner"></div></div>';
  document.body.style.overflow = 'hidden';
}

window.closeFilmModal = function() {
  const m = document.getElementById('filmModal');
  if (m) m.classList.remove('open');
  document.body.style.overflow = '';
};

window.closeModal = function(e) {
  if (e.target === document.getElementById('filmModal')) {
    closeFilmModal();
  }
};

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closeFilmModal();
});

function renderModalError() {
  const c = document.getElementById('filmModalContent');
  if (c) c.innerHTML = `
    <div class="modal-body" style="text-align:center;padding:4rem">
      <div style="font-size:3rem;margin-bottom:1rem">😔</div>
      <h3>İçerik yüklenemedi</h3>
      <p style="color:#7a8599;margin-top:0.5rem">Lütfen tekrar deneyin.</p>
    </div>`;
}

function renderFilmModal(data) {
  const c = document.getElementById('filmModalContent');
  if (!c) return;
  
  const tmdb = data.tmdb || {};
  const backdrop = tmdb.backdrop || data.logo || '';
  const poster = tmdb.poster || data.logo || '';
  const title = tmdb.title || data.name;
  const overview = tmdb.overview || '';
  const year = tmdb.year || '';
  const rating = tmdb.rating || '';
  const runtime = tmdb.runtime ? `${Math.floor(tmdb.runtime/60)}s ${tmdb.runtime%60}dk` : '';
  const genres = [...(tmdb.genres || []), ...(data.genres || [])].filter((v,i,a)=>a.indexOf(v)===i).slice(0,5);
  const cast = tmdb.cast || [];
  const trailer = tmdb.trailer || '';
  
  const versions = data.versions || [];
  const audioTypes = [...new Set(versions.map(v => v.audio))];
  
  c.innerHTML = `
    ${backdrop ? `<img class="modal-backdrop" src="${escHtml(backdrop)}" alt="${escHtml(title)}" onerror="this.style.display='none'">` : ''}
    <div class="modal-body">
      <div style="display:flex;gap:1.5rem;align-items:flex-start;flex-wrap:wrap">
        ${poster && poster !== backdrop ? `
        <img src="${escHtml(poster)}" alt="${escHtml(title)}" 
             style="width:140px;border-radius:10px;flex-shrink:0;box-shadow:0 8px 24px rgba(0,0,0,0.4)"
             onerror="this.style.display='none'">` : ''}
        <div style="flex:1;min-width:200px">
          <h2 class="modal-title">${escHtml(title)}</h2>
          <div class="modal-meta-row">
            ${year ? `<span class="meta-chip">${year}</span>` : ''}
            ${rating && rating !== '0.0' ? `<span class="meta-chip rating">⭐ ${rating}</span>` : ''}
            ${runtime ? `<span class="meta-chip">🕐 ${runtime}</span>` : ''}
          </div>
          ${tmdb.tagline ? `<p style="color:#e50914;font-style:italic;font-size:0.9rem;margin-bottom:0.75rem">"${escHtml(tmdb.tagline)}"</p>` : ''}
          ${overview ? `<p class="modal-overview">${escHtml(overview)}</p>` : ''}
        </div>
      </div>
      
      ${genres.length > 0 ? `
      <div class="modal-genres" style="margin-top:1rem">
        ${genres.map(g => `<span class="genre-chip">${escHtml(g)}</span>`).join('')}
      </div>` : ''}
      
      ${audioTypes.length > 1 ? `
      <div class="version-selector">
        <h4>Ses Seçeneği</h4>
        <div class="version-btns">
          ${audioTypes.map((a,i) => `
          <button class="version-btn ${i===0?'active':''}" onclick="selectAudio(this, '${escHtml(a)}')">${escHtml(a)}</button>
          `).join('')}
        </div>
      </div>` : ''}
      
      <div class="modal-actions">
        <a href="izle.php?type=film&name=${encodeURIComponent(data.name)}&audio=${encodeURIComponent(currentSelectedAudio)}"
           class="btn-watch-primary" id="watchBtn">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
          İzle${currentSelectedAudio ? ' · ' + currentSelectedAudio : ''}
        </a>
        ${trailer ? `
        <a href="https://www.youtube.com/watch?v=${escHtml(trailer)}" target="_blank" class="btn-watch-secondary">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
          Fragman
        </a>` : ''}
        <button class="btn-watch-secondary" id="modalFavBtn" onclick="toggleFavorite(${JSON.stringify(data.name)}, 'film', ${JSON.stringify(data.logo)})">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
          Favori
        </button>
      </div>
      
      ${cast.length > 0 ? `
      <div class="cast-section">
        <h4>Oyuncular</h4>
        <div class="cast-list">
          ${cast.map(actor => `
          <div class="cast-item">
            <img class="cast-photo" src="${escHtml(actor.photo||'')}" 
                 alt="${escHtml(actor.name)}" onerror="this.src='assets/placeholder.svg'">
            <div class="cast-name">${escHtml(actor.name)}</div>
            ${actor.character ? `<div class="cast-char">${escHtml(actor.character)}</div>` : ''}
          </div>`).join('')}
        </div>
      </div>` : ''}
    </div>
  `;
}

window.selectAudio = function(btn, audio) {
  currentSelectedAudio = audio;
  document.querySelectorAll('.version-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  const watchBtn = document.getElementById('watchBtn');
  if (watchBtn && currentFilmData) {
    watchBtn.href = `izle.php?type=film&name=${encodeURIComponent(currentFilmData.name)}&audio=${encodeURIComponent(audio)}`;
    watchBtn.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg> İzle · ${escHtml(audio)}`;
  }
};

function renderShowModal(data) {
  const c = document.getElementById('filmModalContent');
  if (!c) return;
  
  const tmdb = data.tmdb || {};
  const backdrop = tmdb.backdrop || data.logo || '';
  const poster = tmdb.poster || data.logo || '';
  const title = tmdb.title || data.name;
  const overview = tmdb.overview || '';
  const year = tmdb.year || '';
  const rating = tmdb.rating || '';
  const genres = tmdb.genres || [];
  const cast = tmdb.cast || [];
  const seasons = data.seasons || {};
  const seasonNums = Object.keys(seasons).map(Number).sort((a,b) => a-b);
  const firstSeason = seasonNums[0] || 1;
  
  c.innerHTML = `
    ${backdrop ? `<img class="modal-backdrop" src="${escHtml(backdrop)}" alt="${escHtml(title)}" onerror="this.style.display='none'">` : ''}
    <div class="modal-body">
      <div style="display:flex;gap:1.5rem;align-items:flex-start;flex-wrap:wrap">
        ${poster && poster !== backdrop ? `
        <img src="${escHtml(poster)}" alt="${escHtml(title)}"
             style="width:140px;border-radius:10px;flex-shrink:0;box-shadow:0 8px 24px rgba(0,0,0,0.4)"
             onerror="this.style.display='none'">` : ''}
        <div style="flex:1;min-width:200px">
          <h2 class="modal-title">${escHtml(title)}</h2>
          <div class="modal-meta-row">
            ${year ? `<span class="meta-chip">${year}</span>` : ''}
            ${rating && rating !== '0.0' ? `<span class="meta-chip rating">⭐ ${rating}</span>` : ''}
            ${seasonNums.length ? `<span class="meta-chip">📺 ${seasonNums.length} Sezon</span>` : ''}
          </div>
          ${overview ? `<p class="modal-overview">${escHtml(overview)}</p>` : ''}
        </div>
      </div>
      
      ${genres.length > 0 ? `
      <div class="modal-genres" style="margin-top:1rem">
        ${genres.map(g => `<span class="genre-chip">${escHtml(g)}</span>`).join('')}
      </div>` : ''}
      
      <div class="modal-actions" style="margin-top:1rem">
        <button class="btn-watch-secondary" onclick="toggleFavorite(${JSON.stringify(data.name)}, 'dizi', ${JSON.stringify(data.logo)})">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
          Favori
        </button>
      </div>
      
      <div class="seasons-panel">
        <h4>Sezonlar & Bölümler</h4>
        <div class="season-tabs-modal">
          ${seasonNums.map(s => `
          <button class="season-tab-modal ${s===firstSeason?'active':''}" 
                  onclick="showSeasonInModal(this, ${s})">
            Sezon ${s}
          </button>`).join('')}
        </div>
        ${seasonNums.map(s => {
          const eps = seasons[s] || [];
          const unique = [];
          const seen = {};
          eps.forEach(ep => {
            if (!seen[ep.episode]) { seen[ep.episode] = true; unique.push(ep); }
          });
          unique.sort((a,b) => a.episode - b.episode);
          return `
          <div class="episodes-grid" id="showSeason-${s}" ${s!==firstSeason?'style="display:none"':''}>
            ${unique.map(ep => `
            <div class="ep-card" onclick="closeFilmModal(); window.location='izle.php?type=dizi&name=${encodeURIComponent(data.name)}&season=${s}&episode=${ep.episode}&audio=${encodeURIComponent(ep.audio||'')}'">
              <div class="ep-card-num">B${String(ep.episode).padStart(2,'0')}</div>
              <div class="ep-card-title">${escHtml(ep.name || `Bölüm ${ep.episode}`)}</div>
              ${ep.audio ? `<div class="ep-card-audio">${escHtml(ep.audio)}</div>` : ''}
            </div>`).join('')}
          </div>`;
        }).join('')}
      </div>
      
      ${cast.length > 0 ? `
      <div class="cast-section">
        <h4>Oyuncular</h4>
        <div class="cast-list">
          ${cast.map(actor => `
          <div class="cast-item">
            <img class="cast-photo" src="${escHtml(actor.photo||'')}" 
                 alt="${escHtml(actor.name)}" onerror="this.src='assets/placeholder.svg'">
            <div class="cast-name">${escHtml(actor.name)}</div>
            ${actor.character ? `<div class="cast-char">${escHtml(actor.character)}</div>` : ''}
          </div>`).join('')}
        </div>
      </div>` : ''}
    </div>
  `;
}

window.showSeasonInModal = function(btn, season) {
  document.querySelectorAll('.season-tab-modal').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('[id^="showSeason-"]').forEach(el => el.style.display = 'none');
  btn.classList.add('active');
  const el = document.getElementById(`showSeason-${season}`);
  if (el) el.style.display = 'grid';
};

// ========= FAVORITES =========
window.toggleFavorite = async function(name, type, logo) {
  try {
    const r = await fetch('api/favorite.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name, type, logo, action: 'toggle' })
    });
    const data = await r.json();
    if (data.success) {
      showToast(data.message, 'success');
      const btn = document.getElementById('modalFavBtn') || document.getElementById('favBtn');
      if (btn) btn.classList.toggle('active', data.favorited);
    }
  } catch {
    showToast('Bir hata oluştu', 'error');
  }
};

// ========= FILTER TABS =========
window.setFilter = function(filter) {
  document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
  event.target.classList.add('active');
  
  const cards = document.querySelectorAll('#yeniEklenenler .card, #populerDiziler .card');
  cards.forEach(card => {
    const type = card.dataset.type;
    if (filter === 'all') {
      card.style.display = '';
    } else if (filter === 'film') {
      card.style.display = type === 'film' ? '' : 'none';
    } else if (filter === 'dizi') {
      card.style.display = type === 'dizi' ? '' : 'none';
    } else if (filter === 'dublaj') {
      card.style.display = card.querySelector('.badge-dublaj') ? '' : 'none';
    }
  });
};

// ========= TOAST =========
function showToast(msg, type = '') {
  const t = document.getElementById('toast');
  if (!t) return;
  t.textContent = msg;
  t.className = `toast show ${type}`;
  setTimeout(() => { t.className = 'toast'; }, 3000);
}

// ========= UTILS =========
function debounce(fn, delay) {
  let timer;
  return function(...args) {
    clearTimeout(timer);
    timer = setTimeout(() => fn.apply(this, args), delay);
  };
}

function escHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

// ========= RESİM YÜKLEME TAKİBİ =========
// Resim yüklenene kadar shimmer göster, yüklenince fade-in yap
function initCardImages() {
  document.querySelectorAll('.card-img').forEach((img, i) => {
    const wrap = img.closest('.card-img-wrap');
    
    // Zaten yüklendiyse (cache) direkt göster
    if (img.complete && img.naturalWidth > 0) {
      img.classList.add('img-loaded');
      if (wrap) wrap.classList.add('loaded');
      return;
    }

    img.addEventListener('load', () => {
      img.classList.add('img-loaded');
      if (wrap) wrap.classList.add('loaded');
    }, { once: true });

    img.addEventListener('error', () => {
      // Hata durumunda placeholder göster
      img.src = 'assets/placeholder.svg';
      img.classList.add('img-loaded');
      if (wrap) wrap.classList.add('loaded');
    }, { once: true });
  });

  // Kartlara sıralı animasyon delay
  document.querySelectorAll('.card').forEach((card, i) => {
    card.style.animationDelay = (i % 12 * 0.04) + 's';
  });
}

// Sayfa yüklenince ve dinamik içerik eklenince çalıştır
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initCardImages);
} else {
  initCardImages();
}

// Dinamik olarak eklenen kartlar için (modal, vs)
const _imgObserver = new MutationObserver(() => { initCardImages(); });
_imgObserver.observe(document.body, { childList: true, subtree: true });

// ========= HORIZONTAL SCROLL WITH MOUSE =========
document.querySelectorAll('.cards-row').forEach(row => {
  let isDown = false;
  let startX;
  let scrollLeft;
  
  row.addEventListener('mousedown', e => {
    isDown = true;
    startX = e.pageX - row.offsetLeft;
    scrollLeft = row.scrollLeft;
    row.style.cursor = 'grabbing';
  });
  
  row.addEventListener('mouseleave', () => { isDown = false; row.style.cursor = ''; });
  row.addEventListener('mouseup', () => { isDown = false; row.style.cursor = ''; });
  row.addEventListener('mousemove', e => {
    if (!isDown) return;
    e.preventDefault();
    const x = e.pageX - row.offsetLeft;
    const walk = (x - startX) * 2;
    row.scrollLeft = scrollLeft - walk;
  });
});

// ========= KEŞFet DROPDOWN =========
function toggleMoreMenu(e) {
  e.stopPropagation();
  const dd = document.getElementById('moreDropdown');
  if (dd) dd.classList.toggle('open');
}
document.addEventListener('click', e => {
  const dd = document.getElementById('moreDropdown');
  if (dd && dd.classList.contains('open')) {
    if (!dd.closest('.nav-dropdown-wrap').contains(e.target)) {
      dd.classList.remove('open');
    }
  }
});
