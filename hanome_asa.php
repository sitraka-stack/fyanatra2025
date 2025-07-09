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

if (!isset($_POST['asa_id']) || !isset($_FILES['rakitra_nomena'])) {
    $msg = "❌ Tsy ampy ny angon-drakitra hanome ny asa.";
    if ($is_ajax) {
        echo $msg;
        exit;
    } else {
        $_SESSION['message'] = $msg;
        header("Location: mpianatra.php");
        exit;
    }
}

$anarana_mpampiasa = $_SESSION['username'];
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
    $msg = "❌ Tsy hita ny asa.";
    if ($is_ajax) {
        echo $msg;
    } else {
        $_SESSION['message'] = $msg;
        header("Location: mpianatra.php");
    }
    exit;
}

$stmt = $mysqli->prepare("SELECT id FROM rendus_asa WHERE asa_id = ? AND mpianatra_anarana = ?");
$stmt->bind_param("is", $asa_id, $anarana_mpampiasa);
$stmt->execute();
$result = $stmt->get_result();
$efa_nomena = $result->fetch_assoc();
$stmt->close();

if ($efa_nomena) {
    $msg = "❌ Efa nomena io asa io.";
    if ($is_ajax) {
        echo $msg;
    } else {
        $_SESSION['message'] = $msg;
        header("Location: mpianatra.php");
    }
    exit;
}

$rakitra_anarana = null;
if (isset($_FILES['rakitra_nomena']) && $_FILES['rakitra_nomena']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = "rendus_mpianatra/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if ($_FILES['rakitra_nomena']['size'] > 10 * 1024 * 1024) {
        echo "❌ Ny rakitra dia be loatra (10MB ambony indrindra).";
        exit;
    }

    $allowed_extensions = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'png', 'zip'];
    $file_extension = strtolower(pathinfo($_FILES['rakitra_nomena']['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_extensions)) {
        echo "❌ Karazana rakitra tsy ekena.";
        exit;
    }

    $rakitra_anarana = $anarana_mpampiasa . "_asa" . $asa_id . "_" . uniqid() . "." . $file_extension;
    $lalana_feno = $upload_dir . $rakitra_anarana;

    if (!move_uploaded_file($_FILES['rakitra_nomena']['tmp_name'], $lalana_feno)) {
        echo "❌ Hadisoana teo am-pikorontanana ny rakitra.";
        exit;
    }
} else {
    echo "❌ Tsy misy rakitra voasafidy na hadisoana teo am-pikorontanana.";
    exit;
}

$stmt = $mysqli->prepare("INSERT INTO rendus_asa (asa_id, mpianatra_anarana, rakitra_nomena) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $asa_id, $anarana_mpampiasa, $rakitra_anarana);

if ($stmt->execute()) {
    $tara = new DateTime() > new DateTime($asa['daty_farany']);
    $msg = $tara ?
        "⚠️ Asa nomena tara nefa nahomby." :
        "✅ Asa nomena nahomby.";
    if ($is_ajax) {
        echo $msg;
    } else {
        $_SESSION['message'] = $msg;
        $redirect_page = isset($_POST['redirect']) ? $_POST['redirect'] : 'mpianatra.php';
        header("Location: " . $redirect_page);
    }
} else {
    if (file_exists($lalana_feno)) {
        unlink($lalana_feno);
    }
    echo "❌ Hadisoana teo am-pirakitana ny fanomezana.";
}

$stmt->close();
$mysqli->close();
exit;
?>