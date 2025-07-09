<?php
session_start();

// Fanakatonana ny session rehetra
session_unset();
session_destroy();

// Fanakatonana ny cookies session raha misy
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Fampandrenesana fahombiazana ny fivoahana
$_SESSION = array(); // Manadio ny session array
session_start(); // Manomboka session vaovao ho an'ny hafatra
$_SESSION['logout_success'] = 'Nivoaka tamim-pahombiazana ianao';

// Famindrana mankany amin'ny pejin'ny fidirana
header("Location: fidirana.php");
exit();
?>