<?php 
session_start();

// ✅ FANARAHA-MASO NY FIDIRANA - Ny mpianatra sy mpampianatra ihany no afaka miditra
require_once 'check_access.php';
checkAccess(['G_Tous_Eleves', 'G_Tous_Professeurs']);

if (!isset($_SESSION['username'])) {
    header("Location: fidirana.php");
    exit;
}

$anarana_mpampiasa = $_SESSION['username'];
$anarana_feno = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : $anarana_mpampiasa;
$vondrona_mpampiasa = isset($_SESSION['groups']) ? $_SESSION['groups'] : [];

// Fifandraisana MySQL
$mysqli = new mysqli("localhost", "root", "Basique12345", "educonnect");
$mysqli->set_charset("utf8mb4");
if ($mysqli->connect_error) {
    die("Hadisoana MySQL : " . $mysqli->connect_error);
}

// Famaritana raha mpampianatra
$mpampianatra_ve = false;
$vondrona_taranja = ["G_Mathematique", "G_Francais", "G_Histoire", "G_Physique"];
foreach ($vondrona_taranja as $vondrona) {
    if (in_array($vondrona, $vondrona_mpampiasa)) {
        $mpampianatra_ve = true;
        break;
    }
}

// Raha mpampianatra → maka ny fampianarana rehetra amin'ny kilasy rehetra
if ($mpampianatra_ve) {
    $fangatahana_fandaharam_potoana = $mysqli->prepare("SELECT * FROM fandaharam_potoana WHERE mpampianatra = ? ORDER BY FIELD(andro, 'Alatsinainy','Talata','Alarobia','Alakamisy','Zoma','Sabotsy','Alahady'), ora_fanombohana");
    $fangatahana_fandaharam_potoana->bind_param("s", $anarana_feno);
    $kilasy = "Kilasy rehetra";
} else {
    // Mpianatra → famaritana ny kilasy avy amin'ny vondrona
    $kilasy = '';
    if (in_array('G_L1G1', $vondrona_mpampiasa)) $kilasy = 'L1G1';
    elseif (in_array('G_L1G2', $vondrona_mpampiasa)) $kilasy = 'L1G2';
    elseif (in_array('G_L2G1', $vondrona_mpampiasa)) $kilasy = 'L2G1';
    elseif (in_array('G_L2G2', $vondrona_mpampiasa)) $kilasy = 'L2G2';

    $fangatahana_fandaharam_potoana = $mysqli->prepare("SELECT * FROM fandaharam_potoana WHERE kilasy = ? ORDER BY FIELD(andro, 'Alatsinainy','Talata','Alarobia','Alakamisy','Zoma','Sabotsy','Alahady'), ora_fanombohana");
    $fangatahana_fandaharam_potoana->bind_param("s", $kilasy);
}

$fangatahana_fandaharam_potoana->execute();
$valiny_fandaharam_potoana = $fangatahana_fandaharam_potoana->get_result();

// Mandamina ny fampianarana araka ny andro
$fandaharam_potoana = [];
$andro_filaharana = ['Alatsinainy', 'Talata', 'Alarobia', 'Alakamisy', 'Zoma', 'Sabotsy', 'Alahady'];

while ($fampianarana = $valiny_fandaharam_potoana->fetch_assoc()) {
    $andro = $fampianarana['andro'];
    if (!isset($fandaharam_potoana[$andro])) {
        $fandaharam_potoana[$andro] = [];
    }
    $fandaharam_potoana[$andro][] = $fampianarana;
}

// Andro ankehitriny ho an'ny fanamarihana
$andro_malagasy = [
    'Monday' => 'Alatsinainy',
    'Tuesday' => 'Talata', 
    'Wednesday' => 'Alarobia',
    'Thursday' => 'Alakamisy',
    'Friday' => 'Zoma',
    'Saturday' => 'Sabotsy',
    'Sunday' => 'Alahady'
];
$andro_ankehitriny = $andro_malagasy[date('l')];
?>

