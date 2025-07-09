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
$mpianatra = $_POST['mpianatra'];
$taranja = $_POST['taranja'];
$kilasy = $_POST['kilasy'];
$naoty = $_POST['naoty'];

// Fanamarinana ny angon-drakitra
if (empty($mpianatra) || empty($taranja) || empty($kilasy) || empty($naoty)) {
    $_SESSION['hafatra_diso'] = 'Fenoina daholo ny sehatra ilaina.';
    header("Location: mpampianatra.php");
    exit;
}

// Fanamarinana ny naoty
if (!is_numeric($naoty) || $naoty < 0 || $naoty > 20) {
    $_SESSION['hafatra_diso'] = 'Ny naoty dia tokony ho eo anelanelan\'ny 0 sy 20.';
    header("Location: mpampianatra.php");
    exit;
}

// Fifandraisana MySQL
$mysqli = new mysqli("localhost", "root", "Basique12345", "educonnect");
$mysqli->set_charset("utf8mb4");

if ($mysqli->connect_error) {
    $_SESSION['hafatra_diso'] = 'Fahadisoana fifandraisana amin\'ny angon-drakitra.';
    header("Location: mpampianatra.php");
    exit;
}

// Fanamarinana raha misy ny tabilao naoty
$check_table = $mysqli->query("SHOW TABLES LIKE 'naoty'");
if ($check_table->num_rows == 0) {
    $_SESSION['hafatra_diso'] = 'Ny tabilao naoty dia tsy mbola noforonina. Jereo ny create_table_naoty.sql.';
    header("Location: mpampianatra.php");
    exit;
}

// Fampidirana ny naoty
$fanontaniana = $mysqli->prepare("
    INSERT INTO naoty (mpianatra, mpampianatra, taranja, kilasy, naoty, daty_fampidirana) 
    VALUES (?, ?, ?, ?, ?, NOW())
");

if ($fanontaniana === false) {
    $_SESSION['hafatra_diso'] = 'Fahadisoana tamin\'ny fanomanana ny fanontaniana SQL: ' . $mysqli->error;
    header("Location: mpampianatra.php");
    exit;
}

if ($fanontaniana->bind_param("ssssd", $mpianatra, $anarana_feno, $taranja, $kilasy, $naoty)) {
    if ($fanontaniana->execute()) {
        $_SESSION['hafatra'] = "Ny naoty dia voalefa soa aman-tsara ho an'i $mpianatra ($naoty/20).";
    } else {
        $_SESSION['hafatra_diso'] = 'Nisy olana tamin\'ny fampidirana ny naoty: ' . $fanontaniana->error;
    }
} else {
    $_SESSION['hafatra_diso'] = 'Nisy olana tamin\'ny fanomanana ny fandefasana: ' . $fanontaniana->error;
}

$fanontaniana->close();
$mysqli->close();

header("Location: mpampianatra.php");
exit;
?>