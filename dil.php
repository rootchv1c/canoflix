<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/data.php';
if (!isLoggedIn()) { header('Location: login.php'); exit; }

$dil = $_GET['dil'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 48;

$allFilms = loadFilms();

// Dilleri bul
$dilCounts = [];
foreach ($allFilms as $f) {
    foreach ($f['versions'] as $v) {
        $a = $v['audio'] ?? '';
        if ($a) $dilCounts[$a] = ($dilCounts[$a] ?? 0) + 1;
    }
}
arsort($dilCounts);

// Filtrele
$filtered = [];
if ($dil) {
    $seen = [];
    foreach ($allFilms as $f) {
        foreach ($f['versions'] as $v) {
            if (stripos($v['audio'] ?? '', $dil) !== false && !isset($seen[$f['name']])) {
                $filtered[] = $f;
                $seen[$f['name']] = true;
                break;
            }
        }
    }
}

$total = count($filtered);
$totalPages = $total ? ceil($total / $perPage) : 1;
$items = array_slice($filtered, ($page-1)*$perPage, $perPage);

$dilEmojis = ['Dublaj'=>'🎙️','Altyazı'=>'📝','Orijinal'=>'🌍','Türkçe'=>'🇹🇷','İngilizce'=>'🇺🇸'];
$dilAciklamalari = [
    'Dublaj' => 'Türkçe seslendirmeli tüm filmler',
    'Altyazı' => 'Türkçe altyazılı tüm filmler',
    'Orijinal' => 'Orijinal dilde filmler',
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $dil ? htmlspecialchars($dil) : 'Dile Göre' ?> — StreamFlix</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
<style>
.dil-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1.25rem;
  padding: 0 2rem 3rem;
  max-width: 1000px;
  margin: 0 auto;
}
.dil-card {
  background: var(--surface);
  border: 1px solid var(--surface3);
  border-radius: 16px;
  padding: 2rem 1.5rem;
  text-align: center;
  cursor: pointer;
  transition: all .2s;
  text-decoration: none;
  color: var(--text);
  display: block;
}
.dil-card:hover {
  background: var(--surface2);
  border-color: var(--accent);
  transform: translateY(-4px);
  box-shadow: 0 12px 32px rgba(229,9,20,.2);
}
.dil-emoji { font-size: 3rem; display: block; margin-bottom: .75rem; }
.dil-name { font-family: 'Bebas Neue', sans-serif; font-size: 1.6rem; letter-spacing: .05em; }
.dil-desc { font-size: .85rem; color: var(--text2); margin-top: .3rem; }
.dil-count { display: inline-block; margin-top: .75rem; background: var(--accent); color: #fff; padding: .25rem .75rem; border-radius: 99px; font-size: .8rem; font-weight: 600; }
</style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div style="padding-top: calc(var(--nav-h) + 2rem)">

  <?php if (!$dil): ?>
  <div style="max-width:1000px;margin:0 auto;padding:0 2rem 2rem">
    <h1 class="font-display" style="font-size:2.2rem;margin-bottom:.5rem">🌍 Dile Göre</h1>
    <p style="color:var(--text2);margin-bottom:2rem">Dublaj mı, altyazı mı? Seçim senin!</p>
  </div>
  <div class="dil-grid">
    <?php foreach ($dilCounts as $dil_ => $count): ?>
    <a href="dil.php?dil=<?= urlencode($dil_) ?>" class="dil-card">
      <span class="dil-emoji"><?= $dilEmojis[$dil_] ?? '🎬' ?></span>
      <div class="dil-name"><?= htmlspecialchars($dil_) ?></div>
      <div class="dil-desc"><?= $dilAciklamalari[$dil_] ?? '' ?></div>
      <span class="dil-count"><?= $count ?> film</span>
    </a>
    <?php endforeach; ?>
  </div>

  <?php else: ?>
  <div style="max-width:1400px;margin:0 auto;padding:0 2rem 1rem">
    <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap">
      <a href="dil.php" style="color:var(--text3);font-size:.9rem">← Tüm Diller</a>
      <h1 class="font-display" style="font-size:2rem"><?= $dilEmojis[$dil] ?? '🎬' ?> <?= htmlspecialchars($dil) ?></h1>
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
      <?php if ($page > 1): ?><a href="?dil=<?=urlencode($dil)?>&page=<?=$page-1?>" class="page-btn">← Önceki</a><?php endif; ?>
      <span class="page-info"><?= $page ?> / <?= $totalPages ?></span>
      <?php if ($page < $totalPages): ?><a href="?dil=<?=urlencode($dil)?>&page=<?=$page+1?>" class="page-btn">Sonraki →</a><?php endif; ?>
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
