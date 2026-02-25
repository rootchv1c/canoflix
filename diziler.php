<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/data.php';

if (!isLoggedIn()) { header('Location: login.php'); exit; }

$page = max(1, intval($_GET['page'] ?? 1));
$search = trim($_GET['q'] ?? '');

if ($search) {
    $results = searchContent($search, 200);
    $shows_only = array_filter($results, fn($r) => $r['type'] === 'dizi');
    $data = ['items' => array_values($shows_only), 'total' => count($shows_only), 'pages' => 1, 'current_page' => 1];
} else {
    $data = getAllShowsPaginated($page, 48);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Diziler — StreamFlix</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="page-header">
  <div class="page-header-inner">
    <h1 class="page-title">📺 Diziler</h1>
    <p class="page-count"><?= number_format($data['total']) ?> dizi</p>
  </div>
</div>

<div class="filter-bar">
  <div class="filter-bar-inner">
    <form method="GET" class="filter-search">
      <input type="text" name="q" placeholder="Dizi ara..." value="<?= htmlspecialchars($search) ?>">
    </form>
  </div>
</div>

<main class="browse-grid-container">
  <?php if (empty($data['items'])): ?>
  <div class="empty-state">
    <div class="empty-icon">📺</div>
    <h3>Dizi bulunamadı</h3>
    <a href="diziler.php" class="btn-primary">Tümünü Gör</a>
  </div>
  <?php else: ?>
  <div class="browse-grid">
    <?php foreach ($data['items'] as $show): ?>
    <div class="card"
         onclick="openShow('<?= htmlspecialchars(addslashes($show['name'])) ?>')"
         data-title="<?= htmlspecialchars($show['name']) ?>">
      <div class="card-img-wrap">
        <img class="card-img" src="<?= htmlspecialchars($show['logo'] ?? '') ?>"
             alt="<?= htmlspecialchars($show['name']) ?>"
             loading="lazy"
             onerror="this.src='assets/placeholder.svg';this.classList.add('img-loaded');this.closest('.card-img-wrap')?.classList.add('loaded')">
        <div class="card-overlay">
          <div class="card-play">▶</div>
          <div class="card-badges">
            <span class="badge badge-dizi">Dizi</span>
            <?php if (!empty($show['season_count'])): ?>
            <span class="badge badge-season"><?= $show['season_count'] ?> Sezon</span>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="card-info">
        <p class="card-title"><?= htmlspecialchars($show['name']) ?></p>
        <p class="card-genre">
          <?php if (!empty($show['season_count'])): ?><?= $show['season_count'] ?> Sezon · <?php endif; ?>
          <?= !empty($show['episode_count']) ? $show['episode_count'] . ' Bölüm' : '' ?>
        </p>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  
  <?php if ($data['pages'] > 1): ?>
  <div class="pagination">
    <?php if ($data['current_page'] > 1): ?>
    <a href="?page=<?= $data['current_page']-1 ?><?= $search ? '&q='.urlencode($search) : '' ?>" class="page-btn">← Önceki</a>
    <?php endif; ?>
    <?php
    $start = max(1, $data['current_page'] - 3);
    $end = min($data['pages'], $data['current_page'] + 3);
    for ($p = $start; $p <= $end; $p++):
    ?>
    <a href="?page=<?= $p ?><?= $search ? '&q='.urlencode($search) : '' ?>"
       class="page-btn <?= $p === $data['current_page'] ? 'active' : '' ?>"><?= $p ?></a>
    <?php endfor; ?>
    <?php if ($data['current_page'] < $data['pages']): ?>
    <a href="?page=<?= $data['current_page']+1 ?><?= $search ? '&q='.urlencode($search) : '' ?>" class="page-btn">Sonraki →</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
  <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>

<!-- Show Modal -->
<div class="modal-overlay" id="filmModal" onclick="closeModal(event)">
  <div class="modal-box modal-box-show" id="filmModalBox">
    <button class="modal-close" onclick="closeFilmModal()">×</button>
    <div id="filmModalContent"><div class="modal-loading"><div class="spinner"></div></div></div>
  </div>
</div>

<div class="toast" id="toast"></div>
<script src="assets/app.js"></script>
</body>
</html>