<!DOCTYPE html>
<html lang="mg">
<head>
    <meta charset="UTF-8">
    <title>Fandaharam-potoana feno - <?php echo htmlspecialchars($anarana_feno); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="fandaharam_potoana_feno.css">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<div class="container">
    <header class="agenda-header">
        <div class="header-navigation">
        <a href="javascript:history.back()" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Hiverina amin'ny profil
        </a>

        </div>
        <div class="header-title">
            <h1><i class="fas fa-calendar-week"></i> Fandaharam-potoana feno</h1>
            <p><?php echo ($mpampianatra_ve ? "Ny fampianarana rehetra" : "Kilasy " . htmlspecialchars($kilasy)); ?> - Herinandron'ny <?php echo date('d/m/Y', strtotime('monday this week')); ?></p>

        </div>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="atontao_fandaharam_potoana()">
                <i class="fas fa-print"></i> Atontao
            </button>
        </div>
    </header>

    <div class="agenda-container">
        <?php if (!empty($fandaharam_potoana)): ?>
            <div class="week-view">
                <?php foreach ($andro_filaharana as $andro): ?>
                    <?php if (isset($fandaharam_potoana[$andro])): ?>
                        <div class="day-column <?php echo ($andro === $andro_ankehitriny) ? 'current-day' : ''; ?>">
                            <div class="day-header">
                                <h3><?php echo $andro; ?></h3>
                                <div class="day-date">
                                    <?php 
                                    $laharana_andro = array_search($andro, $andro_filaharana);
                                    $daty = date('d/m', strtotime('monday this week +' . $laharana_andro . ' days'));
                                    echo $daty;
                                    ?>
                                </div>
                                <?php if ($andro === $andro_ankehitriny): ?>
                                    <div class="today-indicator">
                                        <i class="fas fa-circle"></i> Anio
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="day-courses">
                                <?php 
                                $ora_ankehitriny = date('H:i');
                                foreach ($fandaharam_potoana[$andro] as $fampianarana): 
                                    $mandeha_ankehitriny = ($andro === $andro_ankehitriny && $ora_ankehitriny >= $fampianarana['ora_fanombohana'] && $ora_ankehitriny <= $fampianarana['ora_famaranana']);
                                    $ho_avy = ($andro === $andro_ankehitriny && $ora_ankehitriny < $fampianarana['ora_fanombohana']);
                                    $lasa = ($andro === $andro_ankehitriny && $ora_ankehitriny > $fampianarana['ora_famaranana']);
                                    
                                    $kilasy_status = '';
                                    if ($mandeha_ankehitriny) {
                                        $kilasy_status = 'current';
                                    } elseif ($ho_avy) {
                                        $kilasy_status = 'upcoming';
                                    } elseif ($lasa) {
                                        $kilasy_status = 'past';
                                    }
                                ?>
                                    <div class="agenda-course-item <?php echo $kilasy_status; ?>" data-subject="<?php echo htmlspecialchars($fampianarana['taranja']); ?>">
                                        <div class="course-time-slot">
                                            <?php echo htmlspecialchars($fampianarana['ora_fanombohana']); ?>
                                            <span class="time-separator">-</span>
                                            <?php echo htmlspecialchars($fampianarana['ora_famaranana']); ?>
                                        </div>
                                        <div class="course-content">
                                            <div class="course-subject-name">
                                                <?php echo htmlspecialchars($fampianarana['taranja']); ?>
                                            </div>
                                            <div class="course-details-mini">
                                                <span class="course-room-mini">
                                                    <i class="fas fa-door-open"></i>
                                                    <?php echo htmlspecialchars($fampianarana['efitrano']); ?>
                                                </span>
                                                <?php if ($mpampianatra_ve): ?>
                                                    <span class="course-teacher-mini">
                                                        <i class="fas fa-users"></i>
                                                        Kilasy <?php echo htmlspecialchars($fampianarana['kilasy']); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="course-teacher-mini">
                                                        <i class="fas fa-user"></i>
                                                        <?php echo htmlspecialchars($fampianarana['mpampianatra']); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php if ($mandeha_ankehitriny): ?>
                                            <div class="live-indicator">
                                                <i class="fas fa-circle"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="day-column empty-day">
                            <div class="day-header">
                                <h3><?php echo $andro; ?></h3>
                                <div class="day-date">
                                    <?php 
                                    $laharana_andro = array_search($andro, $andro_filaharana);
                                    $daty = date('d/m', strtotime('monday this week +' . $laharana_andro . ' days'));
                                    echo $daty;
                                    ?>
                                </div>
                            </div>
                            <div class="day-courses">
                                <div class="no-courses">
                                    <i class="fas fa-calendar-times"></i>
                                    <span>Tsy misy fampianarana</span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-schedule-full">
                <div class="empty-schedule-icon">
                    <i class="fas fa-calendar-times"></i>
                </div>
                <h2>Tsy misy fampianarana voalahatra</h2>
                <p>Mbola tsy voafaritra ny fandaharam-potoana ho an'ny kilasinareo.</p>
                <a href="mpianatra.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Hiverina amin'ny profil
                </a>
            </div>
        <?php endif; ?>
    </div>

    <div class="schedule-summary">
        <div class="summary-card">
            <div class="summary-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="summary-info">
                <h4>Ora fampianarana amin'ity herinandro ity</h4>
                <div class="summary-value">
                    <?php 
                    $ora_totaliny = 0;
                    foreach ($fandaharam_potoana as $andro => $fampianarana_andro) {
                        foreach ($fampianarana_andro as $fampianarana) {
                            $fanombohana = new DateTime($fampianarana['ora_fanombohana']);
                            $famaranana = new DateTime($fampianarana['ora_famaranana']);
                            $faharetana = $famaranana->diff($fanombohana);
                            $ora_totaliny += $faharetana->h + ($faharetana->i / 60);
                        }
                    }
                    echo number_format($ora_totaliny, 1) . 'ora';
                    ?>
                </div>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon">
                <i class="fas fa-book"></i>
            </div>
            <div class="summary-info">
                <h4>Taranja samihafa</h4>
                <div class="summary-value">
                    <?php 
                    $taranja = [];
                    foreach ($fandaharam_potoana as $andro => $fampianarana_andro) {
                        foreach ($fampianarana_andro as $fampianarana) {
                            $taranja[$fampianarana['taranja']] = true;
                        }
                    }
                    echo count($taranja);
                    ?>
                </div>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div class="summary-info">
                <h4>Mpampianatra samihafa</h4>
                <div class="summary-value">
                    <?php 
                    $mpampianatra = [];
                    foreach ($fandaharam_potoana as $andro => $fampianarana_andro) {
                        foreach ($fampianarana_andro as $fampianarana) {
                            $mpampianatra[$fampianarana['mpampianatra']] = true;
                        }
                    }
                    echo count($mpampianatra);
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="fandaharam_potoana_script.js"></script>
<script>
function atontao_fandaharam_potoana() {
    window.print();
}
</script>
</body>
</html>