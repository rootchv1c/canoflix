<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/data.php';
if (!isLoggedIn()) { header('Location: login.php'); exit; }

$tip = $_GET['tip'] ?? '';
$tur = $_GET['tur'] ?? '';

// Eğer direkt yönlendirme isteği varsa
if ($_GET['action'] ?? '' === 'go') {
    if ($tip === 'dizi') {
        $shows = loadShows();
        $keys = array_keys($shows);
        $random = $keys[array_rand($keys)];
        header('Location: izle.php?type=dizi&name=' . urlencode($random));
        exit;
    } else {
        $films = loadFilms();
        if ($tur) {
            $films = array_values(array_filter($films, fn($f) => in_array($tur, $f['genres'])));
        }
        if (empty($films)) { header('Location: sans.php'); exit; }
        $random = $films[array_rand($films)];
        header('Location: izle.php?type=film&name=' . urlencode($random['name']));
        exit;
    }
}

// Türleri hazırla
$allFilms = loadFilms();
$genres = [];
foreach ($allFilms as $f) {
    foreach ($f['genres'] as $g) $genres[$g] = ($genres[$g]??0)+1;
}
arsort($genres);
$topGenres = array_slice(array_keys($genres), 0, 12);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Şansına İzle — StreamFlix</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
<style>
.sans-page {
  min-height: calc(100vh - var(--nav-h));
  display: flex; align-items: center; justify-content: center;
  padding: 2rem;
}
.sans-box {
  background: var(--surface);
  border: 1px solid var(--surface3);
  border-radius: 24px;
  padding: 3rem 2.5rem;
  max-width: 560px;
  width: 100%;
  text-align: center;
}
.sans-dice {
  font-size: 5rem;
  display: block;
  margin-bottom: 1rem;
  animation: spin 3s ease infinite;
}
@keyframes spin {
  0%,85%,100% { transform: rotate(0deg) scale(1); }
  90% { transform: rotate(20deg) scale(1.1); }
  95% { transform: rotate(-10deg) scale(1.05); }
}
.sans-title { font-family: 'Bebas Neue', sans-serif; font-size: 2.5rem; margin-bottom: .5rem; }
.sans-desc { color: var(--text2); margin-bottom: 2rem; }
.sans-btn {
  display: inline-flex; align-items: center; gap: .75rem;
  padding: 1rem 2.5rem; border-radius: 12px; font-size: 1.1rem;
  font-weight: 700; cursor: pointer; border: none; font-family: inherit;
  transition: all .2s; text-decoration: none; margin: .4rem;
}
.sans-btn-film { background: var(--accent); color: #fff; }
.sans-btn-film:hover { background: var(--accent-hover); transform: scale(1.03); }
.sans-btn-dizi { background: var(--surface2); color: var(--text); border: 1px solid var(--surface3); }
.sans-btn-dizi:hover { border-color: var(--accent); transform: scale(1.03); }

.genre-filter { margin-top: 2rem; }
.genre-filter p { font-size: .85rem; color: var(--text2); margin-bottom: .75rem; }
.genre-chips { display: flex; flex-wrap: wrap; gap: .4rem; justify-content: center; }
.genre-chip {
  padding: .35rem .85rem; border-radius: 99px; font-size: .8rem; font-weight: 500;
  background: var(--surface2); border: 1px solid var(--surface3); color: var(--text2);
  cursor: pointer; transition: all .15s; text-decoration: none;
}
.genre-chip:hover, .genre-chip.active { background: var(--accent); border-color: var(--accent); color: #fff; }
</style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div style="padding-top: var(--nav-h)">
  <div class="sans-page">
    <div class="sans-box">
      <span class="sans-dice">🎲</span>
      <h1 class="sans-title">Şansına İzle!</h1>
      <p class="sans-desc">Ne izleyeceğine karar veremiyor musun?<br>Biz seçelim, sen izle!</p>

      <div>
        <a href="sans.php?action=go&tip=film<?= $tur ? '&tur='.urlencode($tur) : '' ?>" class="sans-btn sans-btn-film">
          🎬 Rastgele Film
        </a>
        <a href="sans.php?action=go&tip=dizi" class="sans-btn sans-btn-dizi">
          📺 Rastgele Dizi
        </a>
      </div>

      <div class="genre-filter">
        <p>veya türe göre rastgele film seç:</p>
        <div class="genre-chips">
          <a href="sans.php" class="genre-chip <?= !$tur ? 'active' : '' ?>">Hepsi</a>
          <?php foreach ($topGenres as $g): ?>
          <a href="sans.php?tur=<?= urlencode($g) ?>" class="genre-chip <?= $tur===$g ? 'active' : '' ?>">
            <?= htmlspecialchars($g) ?>
          </a>
          <?php endforeach; ?>
        </div>
      </div>

      <?php if ($tur): ?>
      <p style="margin-top:1.5rem;color:var(--text3);font-size:.85rem">
        🎭 <strong style="color:var(--accent)"><?= htmlspecialchars($tur) ?></strong> türünden rastgele film
      </p>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
<div class="toast" id="toast"></div>
<script src="assets/app.js"></script>
</body>
</html>
