<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/data.php';

// Redirect to login if not authenticated
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();
$recentFilms = getRecentFilms(20);
$recentShows = getRecentShows(20);
$featuredFilms = getFeaturedFilms(8);
$allGenres = getAllGenres();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StreamFlix — Film & Dizi</title>
<link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<!-- HERO SECTION -->
<section class="hero" id="hero">
  <div class="hero-bg" id="heroBg"></div>
  <div class="hero-overlay"></div>
  <div class="hero-content" id="heroContent">
    <div class="hero-badge">🔥 Öne Çıkan</div>
    <h1 class="hero-title" id="heroTitle">StreamFlix'e Hoş Geldin</h1>
    <p class="hero-desc" id="heroDesc">Binlerce film ve dizi seni bekliyor. Türkçe dublaj ve altyazı seçenekleriyle keyfini çıkar.</p>
    <div class="hero-actions">
      <button class="btn-primary" id="heroPlayBtn" onclick="handleHeroPlay()">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
        İzle
      </button>
      <button class="btn-secondary" id="heroInfoBtn" onclick="handleHeroInfo()">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        Daha Fazla
      </button>
    </div>
  </div>
  <div class="hero-pagination" id="heroPagination"></div>
</section>

<!-- MAIN CONTENT -->
<main class="main-content">

  <!-- SEARCH BAR -->
  <div class="search-container">
    <div class="search-box">
      <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      <input type="text" id="searchInput" placeholder="Film veya dizi ara..." autocomplete="off">
      <button class="search-clear" id="searchClear" onclick="clearSearch()">×</button>
    </div>
    <div class="search-results" id="searchResults"></div>
  </div>

  <!-- FILTER TABS -->
  <div class="filter-section">
    <div class="filter-tabs" id="filterTabs">
      <button class="filter-tab active" onclick="setFilter('all')">Tümü</button>
      <button class="filter-tab" onclick="setFilter('film')">🎬 Filmler</button>
      <button class="filter-tab" onclick="setFilter('dizi')">📺 Diziler</button>
      <button class="filter-tab" onclick="setFilter('dublaj')">🇹🇷 Dublaj</button>
    </div>
  </div>

  <!-- YENI EKLENENLER -->
  <section class="content-section">
    <div class="section-header">
      <h2 class="section-title">🆕 Yeni Eklenenler</h2>
      <a href="filmler.php" class="see-all">Tümünü Gör →</a>
    </div>
    <div class="cards-row" id="yeniEklenenler">
      <?php foreach ($recentFilms as $film): ?>
      <div class="card" 
           onclick="openFilm('<?= htmlspecialchars(addslashes($film['name'])) ?>')"
           data-title="<?= htmlspecialchars($film['name']) ?>"
           data-type="film">
        <div class="card-img-wrap">
          <img class="card-img" src="<?= htmlspecialchars($film['logo']) ?>" 
               alt="<?= htmlspecialchars($film['name']) ?>"
               loading="eager"
               onerror="this.src='assets/placeholder.svg';this.classList.add('img-loaded');this.closest('.card-img-wrap')?.classList.add('loaded')">
          <div class="card-overlay">
            <div class="card-play">▶</div>
            <?php if (!empty($film['versions'])): ?>
            <div class="card-badges">
              <?php foreach (array_unique(array_column($film['versions'], 'audio')) as $audio): ?>
              <span class="badge badge-<?= strtolower($audio) ?>"><?= htmlspecialchars($audio) ?></span>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
          </div>
        </div>
        <div class="card-info">
          <p class="card-title"><?= htmlspecialchars($film['name']) ?></p>
          <?php if (!empty($film['genres'])): ?>
          <p class="card-genre"><?= htmlspecialchars(implode(', ', array_slice($film['genres'], 0, 2))) ?></p>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- POPULER DIZILER -->
  <section class="content-section">
    <div class="section-header">
      <h2 class="section-title">📺 Popüler Diziler</h2>
      <a href="diziler.php" class="see-all">Tümünü Gör →</a>
    </div>
    <div class="cards-row" id="populerDiziler">
      <?php foreach ($recentShows as $show): ?>
      <div class="card"
           onclick="openShow('<?= htmlspecialchars(addslashes($show['name'])) ?>')"
           data-title="<?= htmlspecialchars($show['name']) ?>"
           data-type="dizi">
        <div class="card-img-wrap">
          <img class="card-img" src="<?= htmlspecialchars($show['logo']) ?>"
               alt="<?= htmlspecialchars($show['name']) ?>"
               loading="eager"
               onerror="this.src='assets/placeholder.svg';this.classList.add('img-loaded');this.closest('.card-img-wrap')?.classList.add('loaded')">
          <div class="card-overlay">
            <div class="card-play">▶</div>
            <div class="card-badges">
              <span class="badge badge-dizi">Dizi</span>
              <?php if ($show['season_count'] > 0): ?>
              <span class="badge badge-season"><?= $show['season_count'] ?> Sezon</span>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="card-info">
          <p class="card-title"><?= htmlspecialchars($show['name']) ?></p>
          <p class="card-genre"><?= $show['episode_count'] ?> Bölüm</p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- GENRE SECTIONS -->
  <?php
  $genreGroups = [
    'Aksiyon' => 'Aksiyon Filmleri 💥',
    'Korku' => 'Korku & Gerilim 👻',
    'Komedi' => 'Komedi 😂',
    'Romantik' => 'Romantik 💕',
    'Animasyon' => 'Animasyon 🎨',
    'Bilim-Kurgu' => 'Bilim Kurgu 🚀',
  ];
  foreach ($genreGroups as $genre => $title):
    $genreFilms = getFilmsByGenre($genre, 15);
    if (empty($genreFilms)) continue;
  ?>
  <section class="content-section">
    <div class="section-header">
      <h2 class="section-title"><?= $title ?></h2>
      <a href="filmler.php?genre=<?= urlencode($genre) ?>" class="see-all">Tümünü Gör →</a>
    </div>
    <div class="cards-row">
      <?php foreach ($genreFilms as $film): ?>
      <div class="card"
           onclick="openFilm('<?= htmlspecialchars(addslashes($film['name'])) ?>')"
           data-title="<?= htmlspecialchars($film['name']) ?>"
           data-type="film">
        <div class="card-img-wrap">
          <img class="card-img" src="<?= htmlspecialchars($film['logo']) ?>"
               alt="<?= htmlspecialchars($film['name']) ?>"
               loading="eager"
               onerror="this.src='assets/placeholder.svg';this.classList.add('img-loaded');this.closest('.card-img-wrap')?.classList.add('loaded')">
          <div class="card-overlay">
            <div class="card-play">▶</div>
            <?php if (!empty($film['versions'])): ?>
            <div class="card-badges">
              <?php foreach (array_unique(array_column($film['versions'], 'audio')) as $audio): ?>
              <span class="badge badge-<?= strtolower($audio) ?>"><?= htmlspecialchars($audio) ?></span>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
          </div>
        </div>
        <div class="card-info">
          <p class="card-title"><?= htmlspecialchars($film['name']) ?></p>
          <?php if (!empty($film['genres'])): ?>
          <p class="card-genre"><?= htmlspecialchars(implode(', ', array_slice($film['genres'], 0, 2))) ?></p>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endforeach; ?>

  <!-- TURKISH CONTENT -->
  <?php $turkishFilms = getFilmsByGenre('Yerli', 15); ?>
  <?php if (!empty($turkishFilms)): ?>
  <section class="content-section">
    <div class="section-header">
      <h2 class="section-title">🇹🇷 Yerli Yapımlar</h2>
    </div>
    <div class="cards-row">
      <?php foreach ($turkishFilms as $film): ?>
      <div class="card" onclick="openFilm('<?= htmlspecialchars(addslashes($film['name'])) ?>')">
        <div class="card-img-wrap">
          <img class="card-img" src="<?= htmlspecialchars($film['logo']) ?>" loading="eager" onerror="this.src='assets/placeholder.svg';this.classList.add('img-loaded');this.closest('.card-img-wrap')?.classList.add('loaded')">
          <div class="card-overlay"><div class="card-play">▶</div></div>
        </div>
        <div class="card-info">
          <p class="card-title"><?= htmlspecialchars($film['name']) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

</main>

<?php include 'includes/footer.php'; ?>

<!-- FILM MODAL -->
<div class="modal-overlay" id="filmModal" onclick="closeModal(event)">
  <div class="modal-box" id="filmModalBox">
    <button class="modal-close" onclick="closeFilmModal()">×</button>
    <div id="filmModalContent">
      <div class="modal-loading"><div class="spinner"></div></div>
    </div>
  </div>
</div>

<!-- TOAST -->
<div class="toast" id="toast"></div>

<script src="assets/app.js"></script>
<script>
// Hero data
const heroFilms = <?= json_encode(array_values(array_slice($featuredFilms, 0, 6))) ?>;
initHero(heroFilms);

// Search
const searchInput = document.getElementById('searchInput');
searchInput.addEventListener('input', debounce(handleSearch, 300));
</script>
</body>
</html>
