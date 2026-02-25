<footer class="site-footer">
  <div class="footer-inner">
    <div class="footer-brand">
      <a href="index.php" class="nav-logo">
        <svg width="28" height="28" viewBox="0 0 32 32" fill="none">
          <rect width="32" height="32" rx="8" fill="#e50914"/>
          <path d="M8 8h4l4 10 4-10h4l-6 16h-4L8 8z" fill="white"/>
        </svg>
        <span>StreamFlix</span>
      </a>
      <p class="footer-desc">Binlerce film ve dizi, tek platformda.</p>
    </div>
    <div class="footer-links">
      <div class="footer-col">
        <h4>Keşfet</h4>
        <a href="filmler.php">Filmler</a>
        <a href="diziler.php">Diziler</a>
        <a href="filmler.php?audio=Dublaj">Türkçe Dublaj</a>
        <a href="filmler.php?audio=Altyazı">Türkçe Altyazı</a>
      </div>
      <div class="footer-col">
        <h4>Kategoriler</h4>
        <a href="filmler.php?genre=Aksiyon">Aksiyon</a>
        <a href="filmler.php?genre=Komedi">Komedi</a>
        <a href="filmler.php?genre=Korku">Korku</a>
        <a href="filmler.php?genre=Bilim-Kurgu">Bilim Kurgu</a>
      </div>
      <div class="footer-col">
        <h4>Hesap</h4>
        <?php if (isLoggedIn()): ?>
        <a href="profil.php">Profilim</a>
        <a href="favoriler.php">Favorilerim</a>
        <a href="logout.php">Çıkış Yap</a>
        <?php else: ?>
        <a href="login.php">Giriş Yap</a>
        <a href="register.php">Kayıt Ol</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="footer-bottom">
    <p>© <?= date('Y') ?> StreamFlix. Tüm hakları saklıdır.</p>
  </div>
</footer>
