<?php
session_start();

// ✅ FANARAHA-MASO NY FIDIRANA
require_once 'check_access.php';
checkAccess(['G_Tous_Professeurs']);

// Fanamarinana raha tafiditra ny mpampiasa
if (!isset($_SESSION['username'])) {
    header("Location: fidirana.php");
    exit;
}

// Fanamarinana ny fomba fandefasana
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['hafatra_diso'] = 'Fomba fandefasana tsy mety.';
    header("Location: mpampianatra.php");
    exit;
}

$anarana_feno = $_SESSION['fullname'];
$taranja = $_POST['taranja'];
$kilasy = $_POST['kilasy'];
$lohateny = $_POST['lohateny'];
$votoaty = $_POST['votoaty'];
$daty_farany = $_POST['daty_farany'];

// Fanamarinana ny angon-drakitra
if (empty($taranja) || empty($kilasy) || empty($lohateny) || empty($votoaty) || empty($daty_farany)) {
    $_SESSION['hafatra_diso'] = 'Fenoina daholo ny sehatra ilaina.';
    header("Location: mpampianatra.php");
    exit;
}

// Fanamarinana ny daty
$daty_farany_obj = new DateTime($daty_farany);
$ankehitriny = new DateTime();
if ($daty_farany_obj <= $ankehitriny) {
    $_SESSION['hafatra_diso'] = 'Ny daty farany dia tokony ho any aoriana.';
    header("Location: mpampianatra.php");
    exit;
}

// Fitantanana ny rakitra
$anarana_rakitra = null;
if (isset($_FILES['rakitra']) && $_FILES['rakitra']['error'] == 0) {
    $rakitra = $_FILES['rakitra'];
    $anarana_rakitra_taloha = $rakitra['name'];
    $extension = pathinfo($anarana_rakitra_taloha, PATHINFO_EXTENSION);
    $anarana_rakitra = uniqid() . '.' . $extension;
    
    // Fanamarinana ny karazana rakitra
    $karazana_ekena = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'png'];
    if (!in_array(strtolower($extension), $karazana_ekena)) {
        $_SESSION['hafatra_diso'] = 'Karazana rakitra tsy ekena.';
        header("Location: mpampianatra.php");
        exit;
    }
    
    // Fanamarinana ny habe
    if ($rakitra['size'] > 10 * 1024 * 1024) { // 10MB
        $_SESSION['hafatra_diso'] = 'Ny habe ny rakitra dia mihoatra ny 10MB.';
        header("Location: mpampianatra.php");
        exit;
    }
    
    // Mamorona ny laha-tahiry raha tsy misy
    $laha_tahiry = 'rakitra_asa/';
    if (!is_dir($laha_tahiry)) {
        mkdir($laha_tahiry, 0755, true);
    }
    
    // Mamindra ny rakitra
    if (!move_uploaded_file($rakitra['tmp_name'], $laha_tahiry . $anarana_rakitra)) {
        $_SESSION['hafatra_diso'] = 'Tsy afaka namindra ny rakitra.';
        header("Location: mpampianatra.php");
        exit;
    }
}

// Fifandraisana MySQL
$mysqli = new mysqli("localhost", "root", "Basique12345", "educonnect");
$mysqli->set_charset("utf8mb4");

if ($mysqli->connect_error) {
    $_SESSION['hafatra_diso'] = 'Fahadisoana fifandraisana amin\'ny angon-drakitra.';
    header("Location: mpampianatra.php");
    exit;
}

// Fampidirana ny asa
$fanontaniana = $mysqli->prepare("
    INSERT INTO asa (mpampianatra, taranja, kilasy, lohateny, votoaty, rakitra, daty_farany, daty_fampidirana) 
    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
");

if ($fanontaniana->bind_param("sssssss", $anarana_feno, $taranja, $kilasy, $lohateny, $votoaty, $anarana_rakitra, $daty_farany)) {
    if ($fanontaniana->execute()) {
        $_SESSION['hafatra'] = "Ny asa \"$lohateny\" dia voalefa soa aman-tsara ho an'ny kilasy $kilasy.";
    } else {
        $_SESSION['hafatra_diso'] = 'Nisy olana tamin\'ny fampidirana ny asa.';
        // Fafàna ny rakitra raha tsy voalefa ny asa
        if ($anarana_rakitra && file_exists($laha_tahiry . $anarana_rakitra)) {
            unlink($laha_tahiry . $anarana_rakitra);
        }
    }
} else {
    $_SESSION['hafatra_diso'] = 'Nisy olana tamin\'ny fanomanana ny fandefasana.';
    // Fafàna ny rakitra raha tsy voalefa ny asa
    if ($anarana_rakitra && file_exists($laha_tahiry . $anarana_rakitra)) {
        unlink($laha_tahiry . $anarana_rakitra);
    }
}

$fanontaniana->close();
$mysqli->close();

header("Location: mpampianatra.php");
exit;
?>