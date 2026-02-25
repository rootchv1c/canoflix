<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/data.php';

if (!isLoggedIn()) { header('Location: login.php'); exit; }

$user = getCurrentUser();
$history = array_slice($user['watch_history'] ?? [], 0, 12);
$favCount = count($user['favorites'] ?? []);
$histCount = count($user['watch_history'] ?? []);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'change_password') {
        $old = $_POST['old_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        if (strlen($new) < 6) {
            $error = 'Yeni şifre en az 6 karakter olmalı.';
        } elseif (!password_verify($old, $user['password'])) {
            $error = 'Mevcut şifre hatalı.';
        } else {
            $users = getUsers();
            $users[$_SESSION['user_id']]['password'] = password_hash($new, PASSWORD_DEFAULT);
            saveUsers($users);
            $success = 'Şifre başarıyla değiştirildi!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profilim — StreamFlix</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
<style>
.profile-page { max-width: 900px; margin: 0 auto; padding: 2rem; }
.profile-header {
  display: flex; align-items: center; gap: 2rem; flex-wrap: wrap;
  background: var(--surface); border-radius: 16px; padding: 2rem;
  margin-bottom: 2rem; border: 1px solid var(--surface3);
}
.profile-avatar { width: 90px; height: 90px; border-radius: 50%; border: 3px solid var(--accent); }
.profile-name { font-family: 'Bebas Neue', sans-serif; font-size: 2rem; letter-spacing: 0.05em; }
.profile-since { color: var(--text2); font-size: 0.9rem; }
.profile-stats { display: flex; gap: 2rem; margin-top: 1rem; flex-wrap: wrap; }
.stat { text-align: center; }
.stat-num { font-family: 'Bebas Neue', sans-serif; font-size: 2rem; color: var(--accent); }
.stat-label { font-size: 0.8rem; color: var(--text2); }
.profile-section {
  background: var(--surface); border-radius: 16px; padding: 1.5rem 2rem;
  margin-bottom: 1.5rem; border: 1px solid var(--surface3);
}
.profile-section h3 { font-size: 1rem; font-weight: 700; margin-bottom: 1.25rem; }
.form-row { display: flex; gap: 1rem; flex-wrap: wrap; }
.form-row .form-group { flex: 1; min-width: 200px; }
</style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div style="padding-top:calc(var(--nav-h) + 2rem)">
  <div class="profile-page">
    
    <div class="profile-header">
      <img class="profile-avatar" src="<?= htmlspecialchars($user['avatar'] ?? '') ?>" alt="avatar">
      <div>
        <div class="profile-name"><?= htmlspecialchars($user['username']) ?></div>
        <div class="profile-since">
          <?= !empty($user['email']) ? htmlspecialchars($user['email']) . ' · ' : '' ?>
          Üye: <?= date('d.m.Y', strtotime($user['created_at'])) ?>
        </div>
        <div class="profile-stats">
          <div class="stat"><div class="stat-num"><?= $favCount ?></div><div class="stat-label">Favori</div></div>
          <div class="stat"><div class="stat-num"><?= $histCount ?></div><div class="stat-label">İzlenen</div></div>
        </div>
      </div>
      <div style="margin-left:auto">
        <a href="favoriler.php" class="btn-primary">❤️ Favorilerim</a>
      </div>
    </div>

    <?php if (!empty($history)): ?>
    <div class="profile-section">
      <h3>🕐 Son İzlediklerim</h3>
      <div class="browse-grid" style="grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:10px">
        <?php foreach ($history as $h): ?>
        <div class="card" style="cursor:pointer"
             onclick="<?= $h['type']==='film' ? "window.location='izle.php?type=film&name=".urlencode($h['name'])."'" : "window.location='izle.php?type=dizi&name=".urlencode($h['name'])."&season=".($h['season']??1)."&episode=".($h['episode']??1)."'" ?>">
          <div class="card-img-wrap">
            <img class="card-img" src="<?= htmlspecialchars($h['logo']??'') ?>" loading="lazy" onerror="this.src='assets/placeholder.svg'">
            <div class="card-overlay"><div class="card-play">▶</div></div>
          </div>
          <div class="card-info">
            <p class="card-title"><?= htmlspecialchars($h['name']) ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="profile-section">
      <h3>🔐 Şifre Değiştir</h3>
      <?php if ($error): ?><div class="auth-error" style="margin-bottom:1rem"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <?php if ($success): ?><div style="background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.3);color:#4ade80;padding:10px 14px;border-radius:10px;margin-bottom:1rem;font-size:0.9rem"><?= htmlspecialchars($success) ?></div><?php endif; ?>
      <form method="POST">
        <input type="hidden" name="action" value="change_password">
        <div class="form-row">
          <div class="form-group">
            <label>Mevcut Şifre</label>
            <input type="password" name="old_password" required>
          </div>
          <div class="form-group">
            <label>Yeni Şifre</label>
            <input type="password" name="new_password" required minlength="6">
          </div>
        </div>
        <button type="submit" class="btn-primary" style="margin-top:1rem">Şifreyi Güncelle</button>
      </form>
    </div>

    <div style="text-align:center;padding:1rem">
      <a href="logout.php" style="color:var(--accent);font-weight:600">Çıkış Yap →</a>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
<div class="toast" id="toast"></div>
<script src="assets/app.js"></script>
</body>
</html>
