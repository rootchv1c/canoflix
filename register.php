<?php
session_start();
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    $email = trim($_POST['email'] ?? '');
    
    if ($password !== $password2) {
        $error = 'Şifreler eşleşmiyor.';
    } else {
        $result = register($username, $password, $email);
        if ($result['success']) {
            header('Location: index.php');
            exit;
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kayıt Ol — StreamFlix</title>
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
    
    <h1 class="auth-title">Hesap Oluştur</h1>
    <p class="auth-subtitle">StreamFlix'e katıl, hemen izlemeye başla!</p>
    
    <?php if ($error): ?>
    <div class="auth-error">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>
    
    <form method="POST" class="auth-form">
      <div class="form-group">
        <label>Kullanıcı Adı *</label>
        <input type="text" name="username" placeholder="harika_izleyici" required minlength="3"
               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>E-posta (opsiyonel)</label>
        <input type="email" name="email" placeholder="sen@example.com"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Şifre *</label>
        <div class="password-wrap">
          <input type="password" name="password" id="passwordInput" placeholder="En az 6 karakter" required minlength="6">
          <button type="button" class="toggle-password" onclick="togglePassword('passwordInput', 'eye1')">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" id="eye1"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
      </div>
      <div class="form-group">
        <label>Şifre Tekrar *</label>
        <div class="password-wrap">
          <input type="password" name="password2" id="password2Input" placeholder="Şifreni tekrar gir" required>
          <button type="button" class="toggle-password" onclick="togglePassword('password2Input', 'eye2')">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" id="eye2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
      </div>
      <button type="submit" class="btn-auth">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
        Kayıt Ol
      </button>
    </form>
    
    <p class="auth-switch">Zaten hesabın var mı? <a href="login.php">Giriş Yap</a></p>
  </div>
</div>
<script>
function togglePassword(inputId, iconId) {
  const inp = document.getElementById(inputId);
  const icon = document.getElementById(iconId);
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
