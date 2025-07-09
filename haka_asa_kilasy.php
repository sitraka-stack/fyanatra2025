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
    // Makà ny asa rehetra ho an'ity kilasy ity
    $fanontaniana_asa = $mysqli->prepare("
        SELECT 
            id,
            lohateny,
            votoaty,
            kilasy,
            taranja,
            DATE_FORMAT(daty_farany, '%d/%m/%Y %H:%i') as daty_farany_voalamina,
            rakitra,
            DATE_FORMAT(daty_fampidirana, '%d/%m/%Y %H:%i') as daty_fampidirana_voalamina,
            daty_farany as daty_farany_raw
        FROM asa 
        WHERE kilasy = ? AND mpampianatra = ? AND taranja = ?
        ORDER BY daty_fampidirana DESC
    ");
    
    $fanontaniana_asa->bind_param("sss", $kilasy, $anarana_feno, $taranja_normalise);
    $fanontaniana_asa->execute();
    $valiny_asa = $fanontaniana_asa->get_result();
    
    $asa = [];
    while ($andalana = $valiny_asa->fetch_assoc()) {
        // Mamaritra raha tapitra ny asa
        $ankehitriny = new DateTime();
        $daty_farany = new DateTime($andalana['daty_farany_raw']);
        $andalana['tapitra'] = $daty_farany < $ankehitriny;
        
        $asa[] = $andalana;
    }
    
    // Makà ny statistika
    $fanontaniana_statistika = $mysqli->prepare("
        SELECT 
            COUNT(*) as isan_asa,
            SUM(CASE WHEN daty_farany >= NOW() THEN 1 ELSE 0 END) as asa_misokatra,
            SUM(CASE WHEN daty_farany < NOW() THEN 1 ELSE 0 END) as asa_tapitra
        FROM asa 
        WHERE kilasy = ? AND mpampianatra = ? AND taranja = ?
    ");
    
    $fanontaniana_statistika->bind_param("sss", $kilasy, $anarana_feno, $taranja_normalise);
    $fanontaniana_statistika->execute();
    $valiny_statistika = $fanontaniana_statistika->get_result();
    $statistika = $valiny_statistika->fetch_assoc();
    
    echo json_encode([
        'fahombiazana' => true,
        'asa' => $asa,
        'statistika' => $statistika
    ]);
    
} catch (Exception $e) {
    echo json_encode(['fahombiazana' => false, 'hadisoana' => $e->getMessage()]);
} finally {
    $mysqli->close();
}
?>