<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/data.php';

if (!isLoggedIn()) { header('Location: login.php'); exit; }

$page = max(1, intval($_GET['page'] ?? 1));
$genre = trim($_GET['genre'] ?? '');
$audio = trim($_GET['audio'] ?? '');
$sort = trim($_GET['sort'] ?? 'default');
$search = trim($_GET['q'] ?? '');

if ($search) {
    $results = searchContent($search, 200);
    $films_only = array_filter($results, fn($r) => $r['type'] === 'film');
    $data = ['items' => array_values($films_only), 'total' => count($films_only), 'pages' => 1, 'current_page' => 1];
} else {
    $data = getAllFilmsPaginated($page, 48, $genre, $audio, $sort);
}

$genres = getAllGenres();
$title = $genre ? $genre . ' Filmleri' : ($audio ? $audio . ' Filmler' : 'Tüm Filmler');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($title) ?> — StreamFlix</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="page-header">
  <div class="page-header-inner">
    <h1 class="page-title">🎬 <?= htmlspecialchars($title) ?></h1>
    <p class="page-count"><?= number_format($data['total']) ?> film</p>
  </div>
</div>

<div class="filter-bar">
  <div class="filter-bar-inner">
    <!-- Search -->
    <form method="GET" class="filter-search">
      <input type="text" name="q" placeholder="Film ara..." value="<?= htmlspecialchars($search) ?>">
      <?php if ($genre): ?><input type="hidden" name="genre" value="<?= htmlspecialchars($genre) ?>"><?php endif; ?>
    </form>
    
    <!-- Audio Filter -->
    <div class="filter-group">
      <label>Ses</label>
      <div class="filter-pills">
        <a href="?<?= http_build_query(['genre'=>$genre,'sort'=>$sort]) ?>" class="pill <?= !$audio ? 'active' : '' ?>">Tümü</a>
        <a href="?<?= http_build_query(['genre'=>$genre,'audio'=>'Dublaj','sort'=>$sort]) ?>" class="pill <?= $audio==='Dublaj' ? 'active' : '' ?>">Dublaj</a>
        <a href="?<?= http_build_query(['genre'=>$genre,'audio'=>'Altyazı','sort'=>$sort]) ?>" class="pill <?= $audio==='Altyazı' ? 'active' : '' ?>">Altyazı</a>
      </div>
    </div>
    
    <!-- Sort -->
    <div class="filter-group">
      <label>Sırala</label>
      <select onchange="window.location='?<?= http_build_query(['genre'=>$genre,'audio'=>$audio]) ?>&sort='+this.value">
        <option value="default" <?= $sort==='default'?'selected':'' ?>>Varsayılan</option>
        <option value="az" <?= $sort==='az'?'selected':'' ?>>A-Z</option>
        <option value="za" <?= $sort==='za'?'selected':'' ?>>Z-A</option>
      </select>
    </div>
  </div>
</div>

<!-- Genre Tags -->
<div class="genre-tags-bar">
  <a href="filmler.php" class="genre-tag <?= !$genre ? 'active' : '' ?>">Tümü</a>
  <?php foreach (array_slice($genres, 0, 20) as $g): ?>
  <a href="?genre=<?= urlencode($g) ?>" class="genre-tag <?= $genre===$g ? 'active' : '' ?>"><?= htmlspecialchars($g) ?></a>
  <?php endforeach; ?>
</div>

<main class="browse-grid-container">
  <?php if (empty($data['items'])): ?>
  <div class="empty-state">
    <div class="empty-icon">🎬</div>
    <h3>Film bulunamadı</h3>
    <p>Farklı bir arama veya filtre deneyin.</p>
    <a href="filmler.php" class="btn-primary">Tümünü Gör</a>
  </div>
  <?php else: ?>
  <div class="browse-grid" id="browseGrid">
    <?php foreach ($data['items'] as $film): ?>
    <div class="card"
         onclick="openFilm('<?= htmlspecialchars(addslashes($film['name'])) ?>')"
         data-title="<?= htmlspecialchars($film['name']) ?>">
      <div class="card-img-wrap">
        <img class="card-img" src="<?= htmlspecialchars($film['logo']) ?>"
             alt="<?= htmlspecialchars($film['name']) ?>"
             loading="lazy"
             onerror="this.src='assets/placeholder.svg';this.classList.add('img-loaded');this.closest('.card-img-wrap')?.classList.add('loaded')">
        <div class="card-overlay">
          <div class="card-play">▶</div>
          <?php if (!empty($film['versions'])): ?>
          <div class="card-badges">
            <?php foreach (array_unique(array_column($film['versions'], 'audio')) as $a): ?>
            <span class="badge badge-<?= strtolower($a) ?>"><?= htmlspecialchars($a) ?></span>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="card-info">
        <p class="card-title"><?= htmlspecialchars($film['name']) ?></p>
        <?php if (!empty($film['genres'])): ?>
        <p class="card-genre"><?= htmlspecialchars(implode(' · ', array_slice($film['genres'], 0, 2))) ?></p>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  
  <!-- PAGINATION -->
  <?php if ($data['pages'] > 1): ?>
  <div class="pagination">
    <?php if ($data['current_page'] > 1): ?>
    <a href="?<?= http_build_query(['page'=>$data['current_page']-1,'genre'=>$genre,'audio'=>$audio,'sort'=>$sort]) ?>" class="page-btn">← Önceki</a>
    <?php endif; ?>
    
    <?php
    $start = max(1, $data['current_page'] - 3);
    $end = min($data['pages'], $data['current_page'] + 3);
    for ($p = $start; $p <= $end; $p++):
    ?>
    <a href="?<?= http_build_query(['page'=>$p,'genre'=>$genre,'audio'=>$audio,'sort'=>$sort]) ?>"
       class="page-btn <?= $p === $data['current_page'] ? 'active' : '' ?>"><?= $p ?></a>
    <?php endfor; ?>
    
    <?php if ($data['current_page'] < $data['pages']): ?>
    <a href="?<?= http_build_query(['page'=>$data['current_page']+1,'genre'=>$genre,'audio'=>$audio,'sort'=>$sort]) ?>" class="page-btn">Sonraki →</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
  
  <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>

<!-- Film Modal -->
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
