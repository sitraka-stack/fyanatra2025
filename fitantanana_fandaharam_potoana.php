<?php
session_start();

// ✅ FANARAHA-MASO NY FIDIRANA - Ny mpizara ihany no afaka miditra
require_once 'check_access.php';
checkAccess(['G_Tous_Personnel_Admin']);

// Fifandraisana MySQL
$mysqli = new mysqli("localhost", "root", "Basique12345", "educonnect");
$mysqli->set_charset("utf8mb4");
if ($mysqli->connect_error) {
    die("Hadisoana fifandraisana : " . $mysqli->connect_error);
}

// Kilasy nalaina tamin'ny GET
$kilasy = $_GET['kilasy'] ?? '';
if (empty($kilasy)) {
    header("Location: mpizara.php");
    exit;
}

// Maka ny anarana feno an'ny mpampiasa tafiditra
$anarana_feno = $_SESSION['fullname'] ?? 'Anarana tsy voafaritra';
$anarana_mpampiasa = $_SESSION['username'] ?? 'mpizara';

// Fanapahana anarana sy fanampin'anarana
$ampahany = explode(" ", $anarana_feno);
$anarana_voalohany = $ampahany[0] ?? '';
$anarana_farany = $ampahany[1] ?? '';

// Lalana sary profil miaraka amin'ny avatar default
$lalana_sary = "sary/" . $anarana_mpampiasa . ".jpg";
if (!file_exists($lalana_sary)) {
    $litera_voalohany = strtoupper(substr($anarana_voalohany, 0, 1) . substr($anarana_farany, 0, 1));
    $lalana_sary = "data:image/svg+xml;base64," . base64_encode('
        <svg width="150" height="150" xmlns="http://www.w3.org/2000/svg">
            <circle cx="75" cy="75" r="75" fill="#9CA3AF"/>
            <text x="75" y="85" font-family="Arial, sans-serif" font-size="48" font-weight="bold" text-anchor="middle" fill="white">' . $litera_voalohany . '</text>
        </svg>
    ');
}

// ================= Fifandraisana LDAP ho an'ny fakana taranja ================= //
$ldapconn = ldap_connect("ldap://192.168.40.132");
$mpampianatra = 'Mpampianatra tsy fantatra';
$taranja_misy = [];

if ($ldapconn) {
    ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
    $ldapbind = ldap_bind($ldapconn, "EDUCONNECT\\AndrypL1G2", "Test@1234");

    if ($ldapbind) {
        // Maka ny vondrona G_* rehetra ho an'ny taranja
        $fikarohana = ldap_search($ldapconn, "dc=educonnect,dc=mg", "(cn=G_*)", ["cn"]);
        $valiny = ldap_get_entries($ldapconn, $fikarohana);

        for ($i = 0; $i < $valiny["count"]; $i++) {
            $cn = $valiny[$i]["cn"][0];
            // Mitazona ny vondrona taranja tena izy ihany
            if (preg_match('/^G_([A-Za-z]+)$/', $cn, $matches)) {
                $taranja = ucfirst(strtolower($matches[1]));
                $taranja_misy[] = $taranja;
            }
        }

        // Maka mpampianatra mifandray raha voasafidy ny taranja
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $taranja_post = $_POST['taranja'] ?? '';
            if ($taranja_post) {
                $fikarohana_prof = ldap_search($ldapconn, "dc=educonnect,dc=mg", "(CN=G_" . ucfirst($taranja_post) . ")", ["member"]);
                $valiny_prof = ldap_get_entries($ldapconn, $fikarohana_prof);
                if ($valiny_prof["count"] > 0 && isset($valiny_prof[0]["member"][0])) {
                    $dn = $valiny_prof[0]["member"][0];
                    if (preg_match('/CN=([^,]+)/', $dn, $match)) {
                        $mpampianatra = $match[1];
                    }
                }
            }
        }
    }
    ldap_unbind($ldapconn);
}

$hafatra_fampahafantarana = '';
$karazana_fampahafantarana = '';

