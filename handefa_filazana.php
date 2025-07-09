<?php
session_start();

// Manamarina raha tafiditra ny mpampiasa
if (!isset($_SESSION['username'])) {
    header("Location: fidirana.php");
    exit();
}

// Manamarina ny fomba POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['hafatra'] = "❌ Fomba tsy ekena.";
    header("Location: mpizara.php");
    exit();
}

// Fakana ny angon-drakitra avy amin'ny formulaire
$lohateny = trim($_POST['lohateny'] ?? '');
$votoaty = trim($_POST['votoaty'] ?? '');

// Fanamarinana ny angon-drakitra
if (empty($lohateny) || empty($votoaty)) {
    $_SESSION['hafatra'] = "❌ Ny lohateny sy ny votoaty dia tsy maintsy fenoina.";
    header("Location: mpizara.php");
    exit();
}

// Fitantanana ny rakitra ampidirina
$anarana_rakitra = null;
if (isset($_FILES['rakitra']) && $_FILES['rakitra']['error'] === UPLOAD_ERR_OK) {
    $lahatahiry_fampidirana = 'uploads/';
    
    // Mamorona ny lahatahiry raha tsy misy
    if (!is_dir($lahatahiry_fampidirana)) {
        mkdir($lahatahiry_fampidirana, 0755, true);
    }
    
    $rakitra_vonjimaika = $_FILES['rakitra']['tmp_name'];
    $anarana_rakitra = time() . '_' . basename($_FILES['rakitra']['name']);
    $lalana_rakitra = $lahatahiry_fampidirana . $anarana_rakitra;
    
    // Fanamarinana ny fiarovana
    $karazana_ekena = ['pdf', 'doc', 'docx', 'jpg', 'png', 'txt'];
    $karazana = strtolower(pathinfo($anarana_rakitra, PATHINFO_EXTENSION));
    
    if (!in_array($karazana, $karazana_ekena)) {
        $_SESSION['hafatra'] = "❌ Karazana rakitra tsy ekena.";
        header("Location: mpizara.php");
        exit();
    }
    
    if ($_FILES['rakitra']['size'] > 10 * 1024 * 1024) { // 10MB max
        $_SESSION['hafatra'] = "❌ Lehibe loatra ny rakitra (max 10MB).";
        header("Location: mpizara.php");
        exit();
    }
    
    if (!move_uploaded_file($rakitra_vonjimaika, $lalana_rakitra)) {
        $_SESSION['hafatra'] = "❌ Hadisoana tamin'ny fampidirana rakitra.";
        header("Location: mpizara.php");
        exit();
    }
}

// Fifandraisana amin'ny angon-drakitra
$mysqli = new mysqli("localhost", "root", "Basique12345", "educonnect");
if ($mysqli->connect_error) {
    $_SESSION['hafatra'] = "❌ Hadisoana fifandraisana amin'ny angon-drakitra.";
    header("Location: mpizara.php");
    exit();
}

try {
    // Fampidirana ny filazana
    $stmt = $mysqli->prepare("INSERT INTO filazana (lohateny, votoaty, rakitra, daty_fandefasana, mpandefa) VALUES (?, ?, ?, NOW(), ?)");
    $mpandefa = $_SESSION['username'];
    $stmt->bind_param("ssss", $lohateny, $votoaty, $anarana_rakitra, $mpandefa);
    
    if ($stmt->execute()) {
        $filazana_id = $mysqli->insert_id;
        
        // Fampandrenesana fahombiazana
        $_SESSION['hafatra'] = "✅ Nalefa tamim-pahombiazana ny filazana!";
        
        // Log ho an'ny debug
        error_log("Filazana noforonina ID: $filazana_id, Mpandefa: $mpandefa");
        
    } else {
        throw new Exception("Hadisoana tamin'ny fampidirana ao amin'ny angon-drakitra");
    }
    
} catch (Exception $e) {
    error_log("Hadisoana handefa_filazana: " . $e->getMessage());
    $_SESSION['hafatra'] = "❌ Hadisoana tamin'ny fandefasana ny filazana.";
    
    // Fafana ny rakitra raha misy hadisoana
    if ($anarana_rakitra && file_exists($lahatahiry_fampidirana . $anarana_rakitra)) {
        unlink($lahatahiry_fampidirana . $anarana_rakitra);
    }
} finally {
    $mysqli->close();
}

header("Location: mpizara.php");
exit();
?>