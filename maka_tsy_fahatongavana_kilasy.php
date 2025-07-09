<?php
session_start();

// Manamarina raha tafiditra ny mpampiasa
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['fahombiazana' => false, 'hafatra' => 'Tsy nahazo alalana']);
    exit;
}

// Manamarina fa nomena ny kilasy
if (!isset($_POST['kilasy'])) {
    echo json_encode(['fahombiazana' => false, 'hafatra' => 'Tsy voalaza ny kilasy']);
    exit;
}

$kilasy = $_POST['kilasy'];

// Fifandraisana amin'ny MySQL
$mysqli = new mysqli("localhost", "root", "Basique12345", "educonnect");
if ($mysqli->connect_error) {
    echo json_encode(['fahombiazana' => false, 'hafatra' => 'Hadisoana fifandraisana amin\'ny angon-drakitra']);
    exit;
}

try {
    // Maka ny antontan'isa an'ny kilasy
    $stmt = $mysqli->prepare("
        SELECT 
            COUNT(DISTINCT mpianatra) as isan_mpianatra,
            SUM(CASE WHEN karazana = 'tsy_fahatongavana' THEN 1 ELSE 0 END) as isan_tsy_fahatongavana,
            SUM(CASE WHEN karazana = 'fahatara' THEN 1 ELSE 0 END) as isan_fahatara
        FROM tsy_fahatongavana_fahatara 
        WHERE kilasy = ?
    ");
    $stmt->bind_param("s", $kilasy);
    $stmt->execute();
    $stats_result = $stmt->get_result();
    $antontan_isa = $stats_result->fetch_assoc();
    $stmt->close();
    
    // Maka ny mpianatra rehetra miaraka amin'ny tsy fahatongavana sy fahatara
    $stmt = $mysqli->prepare("
        SELECT 
            mpianatra,
            COUNT(*) as totaliny,
            SUM(CASE WHEN karazana = 'tsy_fahatongavana' THEN 1 ELSE 0 END) as isan_tsy_fahatongavana,
            SUM(CASE WHEN karazana = 'fahatara' THEN 1 ELSE 0 END) as isan_fahatara
        FROM tsy_fahatongavana_fahatara 
        WHERE kilasy = ? 
        GROUP BY mpianatra
        ORDER BY mpianatra
    ");
    $stmt->bind_param("s", $kilasy);
    $stmt->execute();
    $students_result = $stmt->get_result();
    
    $mpianatra = [];
    while ($student = $students_result->fetch_assoc()) {
        // Maka ny antsipiriany momba ny tsy fahatongavana/fahatara ho an'ity mpianatra ity
        $stmt2 = $mysqli->prepare("
            SELECT id, daty, ora, karazana, antony 
            FROM tsy_fahatongavana_fahatara 
            WHERE kilasy = ? AND mpianatra = ? 
            ORDER BY daty DESC, ora DESC
        ");
        $stmt2->bind_param("ss", $kilasy, $student['mpianatra']);
        $stmt2->execute();
        $absences_result = $stmt2->get_result();
        
        $tsy_fahatongavana = [];
        while ($absence = $absences_result->fetch_assoc()) {
            $tsy_fahatongavana[] = $absence;
        }
        $stmt2->close();
        
        $student['tsy_fahatongavana'] = $tsy_fahatongavana;
        $mpianatra[] = $student;
    }
    
    $stmt->close();
    $mysqli->close();
    
    echo json_encode([
        'fahombiazana' => true,
        'mpianatra' => $mpianatra,
        'antontan_isa' => $antontan_isa
    ]);
    
} catch (Exception $e) {
    echo json_encode(['fahombiazana' => false, 'hafatra' => 'Hadisoana: ' . $e->getMessage()]);
}
?>