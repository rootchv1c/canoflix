<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/data.php';
if (!isLoggedIn()) { header('Location: login.php'); exit; }

$tur = $_GET['tur'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 48;

// Tüm türler
$allFilms = loadFilms();
$genreCounts = [];
foreach ($allFilms as $f) {
    foreach ($f['genres'] as $g) {
        $genreCounts[$g] = ($genreCounts[$g] ?? 0) + 1;
    }
}
arsort($genreCounts);
$allGenres = array_keys($genreCounts);

// Seçili türe göre filtrele
$filtered = [];
if ($tur) {
    foreach ($allFilms as $f) {
        foreach ($f['genres'] as $g) {
            if ($g === $tur) { $filtered[] = $f; break; }
        }
    }
} 

$total = count($filtered);
$totalPages = $total ? ceil($total / $perPage) : 1;
$items = array_slice($filtered, ($page-1)*$perPage, $perPage);

$genreEmojis = [
    'Aksiyon'=>'💥','Korku'=>'👻','Komedi'=>'😂','Dram'=>'🎭','Gerilim'=>'😰',
    'Romantik'=>'❤️','Bilim-Kurgu'=>'🚀','Fantastik'=>'🧙','Animasyon'=>'🎨',
    'Suç'=>'🔫','Macera'=>'🗺️','Aile'=>'👨‍👩‍👧','Gizem'=>'🔍','Tarih'=>'📜',
    'Belgesel'=>'🎥','Savaş'=>'⚔️','Müzik'=>'🎵','Vahşi Batı'=>'🤠'
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $tur ? htmlspecialchars($tur).' Filmleri' : 'Türe Göre' ?> — StreamFlix</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
<style>
.genre-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 1rem;
  padding: 0 2rem 3rem;
  max-width: 1400px;
  margin: 0 auto;
}
.genre-card {
  background: var(--surface);
  border: 1px solid var(--surface3);
  border-radius: 14px;
  padding: 1.5rem 1rem;
  text-align: center;
  cursor: pointer;
  transition: all .2s;
  text-decoration: none;
  color: var(--text);
  display: block;
}
.genre-card:hover, .genre-card.active {
  background: var(--accent);
  border-color: var(--accent);
  transform: translateY(-3px);
  box-shadow: 0 8px 24px rgba(229,9,20,.3);
}
.genre-emoji { font-size: 2.5rem; display: block; margin-bottom: .5rem; }
.genre-name { font-weight: 600; font-size: .9rem; }
.genre-count { font-size: .75rem; color: var(--text3); margin-top: .2rem; }
.genre-card:hover .genre-count, .genre-card.active .genre-count { color: rgba(255,255,255,.7); }
</style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div style="padding-top: calc(var(--nav-h) + 2rem)">
  
  <?php if (!$tur): ?>
  <!-- TÜR SEÇİM EKRANI -->
  <div style="max-width:1400px;margin:0 auto;padding:0 2rem 2rem">
    <h1 class="font-display" style="font-size:2.2rem;margin-bottom:.5rem">🎭 Türe Göre</h1>
    <p style="color:var(--text2);margin-bottom:2rem">Bir tür seç, o türün tüm filmlerini gör</p>
  </div>
  <div class="genre-grid">
    <?php foreach ($allGenres as $g): 
      if ($genreCounts[$g] < 5) continue;
      $emoji = $genreEmojis[$g] ?? '🎬';
    ?>
    <a href="tur.php?tur=<?= urlencode($g) ?>" class="genre-card">
      <span class="genre-emoji"><?= $emoji ?></span>
      <div class="genre-name"><?= htmlspecialchars($g) ?></div>
      <div class="genre-count"><?= $genreCounts[$g] ?> film</div>
    </a>
    <?php endforeach; ?>
  </div>

  <?php else: ?>
  <!-- FİLTRELENMİŞ FİLMLER -->
  <div style="max-width:1400px;margin:0 auto;padding:0 2rem 1rem">
    <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap">
      <a href="tur.php" style="color:var(--text3);font-size:.9rem">← Tüm Türler</a>
      <h1 class="font-display" style="font-size:2rem"><?= $genreEmojis[$tur] ?? '🎬' ?> <?= htmlspecialchars($tur) ?></h1>
      <span style="color:var(--text3);font-size:.9rem"><?= $total ?> film</span>
    </div>
  </div>

  <div class="browse-grid-container">
    <div class="browse-grid">
      <?php foreach ($items as $film): ?>
      <div class="card" onclick="openFilm('<?= htmlspecialchars(addslashes($film['name'])) ?>')">
        <div class="card-img-wrap">
          <img class="card-img" src="<?= htmlspecialchars($film['logo']) ?>" loading="lazy" onerror="this.src='assets/placeholder.svg';this.classList.add('img-loaded');this.closest('.card-img-wrap')?.classList.add('loaded')">
          <div class="card-overlay"><div class="card-play">▶</div></div>
          <div class="card-badges">
            <?php foreach(array_slice($film['genres'],0,2) as $g): ?>
            <span class="badge"><?= htmlspecialchars($g) ?></span>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="card-info">
          <p class="card-title"><?= htmlspecialchars($film['name']) ?></p>
          <p class="card-meta"><?= implode(' · ', array_unique(array_column($film['versions'],'audio'))) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <?php if ($page > 1): ?>
        <a href="?tur=<?=urlencode($tur)?>&page=<?=$page-1?>" class="page-btn">← Önceki</a>
      <?php endif; ?>
      <span class="page-info"><?= $page ?> / <?= $totalPages ?></span>
      <?php if ($page < $totalPages): ?>
        <a href="?tur=<?=urlencode($tur)?>&page=<?=$page+1?>" class="page-btn">Sonraki →</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
<div class="modal-overlay" id="filmModal" onclick="closeModal(event)">
  <div class="modal-box" id="filmModalBox">
    <button class="modal-close" onclick="closeFilmModal()">×</button>
    <div id="filmModalContent"><div class="modal-loading"><div class="spinner"></div></div></div>
  </div>
</div>
<div class="toast" id="toast"></div>
<script src="assets/app.js"></script>
</body>
</html>
