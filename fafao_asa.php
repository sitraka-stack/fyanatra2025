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

// Fanamarinana ny ID asa
if (!isset($_POST['id_asa']) || empty($_POST['id_asa'])) {
    echo json_encode(['fahombiazana' => false, 'hadisoana' => 'ID asa tsy voafaritra']);
    exit;
}

$id_asa = $_POST['id_asa'];
$anarana_feno = $_SESSION['fullname'];

// Fifandraisana MySQL
$mysqli = new mysqli("localhost", "root", "Basique12345", "educonnect");
$mysqli->set_charset("utf8mb4");

if ($mysqli->connect_error) {
    echo json_encode(['fahombiazana' => false, 'hadisoana' => 'Fahadisoana fifandraisana MySQL']);
    exit;
}

try {
    // Fanamarinana raha ny mpampianatra no tompony ny asa
    $fanontaniana_fanamarinana = $mysqli->prepare("
        SELECT id, rakitra FROM asa 
        WHERE id = ? AND mpampianatra = ?
    ");
    
    $fanontaniana_fanamarinana->bind_param("is", $id_asa, $anarana_feno);
    $fanontaniana_fanamarinana->execute();
    $valiny_fanamarinana = $fanontaniana_fanamarinana->get_result();
    
    if ($valiny_fanamarinana->num_rows === 0) {
        echo json_encode(['fahombiazana' => false, 'hadisoana' => 'Asa tsy hita na tsy azonao atao ny mamafa azy']);
        exit;
    }
    
    $asa_data = $valiny_fanamarinana->fetch_assoc();
    
    // Fafana ny rakitra raha misy
    if (!empty($asa_data['rakitra'])) {
        $lalana_rakitra = "rakitra_asa/" . $asa_data['rakitra'];
        if (file_exists($lalana_rakitra)) {
            unlink($lalana_rakitra);
        }
    }
    
    // Fafana ny asa ao amin'ny database
    $fanontaniana_fafana = $mysqli->prepare("DELETE FROM asa WHERE id = ?");
    $fanontaniana_fafana->bind_param("i", $id_asa);
    
    if ($fanontaniana_fafana->execute()) {
        echo json_encode(['fahombiazana' => true, 'hafatra' => 'Voafafa soa aman-tsara ny asa']);
    } else {
        echo json_encode(['fahombiazana' => false, 'hadisoana' => 'Fahadisoana tamin\'ny fafana ny asa']);
    }
    
} catch (Exception $e) {
    echo json_encode(['fahombiazana' => false, 'hadisoana' => $e->getMessage()]);
} finally {
    $mysqli->close();
}
?>