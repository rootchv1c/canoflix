<nav class="navbar" id="navbar">
  <div class="nav-inner">
    <a href="index.php" class="nav-logo">
      <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
        <rect width="32" height="32" rx="8" fill="#e50914"/>
        <path d="M8 8h4l4 10 4-10h4l-6 16h-4L8 8z" fill="white"/>
      </svg>
      <span>StreamFlix</span>
    </a>
    
    <div class="nav-links" id="navLinks">
      <a href="index.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">Ana Sayfa</a>
      <a href="filmler.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'filmler.php' ? 'active' : '' ?>">Filmler</a>
      <a href="diziler.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'diziler.php' ? 'active' : '' ?>">Diziler</a>
      <a href="canli.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'canli.php' ? 'active' : '' ?>">📡 Canlı TV</a>
      <a href="muzik.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'muzik.php' ? 'active' : '' ?>">🎵 Müzik</a>
      <!-- Yeni Kategoriler -->
      <div class="nav-dropdown-wrap">
        <button class="nav-link nav-more-btn" onclick="toggleMoreMenu(event)">
          Keşfet ▾
        </button>
        <div class="nav-more-dropdown" id="moreDropdown">
          <a href="tur.php" class="dropdown-item">🎭 Türe Göre</a>
          <a href="dil.php" class="dropdown-item">🌍 Dile Göre</a>
          <a href="binge.php" class="dropdown-item">📺 Binge-Worthy</a>
          <a href="yeni.php" class="dropdown-item">🆕 Yeni Eklenenler</a>
          <a href="sans.php" class="dropdown-item">🎲 Şansına İzle</a>
        </div>
      </div>
    </div>

    <div class="nav-actions">
      <button class="nav-search-btn" onclick="toggleNavSearch()">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      </button>
      <?php if (isLoggedIn()): 
        $user = getCurrentUser();
      ?>
      <div class="nav-user" onclick="toggleUserMenu()">
        <img src="<?= htmlspecialchars($user['avatar'] ?? '') ?>" alt="avatar" class="nav-avatar">
        <span class="nav-username"><?= htmlspecialchars($_SESSION['username']) ?></span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
        <div class="user-dropdown" id="userDropdown">
          <a href="profil.php" class="dropdown-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Profilim
          </a>
          <a href="favoriler.php" class="dropdown-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            Favorilerim
          </a>
          <div class="dropdown-divider"></div>
          <a href="logout.php" class="dropdown-item dropdown-logout">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Çıkış Yap
          </a>
        </div>
      </div>
      <?php else: ?>
      <a href="login.php" class="btn-nav-login">Giriş Yap</a>
      <?php endif; ?>
      
      <button class="hamburger" id="hamburger" onclick="toggleMobileMenu()">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
  
  <!-- Mobile Nav Search -->
  <div class="nav-search-bar" id="navSearchBar">
    <input type="text" id="navSearchInput" placeholder="Film veya dizi ara..." autocomplete="off">
    <div class="nav-search-results" id="navSearchResults"></div>
  </div>
</nav>
