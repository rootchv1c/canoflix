<?php
session_start();
require_once 'includes/auth.php';

if (!isLoggedIn()) { header('Location: login.php'); exit; }

$raw = json_decode(file_get_contents(__DIR__ . '/data/canli_tv.json'), true) ?: [];

// Kategori sıralaması - önemli olanlar önce
$categoryOrder = [
    'ULUSAL', 'HABER', 'SPOR', 'SİNEMA', 'EĞLENCE',
    'MÜZİK', 'BELGESEL', 'ÇOCUK', 'DİNİ', 'RAHATLA',
    'YEREL', 'ALMANYA', 'AZERBAYCAN', 'FİLM İZLE', '💎 BoncukTV 💎'
];

$categoryIcons = [
    'ULUSAL'       => '📺',
    'HABER'        => '📰',
    'SPOR'         => '⚽',
    'SİNEMA'       => '🎬',
    'EĞLENCE'      => '🎉',
    'MÜZİK'        => '🎵',
    'BELGESEL'     => '🌍',
    'ÇOCUK'        => '🧸',
    'DİNİ'         => '🕌',
    'RAHATLA'      => '😌',
    'YEREL'        => '📡',
    'ALMANYA'      => '🇩🇪',
    'AZERBAYCAN'   => '🇦🇿',
    'FİLM İZLE'    => '🎞️',
    '💎 BoncukTV 💎' => '💎',
];

// Gruplara ayır
$grouped = [];
foreach ($raw as $ch) {
    $cat = $ch['category'] ?? 'GENEL';
    $grouped[$cat][] = $ch;
}

// Sırala
$sorted = [];
foreach ($categoryOrder as $cat) {
    if (isset($grouped[$cat])) {
        $sorted[$cat] = $grouped[$cat];
    }
}
foreach ($grouped as $cat => $chs) {
    if (!isset($sorted[$cat])) $sorted[$cat] = $chs;
}

$totalChannels = count($raw);
$totalCategories = count($sorted);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Canlı TV — StreamFlix</title>
<link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
<style>
/* ---- CANLI TV SAYFASINA ÖZEL STILLER ---- */
.tv-hero {
  background: linear-gradient(135deg, #0a0f1a 0%, #0d1520 50%, #12001a 100%);
  padding: calc(var(--nav-h) + 2.5rem) 2rem 2rem;
  border-bottom: 1px solid var(--surface3);
  position: relative;
  overflow: hidden;
}
.tv-hero::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -20%;
  width: 600px;
  height: 600px;
  background: radial-gradient(circle, rgba(229,9,20,0.06) 0%, transparent 70%);
  pointer-events: none;
}
.tv-hero::after {
  content: '';
  position: absolute;
  bottom: -30%;
  right: 10%;
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, rgba(99,102,241,0.05) 0%, transparent 70%);
  pointer-events: none;
}
.tv-hero-inner {
  max-width: 1600px;
  margin: 0 auto;
  position: relative;
  z-index: 1;
}
.tv-hero-title {
  font-family: 'Bebas Neue', sans-serif;
  font-size: clamp(2rem, 4vw, 3.5rem);
  letter-spacing: 0.05em;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.75rem;
}
.live-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(229,9,20,0.15);
  border: 1px solid rgba(229,9,20,0.4);
  color: var(--accent);
  padding: 4px 12px;
  border-radius: 99px;
  font-family: 'Outfit', sans-serif;
  font-size: 0.78rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  animation: livePulse 2s ease-in-out infinite;
}
.live-dot {
  width: 7px;
  height: 7px;
  background: var(--accent);
  border-radius: 50%;
  animation: livePulse 2s ease-in-out infinite;
}
@keyframes livePulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}
.tv-hero-stats {
  display: flex;
  gap: 1.5rem;
  flex-wrap: wrap;
}
.tv-stat {
  color: var(--text2);
  font-size: 0.9rem;
}
.tv-stat strong { color: var(--text); }

/* Kategori çubuğu */
.cat-nav {
  background: var(--surface);
  border-bottom: 1px solid var(--surface3);
  position: sticky;
  top: var(--nav-h);
  z-index: 100;
}
.cat-nav-inner {
  max-width: 1600px;
  margin: 0 auto;
  padding: 0 2rem;
  display: flex;
  gap: 4px;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}
.cat-nav-inner::-webkit-scrollbar { height: 0; }
.cat-nav-btn {
  padding: 14px 16px;
  border-bottom: 2px solid transparent;
  color: var(--text2);
  font-size: 0.85rem;
  font-weight: 600;
  white-space: nowrap;
  transition: all var(--transition);
  background: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  flex-shrink: 0;
}
.cat-nav-btn:hover { color: var(--text); }
.cat-nav-btn.active { color: var(--accent); border-bottom-color: var(--accent); }

