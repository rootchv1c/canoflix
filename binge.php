<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/data.php';
if (!isLoggedIn()) { header('Location: login.php'); exit; }

$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 48;
$sort = $_GET['sort'] ?? 'episodes'; // episodes | seasons

$allShows = loadShows(); // returns array of name=>data

// Build list with episode/season counts
$list = [];
foreach ($allShows as $name => $data) {
    $epCount = count($data['episodes'] ?? []);
    $seasons = array_unique(array_column($data['episodes'] ?? [], 'season'));
    $list[] = [
        'name' => $name,
        'logo' => $data['logo'],
        'episode_count' => $epCount,
        'season_count' => count($seasons),
    ];
}

if ($sort === 'seasons') {
    usort($list, fn($a,$b) => $b['season_count'] - $a['season_count']);
} else {
    usort($list, fn($a,$b) => $b['episode_count'] - $a['episode_count']);
}

$total = count($list);
$totalPages = ceil($total / $perPage);
$items = array_slice($list, ($page-1)*$perPage, $perPage);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Binge-Worthy Diziler — StreamFlix</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
<style>
.binge-badge {
  position: absolute; top: 8px; left: 8px;
  background: linear-gradient(135deg, #e50914, #ff6b35);
  color: #fff; padding: .25rem .6rem; border-radius: 6px;
  font-size: .72rem; font-weight: 700; z-index: 2;
}
.ep-counter {
  position: absolute; bottom: 0; left: 0; right: 0;
  background: linear-gradient(transparent, rgba(0,0,0,.9));
  padding: .5rem .6rem .4rem;
  font-size: .75rem; color: #fff; font-weight: 600;
}
</style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div style="padding-top: calc(var(--nav-h) + 2rem)">
  <div class="page-header">
    <div>
      <h1 class="font-display" style="font-size:2.2rem">📺 Binge-Worthy Diziler</h1>
      <p style="color:var(--text2);margin-top:.3rem">Bir oturuşta bitiremeyeceğin diziler — en çok bölümlüler önce!</p>
    </div>
    <div style="margin-left:auto;display:flex;gap:.5rem">
      <a href="?sort=episodes<?= $page>1?'&page='.$page:'' ?>" class="filter-btn <?= $sort==='episodes'?'active':'' ?>">Bölüme Göre</a>
      <a href="?sort=seasons<?= $page>1?'&page='.$page:'' ?>" class="filter-btn <?= $sort==='seasons'?'active':'' ?>">Sezona Göre</a>
    </div>
  </div>

  <div class="browse-grid-container">
    <div class="browse-grid">
      <?php foreach ($items as $i => $show): 
        $rank = ($page-1)*$perPage + $i + 1;
      ?>
      <div class="card" onclick="location.href='izle.php?type=dizi&name=<?= urlencode($show['name']) ?>'">
        <div class="card-img-wrap" style="position:relative">
          <img class="card-img" src="<?= htmlspecialchars($show['logo']) ?>" loading="lazy" onerror="this.src='assets/placeholder.svg';this.classList.add('img-loaded');this.closest('.card-img-wrap')?.classList.add('loaded')">
          <div class="card-overlay"><div class="card-play">▶</div></div>
          <?php if ($rank <= 10): ?>
          <div class="binge-badge">#<?= $rank ?></div>
          <?php endif; ?>
          <div class="ep-counter">
            <?= $show['episode_count'] ?> Bölüm · <?= $show['season_count'] ?> Sezon
          </div>
        </div>
        <div class="card-info">
          <p class="card-title"><?= htmlspecialchars($show['name']) ?></p>
          <p class="card-meta"><?= $show['episode_count'] ?> bölüm</p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <?php if ($page > 1): ?><a href="?sort=<?=$sort?>&page=<?=$page-1?>" class="page-btn">← Önceki</a><?php endif; ?>
      <span class="page-info"><?= $page ?> / <?= $totalPages ?></span>
      <?php if ($page < $totalPages): ?><a href="?sort=<?=$sort?>&page=<?=$page+1?>" class="page-btn">Sonraki →</a><?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
<div class="toast" id="toast"></div>
<script src="assets/app.js"></script>
</body>
</html>