// Fandraketana fampianarana
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['andro'], $_POST['ora_fanombohana'], $_POST['ora_famaranana'], $_POST['taranja'], $_POST['efitrano'])) {
    
    $andro = $_POST['andro'];
    $ora_fanombohana = $_POST['ora_fanombohana'];
    $ora_famaranana = $_POST['ora_famaranana'];
    $taranja = $_POST['taranja'];
    $efitrano = $_POST['efitrano'];
    $fanamarihana = $_POST['fanamarihana'] ?? '';

    // Manamarina raha efa misy fampianarana amin'io fotoana io
    $fangatahana_famarinana = $mysqli->prepare("SELECT * FROM fandaharam_potoana WHERE kilasy = ? AND andro = ? AND ((ora_fanombohana BETWEEN ? AND ?) OR (ora_famaranana BETWEEN ? AND ?) OR (? BETWEEN ora_fanombohana AND ora_famaranana) OR (? BETWEEN ora_fanombohana AND ora_famaranana))");
    $fangatahana_famarinana->bind_param("ssssssss", $kilasy, $andro, $ora_fanombohana, $ora_famaranana, $ora_fanombohana, $ora_famaranana, $ora_fanombohana, $ora_famaranana);
    $fangatahana_famarinana->execute();
    $valiny_famarinana = $fangatahana_famarinana->get_result();

    if ($valiny_famarinana->num_rows > 0) {
        $hafatra_fampahafantarana = 'Efa misy fampianarana amin\'io fotoana io !';
        $karazana_fampahafantarana = 'error';
    } else {
        if ($ora_fanombohana >= $ora_famaranana) {
            $hafatra_fampahafantarana = 'Ny ora fanombohana dia tokony ho mialoha ny ora famaranana.';
            $karazana_fampahafantarana = 'error';
        } else {
            $stmt = $mysqli->prepare("INSERT INTO fandaharam_potoana (kilasy, andro, ora_fanombohana, ora_famaranana, taranja, mpampianatra, efitrano, fanamarihana, daty_famoronana, daty_fanavaozana) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->bind_param("ssssssss", $kilasy, $andro, $ora_fanombohana, $ora_famaranana, $taranja, $mpampianatra, $efitrano, $fanamarihana);
            $stmt->execute();
            $stmt->close();
            $hafatra_fampahafantarana = 'Fampianarana nampidirina tamim-pahombiazana !';
            $karazana_fampahafantarana = 'success';
        }
    }
}

// Fakana ny fampianarana efa misy
$valiny = $mysqli->prepare("SELECT * FROM fandaharam_potoana WHERE kilasy = ? ORDER BY FIELD(andro, 'Alatsinainy','Talata','Alarobia','Alakamisy','Zoma','Sabotsy','Alahady'), ora_fanombohana");
$valiny->bind_param("s", $kilasy);
$valiny->execute();
$fampianarana = $valiny->get_result();

// Famaritana ny andro ankehitriny amin'ny teny malagasy
$andro_malagasy = [
    'Monday' => 'Alatsinainy',
    'Tuesday' => 'Talata', 
    'Wednesday' => 'Alarobia',
    'Thursday' => 'Alakamisy',
    'Friday' => 'Zoma',
    'Saturday' => 'Sabotsy',
    'Sunday' => 'Alahady'
];
$andro_ankehitriny = $andro_malagasy[date('l')] ?? 'Alatsinainy';
?>

<!DOCTYPE html>
<html lang="mg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fitantanana fandaharam-potoana - <?php echo htmlspecialchars($kilasy); ?></title>
    <link rel="stylesheet" href="mpizara.css">
    <link rel="stylesheet" href="fitantanana_fandaharam_potoana.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<div class="container">
    <header class="profile-header">
        <div class="profile-info">
            <div class="avatar-container">
                <img src="<?php echo $lalana_sary; ?>" alt="Sarin'ny profil" class="avatar" id="sarinProfil">
                <div class="status-indicator"></div>
            </div>
            <div class="user-details">
                <h1 class="user-name">Fitantanana fandaharam-potoana</h1>
                <p class="username kilasy-azo-tsindrina" onclick="asehoy_tabilao_fampianarana()" style="cursor: pointer; text-decoration: underline;">
                    Kilasy <?php echo htmlspecialchars($kilasy); ?>
                </p>
                <div class="user-meta">
                    <span class="meta-item">
                        <i class="fas fa-calendar-alt"></i>
                        Fitantanana ny fotoana
                    </span>
                    <span class="meta-item">
                        <i class="fas fa-users"></i>
                        <?php echo htmlspecialchars($kilasy); ?>
                    </span>
                </div>
            </div>
        </div>
        <div class="header-actions">
            <a href="mpizara.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Hiverina any amin'ny mpizara
            </a>
        </div>
    </header>

    <?php if ($hafatra_fampahafantarana): ?>
        <div class="notification notification-<?php echo $karazana_fampahafantarana; ?>">
            <div class="notification-content">
                <i class="fas fa-<?php echo $karazana_fampahafantarana === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <span><?php echo htmlspecialchars($hafatra_fampahafantarana); ?></span>
            </div>
            <button class="notification-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    <?php endif; ?>

    <div class="main-content">
        <div class="content-left">
            <section class="karatra schedule-form-card">
                <div class="card-header">
                    <h3><i class="fas fa-plus-circle"></i> Hanampy fampianarana vaovao</h3>
                </div>
                <div class="card-body">
                    <form method="POST" class="schedule-form" id="fomba_fandaharam_potoana">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="andro"><i class="fas fa-calendar-day"></i> Andron'ny herinandro</label>
                                <select name="andro" id="andro" required class="form-select">
                                    <option value="">-- Safidio andro iray --</option>
                                    <option value="Alatsinainy">Alatsinainy</option>
                                    <option value="Talata">Talata</option>
                                    <option value="Alarobia">Alarobia</option>
                                    <option value="Alakamisy">Alakamisy</option>
                                    <option value="Zoma">Zoma</option>
                                    <option value="Sabotsy">Sabotsy</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="taranja"><i class="fas fa-book"></i> Taranja</label>
                                <select name="taranja" id="taranja" required class="form-select">
                                    <option value="">-- Safidio taranja iray --</option>
                                    <?php foreach ($taranja_misy as $t): ?>
                                        <option value="<?php echo htmlspecialchars($t); ?>">
                                            <?php echo htmlspecialchars($t); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="ora_fanombohana"><i class="fas fa-clock"></i> Ora fanombohana</label>
                                <input type="time" name="ora_fanombohana" id="ora_fanombohana" required class="form-input">
                            </div>
                            <div class="form-group">
                                <label for="ora_famaranana"><i class="fas fa-clock"></i> Ora famaranana</label>
                                <input type="time" name="ora_famaranana" id="ora_famaranana" required class="form-input">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="efitrano"><i class="fas fa-door-open"></i> Efitrano</label>
                                <input type="text" name="efitrano" id="efitrano" required class="form-input" placeholder="Ohatra: A101, B205...">
                            </div>
                            <div class="form-group">
                                <label for="mpampianatra_aseho"><i class="fas fa-user-tie"></i> Mpampianatra</label>
                                <input type="text" id="mpampianatra_aseho" class="form-input" value="<?php echo htmlspecialchars($mpampianatra); ?>" readonly>
                                <small class="form-text">Voatendry ho azy araka ny taranja</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="fanamarihana"><i class="fas fa-sticky-note"></i> Fanamarihana (tsy tsy maintsy)</label>
                            <textarea name="fanamarihana" id="fanamarihana" rows="3" class="form-input" placeholder="Fanamarihana fanampiny..."></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-add-course">
                                <i class="fas fa-plus"></i>
                                Ampidiro ny fampianarana
                            </button>
                            <button type="reset" class="btn btn-secondary" onclick="avereno_fomba()">
                                <i class="fas fa-undo"></i>
                                Avereno
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </div>

        <div class="content-right">
            <section class="karatra schedule-preview-card">
                <div class="card-header">
                    <h3><i class="fas fa-eye"></i> Fijerena ny andro - <?php echo $andro_ankehitriny; ?></h3>
                </div>
                <div class="card-body">
                    <div class="day-preview-today">
                        <?php
                        // Mandamina ny fampianarana araka ny andro
                        $fampianarana_isaky_andro = [];
                        $fampianarana->data_seek(0);
                        while ($andalana = $fampianarana->fetch_assoc()) {
                            $fampianarana_isaky_andro[$andalana['andro']][] = $andalana;
                        }
                        ?>
                        <div class="today-preview">
                            <div class="today-header">
                                <i class="fas fa-calendar-day"></i>
                                Anio - <?php echo $andro_ankehitriny; ?>
                                <span class="today-date"><?php echo date('d/m/Y'); ?></span>
                            </div>
                            <div class="today-courses">
                                <?php if (isset($fampianarana_isaky_andro[$andro_ankehitriny])): ?>
                                    <?php foreach ($fampianarana_isaky_andro[$andro_ankehitriny] as $fampianarana_item): ?>
                                        <div class="course-preview-today">
                                            <div class="course-time-today">
                                                <i class="fas fa-clock"></i>
                                                <?php echo substr($fampianarana_item['ora_fanombohana'], 0, 5) . ' - ' . substr($fampianarana_item['ora_famaranana'], 0, 5); ?>
                                            </div>
                                            <div class="course-details-today">
                                                <div class="course-subject-today"><?php echo htmlspecialchars($fampianarana_item['taranja']); ?></div>
                                                <div class="course-room-today">
                                                    <i class="fas fa-door-open"></i>
                                                    <?php echo htmlspecialchars($fampianarana_item['efitrano']); ?>
                                                </div>
                                                <div class="course-teacher-today">
                                                    <i class="fas fa-user-tie"></i>
                                                    <?php echo htmlspecialchars($fampianarana_item['mpampianatra']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="no-courses-today">
                                        <i class="fas fa-calendar-times"></i>
                                        <p>Tsy misy fampianarana anio</p>
                                        <p>Manaova andro fialan-tsasatra !</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <section class="karatra schedule-table-card" id="tabilao_fampianarana_karatra" style="display: none;">
        <div class="card-header">
            <h3><i class="fas fa-table"></i> Fampianarana voarakitra ho an'ny kilasy <?php echo htmlspecialchars($kilasy); ?></h3>
            <button class="btn btn-secondary btn-sm" onclick="asehoy_tabilao_fampianarana()">
                <i class="fas fa-times"></i>
                Afeno
            </button>
        </div>
        <div class="card-body">
            <?php if ($fampianarana->num_rows > 0): ?>
                <div class="schedule-table-container">
                    <table class="schedule-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-calendar-day"></i> Andro</th>
                                <th><i class="fas fa-clock"></i> Ora</th>
                                <th><i class="fas fa-book"></i> Taranja</th>
                                <th><i class="fas fa-door-open"></i> Efitrano</th>
                                <th><i class="fas fa-user-tie"></i> Mpampianatra</th>
                                <th><i class="fas fa-sticky-note"></i> Fanamarihana</th>
                                <th><i class="fas fa-cog"></i> Asa</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $fampianarana->data_seek(0);
                            while ($andalana = $fampianarana->fetch_assoc()): 
                            ?>
                                <tr class="schedule-row">
                                    <td class="day-cell">
                                        <span class="day-badge"><?php echo htmlspecialchars($andalana['andro']); ?></span>
                                    </td>
                                    <td class="time-cell">
                                        <div class="time-range">
                                            <span class="start-time"><?php echo substr($andalana['ora_fanombohana'], 0, 5); ?></span>
                                            <i class="fas fa-arrow-right"></i>
                                            <span class="end-time"><?php echo substr($andalana['ora_famaranana'], 0, 5); ?></span>
                                        </div>
                                    </td>
                                    <td class="subject-cell">
                                        <span class="subject-name"><?php echo htmlspecialchars($andalana['taranja']); ?></span>
                                    </td>
                                    <td class="room-cell">
                                        <span class="room-number"><?php echo htmlspecialchars($andalana['efitrano']); ?></span>
                                    </td>
                                    <td class="teacher-cell">
                                        <span class="teacher-name"><?php echo htmlspecialchars($andalana['mpampianatra']); ?></span>
                                    </td>
                                    <td class="notes-cell">
                                        <span class="notes-text"><?php echo htmlspecialchars($andalana['fanamarihana'] ?: 'Tsy misy'); ?></span>
                                    </td>
                                    <td class="actions-cell">
                                        <a href="fafao_fandaharam_potoana.php?id=<?php echo $andalana['id']; ?>&kilasy=<?php echo urlencode($kilasy); ?>" 
                                           class="btn btn-danger btn-sm delete-btn" 
                                           onclick="return confirm('Tena hofafana ve ity fampianarana ity ?')">
                                            <i class="fas fa-trash"></i>
                                            Fafao
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <p>Tsy misy fampianarana voarakitra ho an'ity kilasy ity</p>
                    <p>Manomboka ampio ny fampianarana voalohany etsy ambony</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<script src="fitantanana_fandaharam_potoana.js"></script>
</body>
</html>