/* İçerik */
.tv-content {
  max-width: 1600px;
  margin: 0 auto;
  padding: 2rem;
}

/* Kanal kartı - kanallara özel (kare logo) */
.ch-card {
  background: var(--surface);
  border: 1px solid var(--surface3);
  border-radius: 12px;
  overflow: hidden;
  cursor: pointer;
  transition: transform var(--transition), box-shadow var(--transition), border-color var(--transition);
}
.ch-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 32px rgba(0,0,0,0.5);
  border-color: rgba(229,9,20,0.4);
}
.ch-logo-wrap {
  background: #0a0f1a;
  aspect-ratio: 16/9;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  position: relative;
}
.ch-logo {
  max-width: 70%;
  max-height: 70%;
  object-fit: contain;
  transition: transform var(--transition);
}
.ch-card:hover .ch-logo { transform: scale(1.08); }
.ch-live-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity var(--transition);
}
.ch-card:hover .ch-live-overlay { opacity: 1; }
.ch-play-btn {
  width: 52px;
  height: 52px;
  background: rgba(229,9,20,0.9);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  padding-left: 4px;
}
.ch-info {
  padding: 10px 12px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.ch-name {
  flex: 1;
  font-size: 0.85rem;
  font-weight: 600;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.ch-live-tag {
  flex-shrink: 0;
  font-size: 0.65rem;
  font-weight: 700;
  padding: 2px 7px;
  background: rgba(229,9,20,0.15);
  color: var(--accent);
  border-radius: 4px;
  letter-spacing: 0.05em;
}

/* Kategori bölümü */
.tv-section { margin-bottom: 3rem; scroll-margin-top: calc(var(--nav-h) + 60px); }
.tv-section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.25rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid var(--surface3);
}
.tv-section-title {
  font-family: 'Bebas Neue', sans-serif;
  font-size: 1.6rem;
  letter-spacing: 0.05em;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.tv-section-count {
  font-size: 0.82rem;
  color: var(--text2);
  background: var(--surface);
  padding: 3px 10px;
  border-radius: 99px;
  border: 1px solid var(--surface3);
}

/* Grid */
.ch-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
  gap: 12px;
}

/* Player Modal */
.tv-modal-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.9);
  backdrop-filter: blur(12px);
  z-index: 3000;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}
.tv-modal-overlay.open { display: flex; }
.tv-modal-box {
  background: var(--surface);
  border: 1px solid var(--surface3);
  border-radius: 16px;
  width: 100%;
  max-width: 1000px;
  overflow: hidden;
  animation: modalIn 0.3s cubic-bezier(0.34,1.56,0.64,1);
  box-shadow: 0 32px 80px rgba(0,0,0,0.8);
}
@keyframes modalIn {
  from { opacity: 0; transform: scale(0.92) translateY(20px); }
  to   { opacity: 1; transform: scale(1) translateY(0); }
}
.tv-player-wrap {
  background: #000;
  position: relative;
}
.tv-player-wrap video {
  width: 100%;
  aspect-ratio: 16/9;
  display: block;
}
.tv-modal-bar {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.5rem;
  flex-wrap: wrap;
}
.tv-modal-logo {
  width: 44px;
  height: 44px;
  object-fit: contain;
  background: #0a0f1a;
  border-radius: 8px;
  padding: 4px;
  flex-shrink: 0;
}
.tv-modal-info { flex: 1; min-width: 0; }
.tv-modal-name { font-size: 1.1rem; font-weight: 700; }
.tv-modal-cat { font-size: 0.82rem; color: var(--text2); margin-top: 2px; }
.tv-modal-actions { display: flex; gap: 8px; margin-left: auto; }
.tv-modal-close-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  background: var(--surface2);
  border: 1px solid var(--surface3);
  border-radius: 8px;
  color: var(--text2);
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all var(--transition);
}
.tv-modal-close-btn:hover { background: var(--surface3); color: var(--text); }

/* Loading state */
.tv-loading {
  aspect-ratio: 16/9;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  background: #000;
  color: var(--text2);
  font-size: 0.9rem;
}

/* Responsive */
@media (max-width: 768px) {
  .tv-hero { padding: calc(var(--nav-h) + 1.5rem) 1rem 1.5rem; }
  .tv-content { padding: 1.5rem 1rem; }
  .ch-grid { grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 10px; }
  .cat-nav-inner { padding: 0 1rem; }
  .tv-modal-bar { padding: 0.75rem 1rem; }
  .tv-modal-name { font-size: 1rem; }
}
@media (max-width: 480px) {
  .ch-grid { grid-template-columns: repeat(3, 1fr); gap: 8px; }
}
</style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<!-- HERO -->
<div class="tv-hero">
  <div class="tv-hero-inner">
    <h1 class="tv-hero-title">
      📡 Canlı TV
      <span class="live-badge"><span class="live-dot"></span> CANLI</span>
    </h1>
    <div class="tv-hero-stats">
      <span class="tv-stat"><strong><?= $totalChannels ?></strong> kanal</span>
      <span class="tv-stat"><strong><?= $totalCategories ?></strong> kategori</span>
      
    </div>
  </div>
