<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/data.php';
if (!isLoggedIn()) { header('Location: login.php'); exit; }

$tip = $_GET['tip'] ?? 'film'; // film | dizi
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 48;

// "Yeni" = listenin sonu (en son eklenen)
if ($tip === 'dizi') {
    $allShows = loadShows();
    $list = [];
    foreach ($allShows as $name => $data) {
        $seasons = array_unique(array_column($data['episodes'] ?? [], 'season'));
        $list[] = ['name'=>$name,'logo'=>$data['logo'],'season_count'=>count($seasons),'episode_count'=>count($data['episodes']??[])];
    }
    $list = array_reverse($list); // son eklenen = en yeni
    $total = count($list);
    $totalPages = ceil($total / $perPage);
    $items = array_slice($list, ($page-1)*$perPage, $perPage);
} else {
    $allFilms = loadFilms();
    $allFilms = array_reverse($allFilms);
    $total = count($allFilms);
    $totalPages = ceil($total / $perPage);
    $items = array_slice($allFilms, ($page-1)*$perPage, $perPage);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Yeni Eklenenler — StreamFlix</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
<style>
.new-badge {
  position: absolute; top: 8px; right: 8px;
  background: #22c55e; color: #fff;
  padding: .2rem .55rem; border-radius: 6px;
  font-size: .7rem; font-weight: 700; z-index: 2;
  animation: pulse 2s infinite;
}
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.6} }
</style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div style="padding-top: calc(var(--nav-h) + 2rem)">
  <div class="page-header">
    <div>
      <h1 class="font-display" style="font-size:2.2rem">🆕 Yeni Eklenenler</h1>
      <p style="color:var(--text2);margin-top:.3rem">En son eklenen içerikler</p>
    </div>
    <div style="margin-left:auto;display:flex;gap:.5rem">
      <a href="?tip=film" class="filter-btn <?= $tip==='film'?'active':'' ?>">🎬 Filmler</a>
      <a href="?tip=dizi" class="filter-btn <?= $tip==='dizi'?'active':'' ?>">📺 Diziler</a>
    </div>
  </div>

  <div class="browse-grid-container">
    <div class="browse-grid">
      <?php foreach ($items as $i => $item): 
        $isNew = $i < 24; // ilk 24 = gerçekten yeni
      ?>
      <?php if ($tip === 'film'): ?>
      <div class="card" onclick="openFilm('<?= htmlspecialchars(addslashes($item['name'])) ?>')">
        <div class="card-img-wrap" style="position:relative">
          <img class="card-img" src="<?= htmlspecialchars($item['logo']) ?>" loading="lazy" onerror="this.src='assets/placeholder.svg';this.classList.add('img-loaded');this.closest('.card-img-wrap')?.classList.add('loaded')">
          <div class="card-overlay"><div class="card-play">▶</div></div>
          <?php if ($isNew): ?><div class="new-badge">YENİ</div><?php endif; ?>
          <div class="card-badges">
            <?php foreach(array_slice($item['genres'],0,2) as $g): ?>
            <span class="badge"><?= htmlspecialchars($g) ?></span>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="card-info">
          <p class="card-title"><?= htmlspecialchars($item['name']) ?></p>
          <p class="card-meta"><?= implode(' · ', array_unique(array_column($item['versions'],'audio'))) ?></p>
        </div>
      </div>
      <?php else: ?>
      <div class="card" onclick="location.href='izle.php?type=dizi&name=<?= urlencode($item['name']) ?>'">
        <div class="card-img-wrap" style="position:relative">
          <img class="card-img" src="<?= htmlspecialchars($item['logo']) ?>" loading="lazy" onerror="this.src='assets/placeholder.svg';this.classList.add('img-loaded');this.closest('.card-img-wrap')?.classList.add('loaded')">
          <div class="card-overlay"><div class="card-play">▶</div></div>
          <?php if ($isNew): ?><div class="new-badge">YENİ</div><?php endif; ?>
        </div>
        <div class="card-info">
          <p class="card-title"><?= htmlspecialchars($item['name']) ?></p>
          <p class="card-meta"><?= $item['season_count'] ?> Sezon · <?= $item['episode_count'] ?> Bölüm</p>
        </div>
      </div>
      <?php endif; ?>
      <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <?php if ($page > 1): ?><a href="?tip=<?=$tip?>&page=<?=$page-1?>" class="page-btn">← Önceki</a><?php endif; ?>
      <span class="page-info"><?= $page ?> / <?= $totalPages ?></span>
      <?php if ($page < $totalPages): ?><a href="?tip=<?=$tip?>&page=<?=$page+1?>" class="page-btn">Sonraki →</a><?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
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
