<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/data.php';

if (!isLoggedIn()) { header('Location: login.php'); exit; }

$user = getCurrentUser();
$favorites = array_values($user['favorites'] ?? []);
$history = array_slice($user['watch_history'] ?? [], 0, 20);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Favorilerim — StreamFlix</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="page-header">
  <div class="page-header-inner">
    <h1 class="page-title">❤️ Favorilerim</h1>
    <p class="page-count"><?= count($favorites) ?> içerik</p>
  </div>
</div>

<main class="browse-grid-container">
  <?php if (empty($favorites)): ?>
  <div class="empty-state">
    <div class="empty-icon">💔</div>
    <h3>Henüz favori eklemediniz</h3>
    <p>Film veya dizi sayfasında ♥ butonuna basarak favorilere ekleyebilirsiniz.</p>
    <a href="index.php" class="btn-primary">Keşfet</a>
  </div>
  <?php else: ?>
  <div class="browse-grid">
    <?php foreach ($favorites as $fav): ?>
    <div class="card"
         onclick="<?= $fav['type']==='film' ? "openFilm('" . htmlspecialchars(addslashes($fav['name'])) . "')" : "openShow('" . htmlspecialchars(addslashes($fav['name'])) . "')" ?>">
      <div class="card-img-wrap">
        <img class="card-img" src="<?= htmlspecialchars($fav['logo'] ?? '') ?>"
             alt="<?= htmlspecialchars($fav['name']) ?>"
             loading="lazy" onerror="this.src='assets/placeholder.svg';this.classList.add('img-loaded');this.closest('.card-img-wrap')?.classList.add('loaded')">
        <div class="card-overlay">
          <div class="card-play">▶</div>
          <div class="card-badges">
            <span class="badge badge-<?= $fav['type'] ?>"><?= $fav['type'] === 'film' ? 'Film' : 'Dizi' ?></span>
          </div>
        </div>
      </div>
      <div class="card-info">
        <p class="card-title"><?= htmlspecialchars($fav['name']) ?></p>
        <p class="card-genre"><?= date('d.m.Y', strtotime($fav['added_at'])) ?></p>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if (!empty($history)): ?>
  <section class="content-section" style="margin-top:3rem">
    <div class="section-header">
      <h2 class="section-title">🕐 İzleme Geçmişi</h2>
    </div>
    <div class="cards-row">
      <?php foreach ($history as $h): ?>
      <div class="card"
           onclick="<?= $h['type']==='film' ? "window.location='izle.php?type=film&name=".urlencode($h['name'])."'" : "window.location='izle.php?type=dizi&name=".urlencode($h['name'])."&season=".($h['season']??1)."&episode=".($h['episode']??1)."'" ?>">
        <div class="card-img-wrap">
          <img class="card-img" src="<?= htmlspecialchars($h['logo'] ?? '') ?>" loading="lazy" onerror="this.src='assets/placeholder.svg';this.classList.add('img-loaded');this.closest('.card-img-wrap')?.classList.add('loaded')">
          <div class="card-overlay"><div class="card-play">▶</div></div>
        </div>
        <div class="card-info">
          <p class="card-title"><?= htmlspecialchars($h['name']) ?></p>
          <?php if ($h['type'] === 'dizi' && !empty($h['season'])): ?>
          <p class="card-genre">S<?= $h['season'] ?> B<?= $h['episode'] ?></p>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>
</main>

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