</div>

<!-- KATEGORİ NAVİGASYON ÇUBUĞU -->
<nav class="cat-nav" id="catNav">
  <div class="cat-nav-inner">
    <?php foreach ($sorted as $cat => $chs): ?>
    <?php $icon = $categoryIcons[$cat] ?? '📺'; ?>
    <button class="cat-nav-btn" onclick="scrollToCategory('cat-<?= htmlspecialchars(preg_replace('/[^a-zA-Z0-9]/', '', $cat)) ?>')">
      <?= $icon ?> <?= htmlspecialchars($cat) ?>
      <span style="color:var(--text3);font-weight:400">(<?= count($chs) ?>)</span>
    </button>
    <?php endforeach; ?>
  </div>
</nav>

<!-- KANALLAR -->
<div class="tv-content">
  <?php foreach ($sorted as $cat => $chs): ?>
  <?php $icon = $categoryIcons[$cat] ?? '📺'; ?>
  <?php $sectionId = 'cat-' . preg_replace('/[^a-zA-Z0-9]/', '', $cat); ?>
  <section class="tv-section" id="<?= $sectionId ?>">
    <div class="tv-section-header">
      <h2 class="tv-section-title"><?= $icon ?> <?= htmlspecialchars($cat) ?></h2>
      <span class="tv-section-count"><?= count($chs) ?> kanal</span>
    </div>
    <div class="ch-grid">
      <?php foreach ($chs as $ch): ?>
      <div class="ch-card"
           onclick="playChannelFull(<?= htmlspecialchars(json_encode([
             'name' => $ch['name'],
             'url'  => $ch['url'],
             'logo' => $ch['logo'],
             'category' => $ch['category']
           ])) ?>)">
        <div class="ch-logo-wrap">
          <img class="ch-logo"
               src="<?= htmlspecialchars($ch['logo']) ?>"
               alt="<?= htmlspecialchars($ch['name']) ?>"
               loading="lazy"
               onerror="this.src='assets/placeholder.svg'">
          <div class="ch-live-overlay">
            <div class="ch-play-btn">▶</div>
          </div>
        </div>
        <div class="ch-info">
          <span class="ch-name"><?= htmlspecialchars($ch['name']) ?></span>
          <span class="ch-live-tag">CANLI</span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endforeach; ?>
</div>

<?php include 'includes/footer.php'; ?>

<!-- TV PLAYER MODAL -->
<div class="tv-modal-overlay" id="tvModal">
  <div class="tv-modal-box">
    <div class="tv-player-wrap" id="tvPlayerWrap">
      <div class="tv-loading" id="tvLoading">
        <div class="spinner"></div>
        <span>Kanal yükleniyor...</span>
      </div>
      <video id="tvVideo" controls playsinline style="display:none"></video>
    </div>
    <div class="tv-modal-bar">
      <img class="tv-modal-logo" id="tvModalLogo" src="" alt="">
      <div class="tv-modal-info">
        <div class="tv-modal-name" id="tvModalName"></div>
        <div class="tv-modal-cat" id="tvModalCat"></div>
      </div>
      <div class="tv-modal-actions">
        <button class="tv-modal-close-btn" onclick="closeTVModal()">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          Kapat
        </button>
      </div>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/hls.js/1.4.12/hls.min.js"></script>
<script src="assets/app.js"></script>
<script>
// Kategori nav aktif takip
const catNavBtns = document.querySelectorAll('.cat-nav-btn');
const tvSections = document.querySelectorAll('.tv-section');

const sectionObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      const id = entry.target.id;
      catNavBtns.forEach(btn => btn.classList.remove('active'));
      catNavBtns.forEach(btn => {
        if (btn.getAttribute('onclick')?.includes(id)) {
          btn.classList.add('active');
          btn.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }
      });
    }
  });
}, { rootMargin: '-20% 0px -70% 0px' });

tvSections.forEach(s => sectionObserver.observe(s));

