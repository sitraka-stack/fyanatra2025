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

// Fanamarinana ny kilasy
if (!isset($_GET['kilasy']) || empty($_GET['kilasy'])) {
    echo json_encode(['fahombiazana' => false, 'hadisoana' => 'Kilasy tsy voafaritra']);
    exit;
}

$kilasy = $_GET['kilasy'];
$anarana_feno = $_SESSION['fullname'];
$taranja = isset($_SESSION['matiere']) ? $_SESSION['matiere'] : '';

// ✅ CORRECTION: Normaliser le nom de la matière pour correspondre à la base
$taranja_normalise = $taranja;
if ($taranja === 'Mathematique') {
    $taranja_normalise = 'Matematika';
}

// Fifandraisana MySQL
$mysqli = new mysqli("localhost", "root", "Basique12345", "educonnect");
$mysqli->set_charset("utf8mb4");

if ($mysqli->connect_error) {
    echo json_encode(['fahombiazana' => false, 'hadisoana' => 'Fahadisoana fifandraisana MySQL']);
    exit;
}

try {
    // Makà ny naoty rehetra ho an'ity kilasy ity
    $fanontaniana_naoty = $mysqli->prepare("
        SELECT 
            id,
            mpianatra as anarana_mpianatra,
            taranja,
            naoty,
            DATE_FORMAT(daty_fampidirana, '%d/%m/%Y %H:%i') as daty_voalamina
        FROM naoty 
        WHERE kilasy = ? AND mpampianatra = ? AND taranja = ?
        ORDER BY daty_fampidirana DESC
    ");
    
    $fanontaniana_naoty->bind_param("sss", $kilasy, $anarana_feno, $taranja_normalise);
    $fanontaniana_naoty->execute();
    $valiny_naoty = $fanontaniana_naoty->get_result();
    
    $naoty = [];
    while ($andalana = $valiny_naoty->fetch_assoc()) {
        $naoty[] = $andalana;
    }
    
    // Makà ny statistika
    $fanontaniana_statistika = $mysqli->prepare("
        SELECT 
            COUNT(DISTINCT mpianatra) as isan_mpianatra,
            ROUND(AVG(naoty), 1) as salan_isa
        FROM naoty 
        WHERE kilasy = ? AND mpampianatra = ? AND taranja = ?
    ");
    
    $fanontaniana_statistika->bind_param("sss", $kilasy, $anarana_feno, $taranja_normalise);
    $fanontaniana_statistika->execute();
    $valiny_statistika = $fanontaniana_statistika->get_result();
    $statistika = $valiny_statistika->fetch_assoc();
    
    echo json_encode([
        'fahombiazana' => true,
        'naoty' => $naoty,
        'statistika' => $statistika
    ]);
    
} catch (Exception $e) {
    echo json_encode(['fahombiazana' => false, 'hadisoana' => $e->getMessage()]);
} finally {
    $mysqli->close();
}
?>