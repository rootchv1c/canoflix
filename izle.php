<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/data.php';

if (!isLoggedIn()) { header('Location: login.php'); exit; }

$type = $_GET['type'] ?? 'film';
$name = urldecode($_GET['name'] ?? '');
$season = intval($_GET['season'] ?? 1);
$episode = intval($_GET['episode'] ?? 1);
$audioType = $_GET['audio'] ?? '';

if (empty($name)) { header('Location: index.php'); exit; }

$streamUrl = '';
$title = $name;
$logo = '';
$similar = [];

if ($type === 'film') {
    $film = getFilmByName($name);
    if (!$film) { header('Location: filmler.php'); exit; }
    $logo = $film['logo'];
    // Find matching version
    foreach ($film['versions'] as $v) {
        if (empty($audioType) || stripos($v['audio'], $audioType) !== false) {
            $streamUrl = $v['url'];
            $audioType = $v['audio'];
            break;
        }
    }
    $similar = getSimilarFilms($film, 12);
    // Add to watch history
    if (isLoggedIn()) {
        addToWatchHistory($_SESSION['user_id'], [
            'type' => 'film', 'name' => $name, 'logo' => $logo, 'audio' => $audioType
        ]);
    }
} else {
    // Dizi episode
    $show = getShowByName($name);
    if (!$show) { header('Location: diziler.php'); exit; }
    $logo = $show['logo'];
    if (isset($show['seasons'][$season])) {
        foreach ($show['seasons'][$season] as $ep) {
            if ($ep['episode'] === $episode) {
                if (empty($audioType) || stripos($ep['audio'], $audioType) !== false) {
                    $streamUrl = $ep['url'];
                    $audioType = $ep['audio'];
                    break;
                }
            }
        }
        if (!$streamUrl && !empty($show['seasons'][$season])) {
            $ep = $show['seasons'][$season][0];
            $streamUrl = $ep['url'];
            $audioType = $ep['audio'];
            $episode = $ep['episode'];
        }
    }
    $title = $name . ' - S' . str_pad($season, 2, '0', STR_PAD_LEFT) . 'B' . str_pad($episode, 2, '0', STR_PAD_LEFT);
    if (isLoggedIn()) {
        addToWatchHistory($_SESSION['user_id'], [
            'type' => 'dizi', 'name' => $name, 'logo' => $logo, 'season' => $season, 'episode' => $episode
        ]);
    }
}

// YouTube URL mi?
$isYouTube = false;
$youtubeEmbedId = '';
if ($streamUrl && preg_match('#(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/live/)([\w\-]{10,12})#', $streamUrl, $m)) {
    $isYouTube = true;
    $youtubeEmbedId = $m[1];
}
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
<body class="player-body">
<?php include 'includes/navbar.php'; ?>

