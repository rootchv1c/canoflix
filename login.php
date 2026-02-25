<?php
session_start();
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (login($username, $password)) {
        header('Location: index.php');
        exit;
    } else {
        $error = 'Kullanıcı adı veya şifre hatalı.';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Giriş Yap — StreamFlix</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="auth-body">
<div class="auth-screen">
  <div class="auth-bg">
    <div class="auth-bg-grid"></div>
    <div class="auth-bg-glow"></div>
  </div>
  
  <div class="auth-box">
    <a href="login.php" class="auth-logo">
      <svg width="40" height="40" viewBox="0 0 32 32" fill="none">
        <rect width="32" height="32" rx="8" fill="#e50914"/>
        <path d="M8 8h4l4 10 4-10h4l-6 16h-4L8 8z" fill="white"/>
      </svg>
      <span>StreamFlix</span>
    </a>
    
    <h1 class="auth-title">Hoş Geldin</h1>
    <p class="auth-subtitle">Hesabınla giriş yap ve izlemeye başla</p>
    
    <?php if ($error): ?>
    <div class="auth-error">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>
    
    <form method="POST" class="auth-form">
      <div class="form-group">
        <label>Kullanıcı Adı</label>
        <input type="text" name="username" placeholder="kullanici_adin" required autocomplete="username"
               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Şifre</label>
        <div class="password-wrap">
          <input type="password" name="password" id="passwordInput" placeholder="••••••••" required autocomplete="current-password">
          <button type="button" class="toggle-password" onclick="togglePassword()">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" id="eyeIcon"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
      </div>
      <button type="submit" class="btn-auth">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
        Giriş Yap
      </button>
    </form>
    
    <p class="auth-switch">Hesabın yok mu? <a href="register.php">Kayıt Ol</a></p>
  </div>
</div>
<script>
function togglePassword() {
  const inp = document.getElementById('passwordInput');
  const icon = document.getElementById('eyeIcon');
  if (inp.type === 'password') {
    inp.type = 'text';
    icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
  } else {
    inp.type = 'password';
    icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
  }
}
</script>
</body>
</html>
