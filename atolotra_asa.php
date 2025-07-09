<?php
session_start();

$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (!isset($_SESSION['username'])) {
    if ($is_ajax) {
        echo "❌ Session tsy mety.";
        exit;
    } else {
        header("Location: fidirana.php");
        exit;
    }
}

if (!isset($_POST['asa_id']) || !isset($_FILES['rakitra_natolotra'])) {
    $msg = "❌ Angon-drakitra tsy feno ho an'ny fanatolotrana asa.";
    if ($is_ajax) {
        echo $msg;
        exit;
    } else {
        $_SESSION['hafatra'] = $msg;
        header("Location: mpianatra.php");
        exit;
    }
}

$username = $_SESSION['username'];
$asa_id = $_POST['asa_id'];

$mysqli = new mysqli("localhost", "root", "Basique12345", "educonnect");
if ($mysqli->connect_error) {
    echo "❌ Hadisoana fifandraisana MySQL: " . $mysqli->connect_error;
    exit;
}

$stmt = $mysqli->prepare("SELECT lohateny, daty_farany FROM asa WHERE id = ?");
$stmt->bind_param("i", $asa_id);
$stmt->execute();
$result = $stmt->get_result();
$asa = $result->fetch_assoc();
$stmt->close();

if (!$asa) {
    $msg = "❌ Asa tsy hita.";
    if ($is_ajax) {
        echo $msg;
    } else {
        $_SESSION['hafatra'] = $msg;
        header("Location: mpianatra.php");
    }
    exit;
}

$stmt = $mysqli->prepare("SELECT id FROM rendus_asa WHERE asa_id = ? AND mpianatra = ?");
$stmt->bind_param("is", $asa_id, $username);
$stmt->execute();
$result = $stmt->get_result();
$efa_natolotra = $result->fetch_assoc();
$stmt->close();

if ($efa_natolotra) {
    $msg = "❌ Efa natolotra sahady ity asa ity.";
    if ($is_ajax) {
        echo $msg;
    } else {
        $_SESSION['hafatra'] = $msg;
        header("Location: mpianatra.php");
    }
    exit;
}

$anarana_rakitra = null;
if (isset($_FILES['rakitra_natolotra']) && $_FILES['rakitra_natolotra']['error'] === UPLOAD_ERR_OK) {
    $lahatahiry_fampidirana = "rendus_mpianatra/";
    if (!is_dir($lahatahiry_fampidirana)) {
        mkdir($lahatahiry_fampidirana, 0777, true);
    }

    if ($_FILES['rakitra_natolotra']['size'] > 10 * 1024 * 1024) {
        echo "❌ Ny rakitra dia lehibe loatra (max 10MB).";
        exit;
    }

    $extensions_azo = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'png', 'zip'];
    $extension_rakitra = strtolower(pathinfo($_FILES['rakitra_natolotra']['name'], PATHINFO_EXTENSION));
    if (!in_array($extension_rakitra, $extensions_azo)) {
        echo "❌ Karazana rakitra tsy azo.";
        exit;
    }

    $anarana_rakitra = $username . "_asa" . $asa_id . "_" . uniqid() . "." . $extension_rakitra;
    $lalana_feno = $lahatahiry_fampidirana . $anarana_rakitra;

    if (!move_uploaded_file($_FILES['rakitra_natolotra']['tmp_name'], $lalana_feno)) {
        echo "❌ Hadisoana tamin'ny fampidirana rakitra.";
        exit;
    }
} else {
    echo "❌ Tsy misy rakitra voasafidy na misy hadisoana tamin'ny fampidirana.";
    exit;
}

$stmt = $mysqli->prepare("INSERT INTO rendus_asa (asa_id, mpianatra, rakitra_natolotra) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $asa_id, $username, $anarana_rakitra);

if ($stmt->execute()) {
    $tara = new DateTime() > new DateTime($asa['daty_farany']);
    $msg = $tara ?
        "⚠️ Asa natolotra tara nefa nahomby." :
        "✅ Asa natolotra tamina fahombiazana.";
    if ($is_ajax) {
        echo $msg;
    } else {
        $_SESSION['hafatra'] = $msg;
        $pejy_fiverenana = isset($_POST['fiverenana']) ? $_POST['fiverenana'] : 'mpianatra.php';
        header("Location: " . $pejy_fiverenana);
    }
} else {
    if (file_exists($lalana_feno)) {
        unlink($lalana_feno);
    }
    echo "❌ Hadisoana tamin'ny fitahirizana fanatolotrana.";
}

$stmt->close();
$mysqli->close();
exit;
?>