<?php
session_start();

// ✅ FANARAHA-MASO NY FIDIRANA
require_once 'check_access.php';
checkAccess(['G_Tous_Professeurs']);

// Fanamarinana raha tafiditra ny mpampiasa
if (!isset($_SESSION['username'])) {
    echo json_encode(['fahombiazana' => false, 'hadisoana' => 'Tsy tafiditra']);
    exit;
}

// Fanamarinana ny fomba fandefasana
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['fahombiazana' => false, 'hadisoana' => 'Fomba fandefasana tsy mety']);
    exit;
}

// Fanamarinana ny ID naoty
if (!isset($_POST['id_naoty']) || empty($_POST['id_naoty'])) {
    echo json_encode(['fahombiazana' => false, 'hadisoana' => 'ID naoty tsy voafaritra']);
    exit;
}

$id_naoty = $_POST['id_naoty'];
$anarana_feno = $_SESSION['fullname'];

// Fifandraisana MySQL
$mysqli = new mysqli("localhost", "root", "Basique12345", "educonnect");
$mysqli->set_charset("utf8mb4");

if ($mysqli->connect_error) {
    echo json_encode(['fahombiazana' => false, 'hadisoana' => 'Fahadisoana fifandraisana MySQL']);
    exit;
}

try {
    // Fanamarinana raha an'ity mpampianatra ity ny naoty
    $fanontaniana_fanamarinana = $mysqli->prepare("SELECT id FROM naoty WHERE id = ? AND mpampianatra = ?");
    $fanontaniana_fanamarinana->bind_param("is", $id_naoty, $anarana_feno);
    $fanontaniana_fanamarinana->execute();
    $valiny_fanamarinana = $fanontaniana_fanamarinana->get_result();
    
    if ($valiny_fanamarinana->num_rows === 0) {
        echo json_encode(['fahombiazana' => false, 'hadisoana' => 'Tsy misy fahazoan-dalana hamafa ity naoty ity']);
        exit;
    }
    
    // Fafao ny naoty
    $fanontaniana_fafana = $mysqli->prepare("DELETE FROM naoty WHERE id = ? AND mpampianatra = ?");
    $fanontaniana_fafana->bind_param("is", $id_naoty, $anarana_feno);
    
    if ($fanontaniana_fafana->execute()) {
        echo json_encode(['fahombiazana' => true, 'hafatra' => 'Voafafa soa aman-tsara ny naoty']);
    } else {
        echo json_encode(['fahombiazana' => false, 'hadisoana' => 'Tsy afaka namafa ny naoty']);
    }
    
} catch (Exception $e) {
    echo json_encode(['fahombiazana' => false, 'hadisoana' => $e->getMessage()]);
} finally {
    $mysqli->close();
}
?>