<div class="player-page">
  <!-- VIDEO PLAYER -->
  <div class="player-wrap">
    <div class="player-container" id="playerContainer">
      <?php if ($streamUrl): ?>
      <?php if ($isYouTube): ?>
      <iframe id="youtubePlayer"
              src="https://www.youtube.com/embed/<?= htmlspecialchars($youtubeEmbedId) ?>?autoplay=1&rel=0&modestbranding=1"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
              allowfullscreen
              style="width:100%;height:100%;border:none;aspect-ratio:16/9;display:block">
      </iframe>
      <?php else: ?>
      <video id="videoPlayer" controls autoplay playsinline 
             poster="<?= htmlspecialchars($logo) ?>"
             crossorigin="anonymous">
        <source src="<?= htmlspecialchars($streamUrl) ?>" type="application/x-mpegURL">
        Tarayıcınız video oynatmayı desteklemiyor.
      </video>
      <?php endif; ?>
      <?php else: ?>
      <div class="player-error">
        <div class="player-error-icon">😔</div>
        <h3>Video yüklenemedi</h3>
        <p>Bu içerik şu an kullanılamıyor.</p>
        <a href="javascript:history.back()" class="btn-primary">Geri Dön</a>
      </div>
      <?php endif; ?>
    </div>
    
    <!-- PLAYER CONTROLS BAR -->
    <div class="player-info-bar">
      <div class="player-info-left">
        <a href="javascript:history.back()" class="back-btn">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5m7-7-7 7 7 7"/></svg>
        </a>
        <div>
          <h2 class="player-title"><?= htmlspecialchars($title) ?></h2>
          <?php if ($type === 'dizi'): ?>
          <p class="player-subtitle">Sezon <?= $season ?> · Bölüm <?= $episode ?></p>
          <?php elseif ($audioType): ?>
          <p class="player-subtitle"><?= htmlspecialchars($audioType) ?></p>
          <?php endif; ?>
        </div>
      </div>
      <div class="player-info-right">
        <?php if ($type === 'film' && !empty($film['versions']) && count($film['versions']) > 1): ?>
        <div class="audio-switcher">
          <?php foreach ($film['versions'] as $v): ?>
          <a href="izle.php?type=film&name=<?= urlencode($name) ?>&audio=<?= urlencode($v['audio']) ?>"
             class="audio-btn <?= $v['url'] === $streamUrl ? 'active' : '' ?>">
            <?= htmlspecialchars($v['audio']) ?>
          </a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <button class="fav-btn" onclick="toggleFavorite('<?= htmlspecialchars(addslashes($name)) ?>', '<?= $type ?>', '<?= htmlspecialchars(addslashes($logo)) ?>')" id="favBtn">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
          Favorilere Ekle
        </button>
      </div>
    </div>
  </div>
  
  <div class="player-sidebar">
    <?php if ($type === 'dizi' && isset($show)):
      $seasons = $show['seasons'];
      ksort($seasons);
    ?>
    <!-- EPISODE LIST -->
    <div class="episode-panel">
      <div class="episode-panel-header">
        <h3>Bölümler</h3>
        <div class="season-tabs">
          <?php foreach ($seasons as $s => $eps): ?>
          <button class="season-tab <?= $s === $season ? 'active' : '' ?>"
                  onclick="switchSeason(<?= $s ?>)">
            S<?= $s ?>
          </button>
          <?php endforeach; ?>
        </div>
      </div>
      <?php foreach ($seasons as $s => $eps): ?>
      <div class="episode-list <?= $s === $season ? 'active' : '' ?>" id="season-<?= $s ?>">
        <?php
        // Group by audio type
        $audioGroups = [];
        foreach ($eps as $ep) {
          $key = $ep['audio'] ?: 'Orijinal';
          $audioGroups[$key][] = $ep;
        }
        // Get unique episodes (show one per episode number, prefer audio filter)
        $uniqueEps = [];
        foreach ($eps as $ep) {
          $k = $ep['episode'];
          if (!isset($uniqueEps[$k])) $uniqueEps[$k] = $ep;
        }
        ksort($uniqueEps);
        ?>
        <?php foreach ($uniqueEps as $epNum => $ep): ?>
        <a href="izle.php?type=dizi&name=<?= urlencode($name) ?>&season=<?= $s ?>&episode=<?= $ep['episode'] ?>&audio=<?= urlencode($ep['audio']) ?>"
           class="episode-item <?= ($s === $season && $ep['episode'] === $episode) ? 'active' : '' ?>">
          <div class="ep-num">B<?= str_pad($ep['episode'], 2, '0', STR_PAD_LEFT) ?></div>
          <div class="ep-info">
            <span class="ep-title"><?= htmlspecialchars($ep['name']) ?></span>
            <?php if ($ep['audio']): ?>
            <span class="ep-audio"><?= htmlspecialchars($ep['audio']) ?></span>
            <?php endif; ?>
          </div>
          <?php if ($s === $season && $ep['episode'] === $episode): ?>
          <div class="ep-playing">▶</div>
          <?php endif; ?>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endforeach; ?>
      
      <!-- PREV/NEXT BUTTONS -->
      <div class="ep-nav-buttons">
        <?php
        $allEps = [];
        foreach ($seasons as $s => $eps) {
          foreach ($eps as $ep) {
            $allEps[] = ['season' => $s, 'episode' => $ep['episode'], 'audio' => $ep['audio']];
          }
        }
        $unique = [];
        $seen = [];
        foreach ($allEps as $e) {
          $k = $e['season'].'-'.$e['episode'];
          if (!isset($seen[$k])) { $seen[$k] = true; $unique[] = $e; }
        }
        $currentIdx = -1;
        foreach ($unique as $i => $e) {
          if ($e['season'] === $season && $e['episode'] === $episode) { $currentIdx = $i; break; }
        }
        $prev = $currentIdx > 0 ? $unique[$currentIdx-1] : null;
        $next = $currentIdx < count($unique)-1 ? $unique[$currentIdx+1] : null;
        ?>
        <?php if ($prev): ?>
        <a href="izle.php?type=dizi&name=<?= urlencode($name) ?>&season=<?= $prev['season'] ?>&episode=<?= $prev['episode'] ?>&audio=<?= urlencode($prev['audio']) ?>"
           class="ep-nav-btn prev-btn">
          ← Önceki Bölüm
        </a>
        <?php endif; ?>
        <?php if ($next): ?>
        <a href="izle.php?type=dizi&name=<?= urlencode($name) ?>&season=<?= $next['season'] ?>&episode=<?= $next['episode'] ?>&audio=<?= urlencode($next['audio']) ?>"
           class="ep-nav-btn next-btn">
          Sonraki Bölüm →
        </a>
        <?php endif; ?>
      </div>
    </div>
    <?php elseif (!empty($similar)): ?>
    <!-- SIMILAR FILMS -->
    <div class="similar-panel">
      <h3>Benzer Filmler</h3>
      <div class="similar-list">
        <?php foreach ($similar as $s): ?>
        <div class="similar-item" onclick="openFilm('<?= htmlspecialchars(addslashes($s['name'])) ?>')">
          <img src="<?= htmlspecialchars($s['logo']) ?>" alt="<?= htmlspecialchars($s['name']) ?>" 
               loading="lazy" onerror="this.src='assets/placeholder.svg'">
          <div class="similar-info">
            <p class="similar-title"><?= htmlspecialchars($s['name']) ?></p>
            <?php if (!empty($s['genres'])): ?>
            <p class="similar-genre"><?= htmlspecialchars(implode(', ', array_slice($s['genres'], 0, 2))) ?></p>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
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

<!-- HLS.js for M3U8 streams -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/hls.js/1.4.12/hls.min.js"></script>
<script src="assets/app.js"></script>
<script>
// Init HLS player
const video = document.getElementById('videoPlayer');
if (video) {
  const src = video.querySelector('source')?.src;
  if (src && Hls.isSupported()) {
    const hls = new Hls({
      maxLoadingDelay: 4,
      minAutoBitrate: 0,
      xhrSetup: function(xhr) {
        xhr.setRequestHeader('Referer', 'https://twitter.com/');
        xhr.setRequestHeader('User-Agent', 'googleusercontent');
      }
    });
    hls.loadSource(src);
    hls.attachMedia(video);
    hls.on(Hls.Events.MANIFEST_PARSED, function() {
      video.play().catch(()=>{});
    });
  } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
    video.src = src;
    video.play().catch(()=>{});
  }
}

function switchSeason(s) {
  document.querySelectorAll('.episode-list').forEach(el => el.classList.remove('active'));
  document.querySelectorAll('.season-tab').forEach(el => el.classList.remove('active'));
  const list = document.getElementById('season-' + s);
  if (list) list.classList.add('active');
  event.target.classList.add('active');
}
</script>
</body>
</html>