function scrollToCategory(id) {
  const el = document.getElementById(id);
  if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ---- PLAYER ----
let hlsInstance = null;

// Proxy URL oluştur - CORS sorununu aşar
function proxyUrl(url) {
  return 'proxy.php?url=' + encodeURIComponent(url);
}

function playChannel(ch) {
  const modal   = document.getElementById('tvModal');
  const video   = document.getElementById('tvVideo');
  const loading = document.getElementById('tvLoading');
  const logo    = document.getElementById('tvModalLogo');
  const nameEl  = document.getElementById('tvModalName');
  const catEl   = document.getElementById('tvModalCat');

  modal.classList.add('open');
  document.body.style.overflow = 'hidden';
  loading.style.display = 'flex';
  loading.innerHTML = '<div class="spinner"></div><span>Kanal yükleniyor...</span>';
  video.style.display = 'none';

  logo.src = ch.logo || 'assets/placeholder.svg';
  logo.onerror = () => logo.src = 'assets/placeholder.svg';
  nameEl.textContent = ch.name;
  catEl.textContent  = ch.category + ' · 🔴 CANLI';

  if (hlsInstance) { hlsInstance.destroy(); hlsInstance = null; }
  video.src = '';

  // YouTube mu?
  const ytMatch = ch.url.match(/(?:youtube\.com\/(?:live\/|watch\?v=)|youtu\.be\/)([\w\-]{10,12})/);
  if (ytMatch) {
    // YouTube iframe embed
    loading.style.display = 'none';
    const existing = document.getElementById('tvYoutubeFrame');
    if (existing) existing.remove();
    const iframe = document.createElement('iframe');
    iframe.id = 'tvYoutubeFrame';
    iframe.src = `https://www.youtube.com/embed/${ytMatch[1]}?autoplay=1&rel=0&modestbranding=1`;
    iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
    iframe.allowFullscreen = true;
    iframe.style.cssText = 'width:100%;aspect-ratio:16/9;border:none;display:block';
    document.getElementById('tvPlayerWrap').prepend(iframe);
    return;
  }

  // HLS stream
  video.poster = ch.logo || '';
  video.style.display = 'none';

  // Stream URL'yi proxy üzerinden geçir
  const streamUrl = proxyUrl(ch.url);

  function tryLoad(url, retryDirect) {
    if (!Hls.isSupported()) {
      // Safari: direkt native HLS
      video.src = url;
      loading.style.display = 'none';
      video.style.display = 'block';
      video.play().catch(() => {});
      return;
    }

    hlsInstance = new Hls({
      maxLoadingDelay: 10,
      maxBufferLength: 30,
      enableWorker: true,
      xhrSetup: function(xhr) {
        xhr.timeout = 15000;
      }
    });

    hlsInstance.loadSource(url);
    hlsInstance.attachMedia(video);

    hlsInstance.on(Hls.Events.MANIFEST_PARSED, () => {
      loading.style.display = 'none';
      video.style.display = 'block';
      video.play().catch(() => {});
    });

    hlsInstance.on(Hls.Events.ERROR, (event, data) => {
      if (data.fatal) {
        hlsInstance.destroy();
        hlsInstance = null;

        if (retryDirect) {
          // Proxy başarısız olduysa direkt dene (bazı kanallar CORS vermiyor ama native oynatabilir)
          loading.style.display = 'flex';
          loading.innerHTML = '<div class="spinner"></div><span>Bağlanıyor...</span>';
          video.style.display = 'none';
          tryLoad(ch.url, false);
        } else {
          loading.innerHTML = `
            <div style="font-size:2.5rem;margin-bottom:0.5rem">📡</div>
            <span style="font-size:1rem;font-weight:600;color:#e8eaf0">Kanal şu an erişilemiyor</span>
            <span style="font-size:0.85rem;color:#94a3b8;margin-top:4px">Sunucu geçici olarak kapalı olabilir</span>
            <button onclick="retryChannel()" style="margin-top:1rem;padding:8px 20px;background:#e50914;color:white;border:none;border-radius:8px;cursor:pointer;font-size:0.9rem;font-weight:600">
              🔄 Tekrar Dene
            </button>`;
        }
      }
    });
  }

  tryLoad(streamUrl, true);
}

// Mevcut kanalı yeniden dene
window._currentChannel = null;
window.playChannelFull = function(ch) {
  window._currentChannel = ch;
  playChannel(ch);
};

function retryChannel() {
  if (window._currentChannel) playChannel(window._currentChannel);
}

function closeTVModal() {
  if (hlsInstance) { hlsInstance.destroy(); hlsInstance = null; }
  const video = document.getElementById('tvVideo');
  if (video) { video.pause(); video.src = ''; video.style.display = 'none'; }
  // YouTube frame temizle
  const ytFrame = document.getElementById('tvYoutubeFrame');
  if (ytFrame) ytFrame.remove();
  const loading = document.getElementById('tvLoading');
  loading.style.display = 'flex';
  loading.innerHTML = '<div class="spinner"></div><span>Kanal yükleniyor...</span>';
  document.getElementById('tvModal').classList.remove('open');
  document.body.style.overflow = '';
}

document.getElementById('tvModal').addEventListener('click', function(e) {
  if (e.target === this) closeTVModal();
});

document.addEventListener('keydown', e => {
  if (e.key === 'Escape') closeTVModal();
});
</script>
</body>
</html>
