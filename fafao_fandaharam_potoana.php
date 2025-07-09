<?php
session_start();

// ✅ FANARAHA-MASO NY FIDIRANA - Ny mpizara ihany no afaka miditra
require_once 'check_access.php';
checkAccess(['G_Tous_Personnel_Admin']);

// Fifandraisana MySQL
$mysqli = new mysqli("localhost", "root", "Basique12345", "educonnect");
if ($mysqli->connect_error) {
    die("Hadisoana fifandraisana : " . $mysqli->connect_error);
}

// Maka ny ID sy kilasy avy amin'ny GET
$id = $_GET['id'] ?? '';
$kilasy = $_GET['kilasy'] ?? '';

if (empty($id) || empty($kilasy)) {
    header("Location: mpizara.php");
    exit;
}

// Fafana ny fampianarana
$stmt = $mysqli->prepare("DELETE FROM fandaharam_potoana WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION['hafatra'] = '<div class="notification notification-success">' . 
                          '<div class="notification-content">' .
                          '<i class="fas fa-check-circle"></i>' .
                          '<span>Fampianarana nofafana tamim-pahombiazana</span>' .
                          '</div></div>';
} else {
    $_SESSION['hafatra'] = '<div class="notification notification-error">' . 
                          '<div class="notification-content">' .
                          '<i class="fas fa-exclamation-circle"></i>' .
                          '<span>Nisy hadisoana teo am-pafana</span>' .
                          '</div></div>';
}

$stmt->close();
header("Location: fitantanana_fandaharam_potoana.php?kilasy=" . urlencode($kilasy));
exit();
?>