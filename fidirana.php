<?php
session_start();

// Makany ny hadisoana session raha misy
$login_error = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : null;
$login_error_message = isset($_SESSION['login_error_message']) ? $_SESSION['login_error_message'] : null;

// Manadio ny hadisoana amin'ny session aorian'ny fakana azy
if (isset($_SESSION['login_error'])) {
    unset($_SESSION['login_error']);
    unset($_SESSION['login_error_message']);
}
?>
<!DOCTYPE html>
<html lang="mg">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Fidirana Fyanatra</title>
  <link rel="manifest" href="/manifest.json" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="login.css">
  <meta name="theme-color" content="#2196F3">
  <meta name="description" content="Fidirana azo antoka amin'ny toerana fanabeazana Fyanatra">
</head>
<body>
  <div class="login-container">
    <div class="logo-container">
      <div class="logo">
        <i class="fas fa-graduation-cap"></i>
        Fyanatra
      </div>
      <p class="subtitle">Midira amin'ny toerana fanabeazana</p>
    </div>

    <h2>Fidirana amin'ny Fyanatra</h2>
    
    <!-- Faritra fanehoana hadisoana -->
    <div id="error-container" style="display: none;"></div>
    
    <?php if ($login_error): ?>
    <div class="error-message" id="phpError">
      <i class="fas fa-exclamation-triangle"></i>
      <span><?php echo htmlspecialchars($login_error_message); ?></span>
    </div>
    <?php endif; ?>
    
    <form action="fanamariana.php" method="POST">
      <div class="form-group">
        <label for="username">
          <i class="fas fa-user"></i> Anaran'ny mpampiasa :
        </label>
        <div class="input-container">
          <input type="text" id="username" name="username" required placeholder="Ampidiro ny anaran'ny mpampiasa" autocomplete="username">
          <i class="fas fa-user input-icon"></i>
        </div>
      </div>

      <div class="form-group">
        <label for="password">
          <i class="fas fa-lock"></i> Teny miafina :
        </label>
        <div class="input-container">
          <input type="password" id="password" name="password" required placeholder="Ampidiro ny teny miafina" autocomplete="current-password">
          <i class="fas fa-lock input-icon"></i>
          <button type="button" class="password-toggle" onclick="togglePassword()" aria-label="Aseho/afeno ny teny miafina">
            <i class="fas fa-eye" id="toggleIcon"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="login-btn" id="loginBtn">
        <i class="fas fa-sign-in-alt"></i> Miditra
      </button>
    </form>
  </div>

  <script src="js/login.js"></script>

  <!-- ✅ Fisoratana ny service worker -->
  <script>
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.register('/service-worker.js')
        .then(reg => console.log("✅ Service Worker voasoratra"))
        .catch(err => console.error("❌ Hadisoana Service Worker :", err));
    }
  </script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Fitantanana hadisoana PHP
      <?php if ($login_error): ?>
        handleLoginError('<?php echo $login_error; ?>', '<?php echo addslashes($login_error_message); ?>');
      <?php endif; ?>
    });
  </script>
</body>
</html>