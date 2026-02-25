<?php
session_start();
require_once 'includes/auth.php';
if (!isLoggedIn()) { header('Location: login.php'); exit; }

$raw = json_decode(file_get_contents(__DIR__ . '/data/muzik.json'), true) ?: [];

$categoryOrder = ['Türk Radyoları','Kanallar','80s 90s','Stingray Music','Dünya Radyoları','Klassik Radio'];
$categoryIcons = [
    'Türk Radyoları'  => '🇹🇷',
    'Kanallar'        => '📻',
    '80s 90s'         => '🎸',
    'Stingray Music'  => '🎵',
    'Dünya Radyoları' => '🌍',
    'Klassik Radio'   => '🎻',
];

$grouped = [];
foreach ($raw as $s) {
    $grouped[$s['category']][] = $s;
}
$sorted = [];
foreach ($categoryOrder as $cat) {
    if (isset($grouped[$cat])) $sorted[$cat] = $grouped[$cat];
}
foreach ($grouped as $cat => $ss) {
    if (!isset($sorted[$cat])) $sorted[$cat] = $ss;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Müzik — StreamFlix</title>
<link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
<style>
/* ===== MÜZİK SAYFASINA ÖZEL ===== */
body { padding-bottom: 100px; } /* alt player için yer */

/* HERO */
.music-hero {
  padding: calc(var(--nav-h) + 2.5rem) 2rem 2.5rem;
  background: linear-gradient(135deg, #080c14 0%, #0a0d1a 40%, #0d0a1f 100%);
  border-bottom: 1px solid var(--surface3);
  position: relative;
  overflow: hidden;
}
.music-hero::before {
  content:'';
  position:absolute;
  top:-100px; left:-100px;
  width:500px; height:500px;
  background: radial-gradient(circle, rgba(139,92,246,0.08) 0%, transparent 70%);
  pointer-events:none;
}
.music-hero::after {
  content:'';
  position:absolute;
  bottom:-100px; right:5%;
  width:400px; height:400px;
  background: radial-gradient(circle, rgba(236,72,153,0.06) 0%, transparent 70%);
  pointer-events:none;
}
.music-hero-inner { max-width:1600px; margin:0 auto; position:relative; z-index:1; }
.music-hero-title {
  font-family:'Bebas Neue',sans-serif;
  font-size: clamp(2rem,4vw,3.5rem);
  letter-spacing:.05em;
  display:flex; align-items:center; gap:.75rem;
  margin-bottom:.75rem;
}
.music-live-badge {
  display:inline-flex; align-items:center; gap:6px;
  background:rgba(139,92,246,0.15);
  border:1px solid rgba(139,92,246,0.4);
  color:#a78bfa;
  padding:4px 12px; border-radius:99px;
  font-family:'Outfit',sans-serif;
  font-size:.78rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase;
}
.music-pulse { width:7px; height:7px; background:#a78bfa; border-radius:50%; animation:livePulse 2s ease-in-out infinite; }
@keyframes livePulse { 0%,100%{opacity:1} 50%{opacity:.4} }
.music-hero-stats { display:flex; gap:1.5rem; flex-wrap:wrap; }
.music-stat { color:var(--text2); font-size:.9rem; }
.music-stat strong { color:var(--text); }

/* KATEGORİ NAVBAR */
.music-cat-nav {
  background:var(--surface);
  border-bottom:1px solid var(--surface3);
  position:sticky; top:var(--nav-h); z-index:100;
}
.music-cat-nav-inner {
  max-width:1600px; margin:0 auto; padding:0 2rem;
  display:flex; gap:4px; overflow-x:auto;
}
.music-cat-nav-inner::-webkit-scrollbar { height:0; }
.music-cat-btn {
  padding:14px 18px;
  border-bottom:2px solid transparent;
  color:var(--text2); font-size:.85rem; font-weight:600;
  white-space:nowrap; background:none; cursor:pointer;
  display:flex; align-items:center; gap:6px;
  transition:all var(--transition); flex-shrink:0;
}
.music-cat-btn:hover { color:var(--text); }
.music-cat-btn.active { color:#a78bfa; border-bottom-color:#a78bfa; }

/* İÇERİK */
.music-content { max-width:1600px; margin:0 auto; padding:2rem; }
.music-section { margin-bottom:3rem; scroll-margin-top:calc(var(--nav-h) + 60px); }
.music-section-header {
  display:flex; align-items:center; justify-content:space-between;
  margin-bottom:1.25rem; padding-bottom:.75rem;
  border-bottom:1px solid var(--surface3);
}
.music-section-title {
  font-family:'Bebas Neue',sans-serif;
  font-size:1.6rem; letter-spacing:.05em;
  display:flex; align-items:center; gap:.5rem;
}
.music-section-count {
  font-size:.82rem; color:var(--text2);
  background:var(--surface); padding:3px 10px;
  border-radius:99px; border:1px solid var(--surface3);
}

/* İSTASYON GRID */
.station-grid {
  display:grid;
  grid-template-columns:repeat(auto-fill, minmax(200px, 1fr));
  gap:12px;
}

/* İSTASYON KARTI */
.station-card {
  background:var(--surface);
  border:1px solid var(--surface3);
  border-radius:12px;
  padding:16px;
  cursor:pointer;
  display:flex; align-items:center; gap:14px;
  transition:all var(--transition);
  position:relative;
  overflow:hidden;
}
.station-card::before {
  content:'';
  position:absolute; inset:0;
  background:linear-gradient(135deg, rgba(139,92,246,0.05) 0%, transparent 100%);
  opacity:0; transition:opacity var(--transition);
}
.station-card:hover { border-color:rgba(139,92,246,0.5); transform:translateY(-2px); box-shadow:0 8px 24px rgba(0,0,0,0.4); }
.station-card:hover::before { opacity:1; }
.station-card.playing { border-color:#a78bfa; background:rgba(139,92,246,0.08); }
.station-card.playing::before { opacity:1; }

.station-logo-wrap {
  width:52px; height:52px; flex-shrink:0;
  background:#0a0f1a; border-radius:10px;
  display:flex; align-items:center; justify-content:center;
  overflow:hidden; position:relative;
}
.station-logo {
  width:100%; height:100%; object-fit:contain; padding:6px;
}
.station-playing-anim {
  display:none;
  position:absolute; inset:0;
  background:rgba(139,92,246,0.9);
  align-items:center; justify-content:center;
  gap:3px;
}
.station-card.playing .station-playing-anim { display:flex; }
.bar {
  width:3px; background:#fff; border-radius:2px;
  animation:barAnim 1s ease-in-out infinite;
}
.bar:nth-child(1) { height:10px; animation-delay:0s; }
.bar:nth-child(2) { height:16px; animation-delay:.2s; }
.bar:nth-child(3) { height:10px; animation-delay:.4s; }
.bar:nth-child(4) { height:14px; animation-delay:.1s; }
@keyframes barAnim {
  0%,100% { transform:scaleY(1); }
  50% { transform:scaleY(.4); }
}

.station-info { flex:1; min-width:0; }
.station-name {
  font-size:.9rem; font-weight:600;
  white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
  margin-bottom:3px;
}
.station-cat { font-size:.75rem; color:var(--text2); }

.station-play-icon {
  width:32px; height:32px; flex-shrink:0;
  background:var(--surface2); border-radius:50%;
  display:flex; align-items:center; justify-content:center;
  font-size:12px; color:var(--text2);
  transition:all var(--transition);
  padding-left:2px;
}
.station-card:hover .station-play-icon { background:#a78bfa; color:#fff; }
.station-card.playing .station-play-icon { background:#a78bfa; color:#fff; }

/* ===== ALTTA YAPIŞIK PLAYER ===== */
.music-player {
  display:none;
  position:fixed;
  bottom:0; left:0; right:0;
  background:rgba(10,12,22,0.97);
  backdrop-filter:blur(24px);
  border-top:1px solid rgba(139,92,246,0.25);
  z-index:2000;
  padding:0 2rem;
  height:80px;
  align-items:center;
  gap:1.5rem;
  box-shadow:0 -8px 32px rgba(0,0,0,0.6);
}
.music-player.visible { display:flex; }

/* Sol: logo + isim */
.mp-left { display:flex; align-items:center; gap:12px; min-width:0; flex:1; }
.mp-logo {
  width:48px; height:48px; flex-shrink:0;
  background:#0a0f1a; border-radius:10px;
  object-fit:contain; padding:5px;
  border:1px solid rgba(139,92,246,0.3);
}
.mp-info { min-width:0; }
.mp-name {
  font-size:.95rem; font-weight:700;
  white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
  max-width:200px;
}
.mp-cat { font-size:.78rem; color:#a78bfa; margin-top:1px; }
.mp-live {
  display:inline-flex; align-items:center; gap:5px;
  font-size:.7rem; font-weight:700; color:#a78bfa;
  background:rgba(139,92,246,0.12);
  padding:2px 8px; border-radius:99px; margin-top:3px;
}
.mp-live-dot { width:6px; height:6px; background:#a78bfa; border-radius:50%; animation:livePulse 2s ease-in-out infinite; }

/* Orta: kontroller */
.mp-controls { display:flex; align-items:center; gap:1rem; }
.mp-btn {
  width:44px; height:44px;
  background:var(--surface);
  border:1px solid var(--surface3);
  border-radius:50%;
  display:flex; align-items:center; justify-content:center;
  color:var(--text2); cursor:pointer;
  transition:all var(--transition);
}
.mp-btn:hover { background:var(--surface2); color:var(--text); }
.mp-play-btn {
  width:52px; height:52px;
  background:#a78bfa; border:none; color:#fff;
  font-size:18px;
}
.mp-play-btn:hover { background:#7c3aed; transform:scale(1.05); }

/* Ses */
.mp-volume { display:flex; align-items:center; gap:10px; }
.mp-vol-icon { color:var(--text2); cursor:pointer; }
.mp-vol-slider {
  -webkit-appearance:none;
  width:90px; height:4px;
  background:var(--surface3); border-radius:2px; cursor:pointer; outline:none;
}
.mp-vol-slider::-webkit-slider-thumb {
  -webkit-appearance:none;
  width:14px; height:14px;
  background:#a78bfa; border-radius:50%; cursor:pointer;
}

/* Dalga animasyonu (player'da) */
.mp-wave { display:flex; align-items:center; gap:2px; }
.mp-wave .bar { background:#a78bfa; }

/* Kapat */
.mp-close {
  width:36px; height:36px;
  background:transparent; border:none;
  color:var(--text3); cursor:pointer;
  display:flex; align-items:center; justify-content:center;
  border-radius:50%; transition:all var(--transition);
  margin-left:auto;
}
.mp-close:hover { background:var(--surface2); color:var(--text); }

/* Loading spinner player içinde */
.mp-loading { display:none; align-items:center; gap:8px; color:#a78bfa; font-size:.82rem; }
.mp-loading .spinner { width:18px; height:18px; border-width:2px; border-top-color:#a78bfa; }
.mp-loading.show { display:flex; }

/* Responsive */
@media (max-width:768px) {
  .music-hero { padding:calc(var(--nav-h) + 1.5rem) 1rem 1.5rem; }
  .music-content { padding:1.5rem 1rem; }
  .music-cat-nav-inner { padding:0 1rem; }
  .station-grid { grid-template-columns:repeat(auto-fill, minmax(160px, 1fr)); gap:10px; }
  .music-player { padding:0 1rem; gap:1rem; }
  .mp-vol-slider { width:70px; }
  .mp-name { max-width:120px; }
}
@media (max-width:480px) {
  .station-grid { grid-template-columns:1fr 1fr; gap:8px; }
  .mp-volume { display:none; }
  .mp-name { max-width:100px; }
}
</style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<!-- HERO -->
<div class="music-hero">
  <div class="music-hero-inner">
    <h1 class="music-hero-title">
      🎵 Müzik
      <span class="music-live-badge"><span class="music-pulse"></span> CANLI RADYO</span>
    </h1>
    <div class="music-hero-stats">
      <span class="music-stat"><strong><?= count($raw) ?></strong> istasyon</span>
      <span class="music-stat"><strong><?= count($sorted) ?></strong> kategori</span>
    </div>
  </div>
</div>

<!-- KATEGORİ NAVBAR -->
<nav class="music-cat-nav" id="musicCatNav">
  <div class="music-cat-nav-inner">
    <?php foreach ($sorted as $cat => $stations): ?>
    <?php $icon = $categoryIcons[$cat] ?? '🎵'; ?>
    <button class="music-cat-btn" onclick="scrollToMusicCat('mcat-<?= htmlspecialchars(preg_replace('/[^a-zA-Z0-9]/','', $cat)) ?>')">
      <?= $icon ?> <?= htmlspecialchars($cat) ?>
      <span style="color:var(--text3);font-weight:400">(<?= count($stations) ?>)</span>
    </button>
    <?php endforeach; ?>
  </div>
</nav>

<!-- İSTASYONLAR -->
<div class="music-content">
  <?php foreach ($sorted as $cat => $stations): ?>
  <?php $icon = $categoryIcons[$cat] ?? '🎵'; ?>
  <?php $secId = 'mcat-' . preg_replace('/[^a-zA-Z0-9]/','', $cat); ?>
  <section class="music-section" id="<?= $secId ?>">
    <div class="music-section-header">
      <h2 class="music-section-title"><?= $icon ?> <?= htmlspecialchars($cat) ?></h2>
      <span class="music-section-count"><?= count($stations) ?> istasyon</span>
    </div>
    <div class="station-grid">
      <?php foreach ($stations as $idx => $st): ?>
      <?php $stData = json_encode(['name'=>$st['name'],'logo'=>$st['logo'],'category'=>$st['category'],'url'=>$st['url']]); ?>
      <div class="station-card" id="sc-<?= md5($st['url']) ?>" onclick="playStation(<?= htmlspecialchars($stData) ?>)">
        <div class="station-logo-wrap">
          <img class="station-logo" src="<?= htmlspecialchars($st['logo']) ?>"
               alt="<?= htmlspecialchars($st['name']) ?>"
               onerror="this.src='assets/placeholder.svg'">
          <div class="station-playing-anim">
            <div class="bar"></div><div class="bar"></div>
            <div class="bar"></div><div class="bar"></div>
          </div>
        </div>
        <div class="station-info">
          <div class="station-name"><?= htmlspecialchars($st['name']) ?></div>
          <div class="station-cat"><?= htmlspecialchars($st['category']) ?></div>
        </div>
        <div class="station-play-icon">▶</div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endforeach; ?>
</div>

<?php include 'includes/footer.php'; ?>

<!-- ALTTA YAPIŞIK MİNİ PLAYER -->
<div class="music-player" id="musicPlayer">
  <!-- Sol -->
  <div class="mp-left">
    <img class="mp-logo" id="mpLogo" src="" alt="">
    <div class="mp-info">
      <div class="mp-name" id="mpName">—</div>
      <div class="mp-cat" id="mpCat"></div>
      <div class="mp-live"><span class="mp-live-dot"></span> CANLI</div>
    </div>
  </div>

  <!-- Yükleniyor -->
  <div class="mp-loading" id="mpLoading">
    <div class="spinner"></div>
    <span>Bağlanıyor...</span>
  </div>

  <!-- Kontroller -->
  <div class="mp-controls">
    <button class="mp-btn mp-play-btn" id="mpPlayBtn" onclick="togglePlay()">
      <svg id="mpPlayIcon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
    </button>
  </div>

  <!-- Ses -->
  <div class="mp-volume">
    <svg class="mp-vol-icon" onclick="toggleMute()" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" id="mpVolIcon">
      <path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02z"/>
    </svg>
    <input type="range" class="mp-vol-slider" id="mpVolume" min="0" max="100" value="80" oninput="setVolume(this.value)">
  </div>

  <!-- Kapat -->
  <button class="mp-close" onclick="stopPlayer()" title="Kapat">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
      <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
    </svg>
  </button>

  <!-- Gizli audio -->
  <audio id="audioEl" preload="none"></audio>
</div>

<div class="toast" id="toast"></div>
<script src="assets/app.js"></script>
<script>
// ===== KATEGORİ NAV AKTİF TAKİP =====
const musicCatBtns = document.querySelectorAll('.music-cat-btn');
const musicSections = document.querySelectorAll('.music-section');

const musicObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      const id = entry.target.id;
      musicCatBtns.forEach(btn => btn.classList.remove('active'));
      musicCatBtns.forEach(btn => {
        if (btn.getAttribute('onclick')?.includes(id)) {
          btn.classList.add('active');
          btn.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }
      });
    }
  });
}, { rootMargin: '-20% 0px -70% 0px' });

musicSections.forEach(s => musicObserver.observe(s));

function scrollToMusicCat(id) {
  const el = document.getElementById(id);
  if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ===== PLAYER =====
const audio     = document.getElementById('audioEl');
const player    = document.getElementById('musicPlayer');
const mpLogo    = document.getElementById('mpLogo');
const mpName    = document.getElementById('mpName');
const mpCat     = document.getElementById('mpCat');
const mpLoading = document.getElementById('mpLoading');
const mpPlayBtn = document.getElementById('mpPlayBtn');
const mpPlayIcon = document.getElementById('mpPlayIcon');

let currentStation = null;
let isPlaying = false;

const PLAY_ICON  = '<path d="M8 5v14l11-7z"/>';
const PAUSE_ICON = '<path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>';

function playStation(st) {
  // Aynı istasyon tekrar tıklandıysa toggle
  if (currentStation && currentStation.url === st.url) {
    togglePlay();
    return;
  }

  // Önceki kartı temizle
  if (currentStation) {
    const old = document.getElementById('sc-' + md5url(currentStation.url));
    if (old) old.classList.remove('playing');
  }

  currentStation = st;

  // Player UI aç
  player.classList.add('visible');
  mpLogo.src = st.logo || 'assets/placeholder.svg';
  mpLogo.onerror = () => mpLogo.src = 'assets/placeholder.svg';
  mpName.textContent = st.name;
  mpCat.textContent  = st.category;
  mpLoading.classList.add('show');
  mpPlayIcon.innerHTML = PAUSE_ICON;

  // Mevcut kartı işaretle
  const card = document.getElementById('sc-' + md5url(st.url));
  if (card) card.classList.add('playing');

  // Proxy üzerinden yükle (MP3 stream için)
  const proxyUrl = 'proxy.php?url=' + encodeURIComponent(st.url);
  audio.src = proxyUrl;
  audio.volume = document.getElementById('mpVolume').value / 100;

  audio.play()
    .then(() => {
      isPlaying = true;
      mpLoading.classList.remove('show');
    })
    .catch(() => {
      // Proxy başarısız → direkt dene
      audio.src = st.url;
      audio.play()
        .then(() => {
          isPlaying = true;
          mpLoading.classList.remove('show');
        })
        .catch(() => {
          mpLoading.classList.remove('show');
          showToast('Bu istasyon şu an açılamıyor 😔', 'error');
        });
    });
}

function togglePlay() {
  if (!currentStation) return;
  if (audio.paused) {
    audio.play().then(() => { isPlaying = true; mpPlayIcon.innerHTML = PAUSE_ICON; });
  } else {
    audio.pause();
    isPlaying = false;
    mpPlayIcon.innerHTML = PLAY_ICON;
  }
}

audio.addEventListener('playing', () => {
  isPlaying = true;
  mpPlayIcon.innerHTML = PAUSE_ICON;
  mpLoading.classList.remove('show');
});

audio.addEventListener('waiting', () => {
  mpLoading.classList.add('show');
});

audio.addEventListener('error', () => {
  mpLoading.classList.remove('show');
  showToast('Bağlantı hatası — başka istasyon dene 🎵', 'error');
});

function stopPlayer() {
  audio.pause();
  audio.src = '';
  isPlaying = false;
  player.classList.remove('visible');
  if (currentStation) {
    const card = document.getElementById('sc-' + md5url(currentStation.url));
    if (card) card.classList.remove('playing');
  }
  currentStation = null;
}

function setVolume(val) {
  audio.volume = val / 100;
  const icon = document.getElementById('mpVolIcon');
  if (val == 0) {
    icon.innerHTML = '<path d="M16.5 12c0-1.77-1.02-3.29-2.5-4.03v2.21l2.45 2.45c.03-.2.05-.41.05-.63zm2.5 0c0 .94-.2 1.82-.54 2.64l1.51 1.51C20.63 14.91 21 13.5 21 12c0-4.28-2.99-7.86-7-8.77v2.06c2.89.86 5 3.54 5 6.71zM4.27 3L3 4.27 7.73 9H3v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.42.93-2.25 1.18v2.06c1.38-.31 2.63-.95 3.69-1.81L19.73 21 21 19.73l-9-9L4.27 3zM12 4L9.91 6.09 12 8.18V4z"/>';
  } else if (val < 50) {
    icon.innerHTML = '<path d="M18.5 12c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM5 9v6h4l5 5V4L9 9H5z"/>';
  } else {
    icon.innerHTML = '<path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"/>';
  }
}

let muted = false;
let prevVol = 80;
function toggleMute() {
  const slider = document.getElementById('mpVolume');
  if (muted) {
    audio.volume = prevVol / 100;
    slider.value = prevVol;
    setVolume(prevVol);
    muted = false;
  } else {
    prevVol = slider.value;
    audio.volume = 0;
    slider.value = 0;
    setVolume(0);
    muted = true;
  }
}

// Basit md5 yerine url hash
function md5url(url) {
  // PHP'deki md5($st['url']) ile eşleşmesi için aynı mantık lazım
  // Ama JS'de md5 yoktur, PHP tarafında id oluştururken biz de aynı şeyi yapacağız
  // Şimdilik btoa kullanıyoruz - ama PHP tarafı md5 kullanıyor
  // Çözüm: data-id attribute'ü ekleyelim
  return url; // placeholder - aşağıda data-id kullanacağız
}

// Kartlara data-url eklendiği için querySelector ile buluyoruz
function playStation(st) {
  // Aynı istasyon toggle
  if (currentStation && currentStation.url === st.url) {
    togglePlay();
    return;
  }
  // Önceki kartı temizle
  document.querySelectorAll('.station-card.playing').forEach(c => c.classList.remove('playing'));

  currentStation = st;
  player.classList.add('visible');
  mpLogo.src = st.logo || 'assets/placeholder.svg';
  mpLogo.onerror = () => mpLogo.src = 'assets/placeholder.svg';
  mpName.textContent = st.name;
  mpCat.textContent  = st.category;
  mpLoading.classList.add('show');
  mpPlayIcon.innerHTML = PAUSE_ICON;

  // Tıklanan kartı bul ve işaretle (onclick içinde this yok, event'ten alalım)
  const allCards = document.querySelectorAll('.station-card');
  allCards.forEach(card => {
    const onclick = card.getAttribute('onclick');
    if (onclick && onclick.includes(JSON.stringify(st.url).slice(1,-1))) {
      card.classList.add('playing');
    }
  });

  const proxyUrl = 'proxy.php?url=' + encodeURIComponent(st.url);
  audio.src = proxyUrl;
  audio.volume = document.getElementById('mpVolume').value / 100;

  audio.play()
    .then(() => { isPlaying = true; mpLoading.classList.remove('show'); })
    .catch(() => {
      audio.src = st.url;
      audio.play()
        .then(() => { isPlaying = true; mpLoading.classList.remove('show'); })
        .catch(() => {
          mpLoading.classList.remove('show');
          showToast('Bu istasyon şu an açılamıyor 😔', 'error');
        });
    });
}

// Sayfa kapatılırken müziği durdur
window.addEventListener('beforeunload', () => { audio.pause(); });

function showToast(msg, type) {
  const t = document.getElementById('toast');
  if (!t) return;
  t.textContent = msg;
  t.className = 'toast show ' + (type||'');
  setTimeout(() => t.className = 'toast', 3000);
}
</script>
</body>
</html>
