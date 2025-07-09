<?php 
session_start();

// ✅ FANARAHA-MASO NY FIDIRANA - Ny mpianatra ihany no afaka miditra
require_once 'check_access.php';
checkAccess(['G_Tous_Eleves']);

if (!isset($_SESSION['username'])) {
    header("Location: fidirana.php");
    exit;
}

$anarana_mpampiasa = $_SESSION['username'];
$anarana_feno = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : $anarana_mpampiasa;
$parts = explode(" ", $anarana_feno);
$anarana_voalohany = isset($parts[0]) ? $parts[0] : '';
$anarana_farany = isset($parts[1]) ? $parts[1] : '';

// Fikarohana ny sary miaraka amin'ny extensions mety
$extensions = ['jpg', 'png', 'gif'];
$sary_lalana = '';
foreach ($extensions as $ext) {
    if (file_exists("photos/$anarana_mpampiasa.$ext")) {
        $sary_lalana = "photos/$anarana_mpampiasa.$ext";
        break;
    }
}

// Raha tsy hita ny sary, mamorona avatar SVG miaraka amin'ny litera voalohany
if (!$sary_lalana) {
    $litera_voalohany = strtoupper(substr($anarana_voalohany, 0, 1) . substr($anarana_farany, 0, 1));
    $sary_lalana = "data:image/svg+xml;base64," . base64_encode('
        <svg width="150" height="150" xmlns="http://www.w3.org/2000/svg">
            <circle cx="75" cy="75" r="75" fill="#9CA3AF"/>
            <text x="75" y="85" font-family="Arial, sans-serif" font-size="48" font-weight="bold" text-anchor="middle" fill="white">' . $litera_voalohany . '</text>
        </svg>
    ');
}

$vondrona_mpampiasa = isset($_SESSION['groups']) ? $_SESSION['groups'] : [];

// Fifandraisana MySQL
$mysqli = new mysqli("localhost", "root", "Basique12345", "educonnect");
$mysqli->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;
if ($mysqli->connect_error) {
    die("Hadisoana MySQL : " . $mysqli->connect_error);
}

// Kilasy
$kilasy = '';
if (in_array('G_L1G1', $vondrona_mpampiasa)) $kilasy = 'L1G1';
elseif (in_array('G_L1G2', $vondrona_mpampiasa)) $kilasy = 'L1G2';
elseif (in_array('G_L2G1', $vondrona_mpampiasa)) $kilasy = 'L2G1';
elseif (in_array('G_L2G2', $vondrona_mpampiasa)) $kilasy = 'L2G2';

// Asa vao haingana (ny farany indrindra ihany) - CORRECTED TO USE entin_mody_miverina
$stmt = $mysqli->prepare("
    SELECT 
        a.id,
        a.lohateny, 
        a.votoaty, 
        a.daty_fampidirana,
        a.daty_farany,
        a.rakitra,
        e.rakitra_natolotra,
        e.daty_fanatolotrana,
        a.taranja,
        CASE WHEN e.rakitra_natolotra IS NOT NULL THEN 1 ELSE 0 END as nomena
    FROM asa a
    LEFT JOIN entin_mody_miverina e ON a.id = e.asa_id AND e.mpianatra = ?
    WHERE a.kilasy = ? 
    ORDER BY a.daty_fampidirana DESC 
    LIMIT 10
");

if ($stmt === false) {
    die("Hadisoana SQL: " . $mysqli->error);
}

$stmt->bind_param("ss", $anarana_mpampiasa, $kilasy);
$stmt->execute();
$result = $stmt->get_result();
$asa = [];
while ($row = $result->fetch_assoc()) {
    $asa[] = $row;
}

// Fanisana ny asa rehetra
$stmt_count = $mysqli->prepare("SELECT COUNT(*) as totaliny FROM asa WHERE kilasy = ?");
$stmt_count->bind_param("s", $kilasy);
$stmt_count->execute();
$result_count = $stmt_count->get_result();
$asa_totaliny = $result_count->fetch_assoc()['totaliny'];

// Naoty vao haingana
$stmt_naoty = $mysqli->prepare("SELECT naoty, taranja, daty_fampidirana FROM naoty WHERE mpianatra = ? ORDER BY daty_fampidirana DESC LIMIT 3");
$stmt_naoty->bind_param("s", $anarana_mpampiasa);
$stmt_naoty->execute();
$result_naoty = $stmt_naoty->get_result();
$naoty_farany = [];
while ($row_naoty = $result_naoty->fetch_assoc()) {
    $naoty_farany[] = $row_naoty;
}

// Naoty rehetra
$stmt_count = $mysqli->prepare("SELECT COUNT(*) as totaliny FROM naoty WHERE mpianatra = ?");
$stmt_count->bind_param("s", $anarana_mpampiasa);
$stmt_count->execute();
$result_count = $stmt_count->get_result();
$naoty_totaliny = $result_count->fetch_assoc()['totaliny'];

// Fanisana ny filazana
$stmt_filazana = $mysqli->prepare("SELECT COUNT(*) as totaliny FROM filazana");
$stmt_filazana->execute();
$result_filazana = $stmt_filazana->get_result();
$filazana_totaliny = $result_filazana->fetch_assoc()['totaliny'];

// Fanisana ny tsy fahatongavana sy fahatara
$stmt_tsy_fahatongavana = $mysqli->prepare("
    SELECT 
        COUNT(*) as totaliny,
        SUM(CASE WHEN karazana = 'tsy_fahatongavana' THEN 1 ELSE 0 END) as tsy_fahatongavana,
        SUM(CASE WHEN karazana = 'fahatara' THEN 1 ELSE 0 END) as fahatara
    FROM tsy_fahatongavana_fahatara 
    WHERE mpianatra = ?
");
$stmt_tsy_fahatongavana->bind_param("s", $anarana_mpampiasa);
$stmt_tsy_fahatongavana->execute();
$result_tsy_fahatongavana = $stmt_tsy_fahatongavana->get_result();
$tsy_fahatongavana_stats = $result_tsy_fahatongavana->fetch_assoc();

// Fandaharam-potoana anio
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

$fandaharam_potoana_anio = $mysqli->prepare("SELECT * FROM fandaharam_potoana WHERE kilasy = ? AND andro = ? ORDER BY ora_fanombohana");
$fandaharam_potoana_anio->bind_param("ss", $kilasy, $andro_ankehitriny);
$fandaharam_potoana_anio->execute();
$fandaharam_potoana_anio_result = $fandaharam_potoana_anio->get_result();

// Fanisana ny fampianarana rehetra amin'ny herinandro
$fandaharam_potoana_count = $mysqli->prepare("SELECT COUNT(*) as totaliny FROM fandaharam_potoana WHERE kilasy = ?");
$fandaharam_potoana_count->bind_param("s", $kilasy);
$fandaharam_potoana_count->execute();
$fandaharam_potoana_count_result = $fandaharam_potoana_count->get_result();
$fampianarana_totaliny = $fandaharam_potoana_count_result->fetch_assoc()['totaliny'];
?>

<!DOCTYPE html>
<html lang="mg">
<head>
  <meta charset="UTF-8">
  <title>Mombamomba ny Mpianatra - <?php echo htmlspecialchars($anarana_feno); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta name="theme-color" content="#3b82f6">
  
  <!-- Styles -->
  <link rel="stylesheet" href="ankapobe.css">
  <link rel="stylesheet" href="fitantanana_fandaharam_potoana.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="student-page">
<div class="container">
  <header class="profile-header">
    <div class="profile-info">
      <div class="avatar-container">
        <img src="<?php echo $sary_lalana . (str_starts_with($sary_lalana, 'data:image') ? '' : '?v=' . time()); ?>" alt="Sary" class="avatar" id="profileImage">
        <div class="status-indicator"></div>
      </div>
      <div class="user-details">
        <h1 class="user-name"><?php echo htmlspecialchars($anarana_voalohany . ' ' . $anarana_farany); ?></h1>
        <p class="username">@<?php echo htmlspecialchars($anarana_mpampiasa); ?></p>
        <div class="user-meta">
          <span class="meta-item"><i class="fas fa-user-graduate"></i> Mpianatra</span>
          <?php if ($kilasy): ?>
            <span class="meta-item"><i class="fas fa-users"></i> Kilasy <?php echo htmlspecialchars($kilasy); ?></span>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="header-actions">
      <a href="filazana.php" class="btn btn-primary">
        <i class="fas fa-bullhorn"></i> 
        <span class="btn-text">Filazana</span>
        <?php if ($filazana_totaliny > 0): ?>
          <span class="notification-badge" id="announcementsBadge"><?php echo $filazana_totaliny; ?></span>
        <?php endif; ?>
      </a>
      <a href="tsy_fahatongavana_fahatara.php" class="btn btn-secondary">
        <i class="fas fa-user-clock"></i>
        <span class="btn-text">Tsy fahatongavana/Fahatara</span>
        <?php if ($tsy_fahatongavana_stats['totaliny'] > 0): ?>
          <span class="notification-badge"><?php echo $tsy_fahatongavana_stats['totaliny']; ?></span>
        <?php endif; ?>
      </a>
      <a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Hivoaka</a>
    </div>
  </header>

  <div class="main-content grid grid-2">
    <div class="content-left">
      <section class="karatra schedule-card">
        <div class="card-header">
          <h3 class="lohateny-kely"><i class="fas fa-calendar-day"></i> Fandaharam-potoana - <?php echo $andro_ankehitriny; ?></h3>
          <div class="schedule-date">
            <i class="fas fa-calendar-alt"></i>
            <?php echo date('d/m/Y'); ?>
          </div>
        </div>
        <div class="card-body">
          <?php if ($fandaharam_potoana_anio_result->num_rows > 0): ?>
            <div class="today-schedule">
              <?php 
              $ora_ankehitriny = date('H:i');
              while ($fampianarana = $fandaharam_potoana_anio_result->fetch_assoc()): 
                $mandeha_ankehitriny = ($ora_ankehitriny >= $fampianarana['ora_fanombohana'] && $ora_ankehitriny <= $fampianarana['ora_famaranana']);
                $ho_avy = ($ora_ankehitriny < $fampianarana['ora_fanombohana']);
                $lasa = ($ora_ankehitriny > $fampianarana['ora_famaranana']);
                
                $status_class = '';
                $status_text = '';
                $status_icon = '';
                
                if ($mandeha_ankehitriny) {
                  $status_class = 'current';
                  $status_text = 'Mandeha ankehitriny';
                  $status_icon = 'fas fa-play-circle';
                } elseif ($ho_avy) {
                  $status_class = 'upcoming';
                  $status_text = 'Ho avy';
                  $status_icon = 'fas fa-clock';
                } else {
                  $status_class = 'past';
                  $status_text = 'Vita';
                  $status_icon = 'fas fa-check-circle';
                }
              ?>
                <div class="course-item <?php echo $status_class; ?>" data-subject="<?php echo htmlspecialchars($fampianarana['taranja']); ?>">
                  <div class="course-time">
                    <div class="time-range">
                      <i class="fas fa-clock"></i>
                      <?php echo htmlspecialchars($fampianarana['ora_fanombohana'] . ' - ' . $fampianarana['ora_famaranana']); ?>
                    </div>
                    <div class="course-status <?php echo $status_class; ?>">
                      <i class="<?php echo $status_icon; ?>"></i>
                      <?php echo $status_text; ?>
                    </div>
                  </div>
                  <div class="course-details">
                    <div class="course-subject">
                      <?php echo htmlspecialchars($fampianarana['taranja']); ?>
                    </div>
                    <div class="course-info">
                      <span class="course-room">
                        <i class="fas fa-door-open"></i>
                        Efitrano <?php echo htmlspecialchars($fampianarana['efitrano']); ?>
                      </span>
                      <span class="course-teacher">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <?php echo htmlspecialchars($fampianarana['mpampianatra']); ?>
                      </span>
                    </div>
                  </div>
                </div>
              <?php endwhile; ?>
            </div>
            
            <?php if ($fampianarana_totaliny > 0): ?>
              <div class="show-all-container">
                <a href="fandaharam_potoana_feno.php" class="btn btn-primary btn-full">
                  <i class="fas fa-calendar-week"></i> Hijery ny fandaharam-potoana feno (<?php echo $fampianarana_totaliny; ?> fampianarana amin'ity herinandro ity)
                </a>
              </div>
            <?php endif; ?>
          <?php else: ?>
            <div class="empty-schedule">
              <div class="empty-schedule-icon">
                <i class="fas fa-calendar-times"></i>
              </div>
              <h4>Tsy misy fampianarana anio</h4>
              <p>Mahafinaritra ity andro malalaka ity!</p>
              <?php if ($fampianarana_totaliny > 0): ?>
                <a href="fandaharam_potoana_feno.php" class="btn btn-primary">
                  <i class="fas fa-calendar-week"></i> Hijery ny fandaharam-potoana amin'ny herinandro
                </a>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      </section>

      <section class="karatra grades-card">
        <div class="card-header">
          <h3 class="lohateny-kely"><i class="fas fa-chart-line"></i> Ny naoty farany</h3>
        </div>
        <div class="card-body">
          <?php if ($naoty_farany): ?>
            <div class="grades-container">
              <?php foreach ($naoty_farany as $naoty): ?>
                <?php
                $naoty_isa = floatval($naoty['naoty']);
                $grade_class = match (true) {
                    $naoty_isa >= 16 => 'excellent',
                    $naoty_isa >= 12 => 'good',
                    $naoty_isa >= 10 => 'average',
                    default => 'poor',
                };
                ?>
                <div class="grade-item">
                  <div class="subject-info">
                    <span class="subject-name"><?php echo htmlspecialchars($naoty['taranja']); ?></span>
                    <span class="grade-date"><?php echo date('d/m/Y', strtotime($naoty['daty_fampidirana'])); ?></span>
                  </div>
                  <div class="grade-value <?php echo $grade_class; ?>">
                    <?php echo htmlspecialchars($naoty['naoty']); ?>/20
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
            <?php if ($naoty_totaliny > 3): ?>
              <div class="show-all-container">
                <a href="naoty_rehetra.php" class="btn btn-primary btn-full">
                  <i class="fas fa-eye"></i> Hijery ny rehetra (<?php echo $naoty_totaliny; ?> naoty)
                </a>
              </div>
            <?php endif; ?>
          <?php else: ?>
            <p class="empty-state">Ny naoty dia hiseho eto rehefa voasoratra.</p>
          <?php endif; ?>
        </div>
      </section>
    </div>

    <div class="content-right">
      <section class="karatra photo-card">
        <div class="card-header">
          <h3 class="lohateny-kely"><i class="fas fa-camera"></i> Sary</h3>
        </div>
        <div class="card-body">
          <div class="photo-upload-container">
            <div class="current-photo">
              <img src="<?php echo $sary_lalana . (str_starts_with($sary_lalana, 'data:image') ? '' : '?v=' . time()); ?>" alt="Sary" id="currentPhoto">
              <div class="photo-overlay"><i class="fas fa-camera"></i></div>
            </div>
            <form action="upload_sary.php" method="POST" enctype="multipart/form-data" class="upload-form form-modern">
              <div class="form-group">
                <input type="file" name="sary" accept="image/*" id="photoInput" required>
                <label for="photoInput" class="btn btn-primary"><i class="fas fa-upload"></i> Hanova ny sary</label>
                <button type="submit" class="btn btn-success" id="uploadBtn" style="display: none;"><i class="fas fa-check"></i> Hanamafy</button>
              </div>
            </form>
            <p class="upload-info text-muted">Karazana raisina: JPG, PNG, GIF<br>Habeny ambony indrindra: 5MB</p>
          </div>
        </div>
      </section>

      <section class="karatra homework-card">
        <div class="card-header">
          <h3 class="lohateny-kely"><i class="fas fa-tasks"></i> Asa farany</h3>
        </div>
        <div class="card-body">
          <?php if ($asa): ?>
            <div class="homework-container">
              <?php
              // Fandaminana ny asa araky ny taranja
              $asa_voavondrona = [];
              foreach ($asa as $asa_iray) {
                $taranja = $asa_iray['taranja'];
                if (!isset($asa_voavondrona[$taranja])) {
                  $asa_voavondrona[$taranja] = [];
                }
                $asa_voavondrona[$taranja][] = $asa_iray;
              }

              // Fisehoana ny asa voavondrona araky ny taranja
              foreach ($asa_voavondrona as $taranja => $asa_taranja):
              ?>
                <div class="homework-subject-section">
                  <h4 class="homework-subject"><?php echo htmlspecialchars($taranja); ?></h4>
                  <?php foreach ($asa_taranja as $asa_iray): ?>
                    <?php
                    $tara = new DateTime($asa_iray['daty_farany']) < new DateTime() && !$asa_iray['nomena'];
                    $statusClass = $asa_iray['nomena'] ? 'rendered' : ($tara ? 'overdue' : 'pending');
                    ?>
                    <div class="homework-item <?php echo $statusClass; ?>">
                      <div class="homework-header">
                        <div class="homework-status">
                          <?php if ($asa_iray['nomena']): ?>
                            <span class="status-badge rendered">
                              <i class="fas fa-check-circle"></i> Nomena
                            </span>
                          <?php elseif ($tara): ?>
                            <span class="status-badge overdue">
                              <i class="fas fa-times-circle"></i> Tara
                            </span>
                          <?php else: ?>
                            <span class="status-badge pending">
                              <i class="fas fa-clock"></i> Homena
                            </span>
                          <?php endif; ?>
                        </div>
                      </div>
                      <div class="homework-title"><?php echo htmlspecialchars($asa_iray['lohateny']); ?></div>
                      <div class="homework-content"><?php echo htmlspecialchars($asa_iray['votoaty']); ?></div>
                      <div class="homework-dates">
                        <div class="homework-date">
                          <i class="fas fa-calendar"></i> Noforonina ny <?php echo date('d/m/Y', strtotime($asa_iray['daty_fampidirana'])); ?>
                        </div>
                        <div class="homework-deadline <?php echo $tara ? 'overdue' : ''; ?>">
                          <i class="fas fa-hourglass-end"></i> Homena alohan'ny <?php echo date('d/m/Y \a\m\i\n\y H:i', strtotime($asa_iray['daty_farany'])); ?>
                        </div>
                      </div>
                      <div class="homework-actions">
                        <?php if ($asa_iray['rakitra']): ?>
                          <a href="pieces_jointes/<?php echo htmlspecialchars($asa_iray['rakitra']); ?>" target="_blank" class="btn btn-secondary">
                            <i class="fas fa-paperclip"></i> Rakitra ampiny
                          </a>
                        <?php endif; ?>
                        <?php if (!$asa_iray['nomena']): ?>
                          <button class="btn btn-primary" onclick="showSubmitModal(<?php echo $asa_iray['id']; ?>, '<?php echo htmlspecialchars($asa_iray['lohateny']); ?>')">
                            <i class="fas fa-upload"></i> Hanome ny asa
                          </button>
                        <?php else: ?>
                          <span class="rendered-info">
                            <i class="fas fa-check"></i> Nomena ny <?php echo date('d/m/Y \a\m\i\n\y H:i', strtotime($asa_iray['daty_fanatolotrana'])); ?>
                          </span>
                        <?php endif; ?>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endforeach; ?>
            </div>
            <?php if ($asa_totaliny > 0): ?>
              <div class="show-all-container">
                <a href="asa_rehetra.php" class="btn btn-primary btn-full">
                  <i class="fas fa-eye"></i> Hijery ny rehetra (<?php echo $asa_totaliny; ?> asa)
                </a>
              </div>
            <?php endif; ?>
          <?php else: ?>
            <p class="empty-state">Ny asa dia hiseho eto rehefa voafaritra ny mpampianatra.</p>
          <?php endif; ?>
        </div>
      </section>
    </div>
  </div>
</div>

<!-- Modal fanomezana asa -->
<div id="submitModal" class="modal" style="display: none;">
  <div class="modal-content">
    <div class="modal-header">
      <h3><i class="fas fa-upload"></i> Hanome ny asa</h3>
      <button type="button" class="close-btn" onclick="closeSubmitModal()">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <div class="modal-body">
      <div id="homework-info"></div>
      <form id="submitForm" action="hanome_asa.php" method="POST" enctype="multipart/form-data" class="form-modern">
        <input type="hidden" name="asa_id" id="asa_id">
        <div class="form-group">
          <label for="rakitra_nomena"><i class="fas fa-file"></i> Safidio ny rakitra:</label>
          <input type="file" name="rakitra_nomena" id="rakitra_nomena" required accept=".pdf,.doc,.docx,.txt,.jpg,.png,.zip">
          <small class="form-help text-muted">Karazana raisina: PDF, DOC, DOCX, TXT, JPG, PNG, ZIP. Habeny ambony indrindra: 10MB</small>
        </div>
        <div class="modal-actions flex gap-antonony">
          <button type="button" class="btn btn-secondary" onclick="closeSubmitModal()">
            <i class="fas fa-times"></i> Aoka ihany
          </button>
          <button type="button" class="btn btn-primary" onclick="submitHomework()">
            <i class="fas fa-check"></i> Hanome ny asa
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Variables ho an'ny modal fanomezana
let currentHomeworkId = null;

// Fonction ho an'ny fisehoana ny modal fanomezana
function showSubmitModal(homeworkId, title) {
    currentHomeworkId = homeworkId;
    
    document.getElementById('homework-info').innerHTML = `
        <div class="homework-info-card">
            <h4>${title}</h4>
            <p>Safidio ny rakitra izay tianareo hanome ho an'ity asa ity.</p>
        </div>
    `;
    
    document.getElementById('asa_id').value = homeworkId;
    document.getElementById('submitModal').style.display = 'flex';
}

// Fonction hanadino ny modal fanomezana
function closeSubmitModal() {
    document.getElementById('submitModal').style.display = 'none';
    document.getElementById('submitForm').reset();
    currentHomeworkId = null;
}

// Fonction hanome ny asa
function submitHomework() {
    const form = document.getElementById('submitForm');
    const fileInput = document.getElementById('rakitra_nomena');
    
    if (!fileInput.files[0]) {
        alert('Mba fidio ny rakitra hanome azafady.');
        return;
    }
    
    // Famaritana ny haben'ny rakitra (10MB ambony indrindra)
    if (fileInput.files[0].size > 10 * 1024 * 1024) {
        alert('Ny haben\'ny rakitra dia tsy tokony hihoatra ny 10MB.');
        return;
    }
    
    form.submit();
}

// Fanadino ny modal raha tsindrina ny ivelany
document.addEventListener('click', function(e) {
    const modal = document.getElementById('submitModal');
    if (e.target === modal) {
        closeSubmitModal();
    }
});

// Fanalahidy haingana hanadino ny modal
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeSubmitModal();
    }
});

document.getElementById('photoInput').addEventListener('change', function () {
  document.getElementById('uploadBtn').style.display = 'inline-block';
});
</script>

<style>
/* Styles spécifiques pour le profil étudiant */
.profile-header {
    background: var(--gradient-karatra);
    border-radius: var(--boribory-lehibe);
    padding: var(--halavany-lehibe);
    margin-bottom: var(--halavany-lehibe);
    box-shadow: var(--aloka-antonony);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: var(--halavany-antonony);
}

.profile-info {
    display: flex;
    align-items: center;
    gap: var(--halavany-antonony);
}

.avatar-container {
    position: relative;
}

.avatar {
    width: 80px;
    height: 80px;
    border-radius: var(--boribory-feno);
    object-fit: cover;
    border: 3px solid var(--loko-maitso);
}

.status-indicator {
    position: absolute;
    bottom: 5px;
    right: 5px;
    width: 16px;
    height: 16px;
    background: var(--loko-maitso);
    border-radius: var(--boribory-feno);
    border: 2px solid var(--loko-fotsy);
}

.user-details h1 {
    margin: 0;
    font-size: 24px;
    font-weight: 600;
    color: var(--loko-maizina);
}

.username {
    color: var(--loko-text-secondary);
    margin: 4px 0;
}

.user-meta {
    display: flex;
    gap: var(--halavany-antonony);
    flex-wrap: wrap;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: var(--halavany-kely);
    font-size: 14px;
    color: var(--loko-text-secondary);
}

.meta-item i {
    color: var(--loko-maitso);
}

.notification-badge {
    background: var(--loko-mena);
    color: var(--loko-fotsy);
    border-radius: var(--boribory-feno);
    padding: 2px 6px;
    font-size: 12px;
    font-weight: 600;
    min-width: 18px;
    text-align: center;
}

.card-header {
    border-bottom: 1px solid var(--loko-border);
    padding-bottom: var(--halavany-antonony);
    margin-bottom: var(--halavany-antonony);
}

.card-body {
    padding: 0;
}

.schedule-date {
    color: var(--loko-text-secondary);
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: var(--halavany-kely);
}

.course-item {
    background: var(--loko-volom-bary);
    border-radius: var(--boribory-antonony);
    padding: var(--halavany-antonony);
    margin-bottom: var(--halavany-antonony);
    border-left: 4px solid var(--loko-maitso);
}

.course-item.current {
    border-left-color: var(--loko-maitso);
    background: var(--loko-maitso-mazava);
}

.course-item.upcoming {
    border-left-color: var(--loko-manga);
    background: var(--loko-manga-malemy);
}

.course-item.past {
    border-left-color: var(--loko-volomparasy);
    opacity: 0.7;
}

.course-time {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--halavany-kely);
}

.time-range {
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: var(--halavany-kely);
}

.course-status {
    font-size: 12px;
    padding: 4px 8px;
    border-radius: var(--boribory-kely);
    font-weight: 500;
}

.course-status.current {
    background: var(--loko-maitso);
    color: var(--loko-fotsy);
}

.course-status.upcoming {
    background: var(--loko-manga);
    color: var(--loko-fotsy);
}

.course-status.past {
    background: var(--loko-volomparasy);
    color: var(--loko-fotsy);
}

.course-subject {
    font-weight: 600;
    margin-bottom: var(--halavany-kely);
}

.course-info {
    display: flex;
    gap: var(--halavany-antonony);
    font-size: 14px;
    color: var(--loko-text-secondary);
}

.course-room, .course-teacher {
    display: flex;
    align-items: center;
    gap: var(--halavany-kely);
}

.grade-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--halavany-antonony);
    background: var(--loko-volom-bary);
    border-radius: var(--boribory-antonony);
    margin-bottom: var(--halavany-antonony);
}

.subject-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.subject-name {
    font-weight: 500;
}

.grade-date {
    font-size: 12px;
    color: var(--loko-text-secondary);
}

.grade-value {
    font-weight: 600;
    padding: 4px 8px;
    border-radius: var(--boribory-kely);
}

.grade-value.excellent {
    background: var(--loko-maitso);
    color: var(--loko-fotsy);
}

.grade-value.good {
    background: var(--loko-manga);
    color: var(--loko-fotsy);
}

.grade-value.average {
    background: var(--loko-mavo);
    color: var(--loko-fotsy);
}

.grade-value.poor {
    background: var(--loko-mena);
    color: var(--loko-fotsy);
}

.photo-upload-container {
    text-align: center;
}

.current-photo {
    position: relative;
    display: inline-block;
    margin-bottom: var(--halavany-antonony);
}

.current-photo img {
    width: 120px;
    height: 120px;
    border-radius: var(--boribory-feno);
    object-fit: cover;
    border: 3px solid var(--loko-border);
}

.photo-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    border-radius: var(--boribory-feno);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: var(--transition-haingana);
    color: var(--loko-fotsy);
    font-size: 24px;
}

.current-photo:hover .photo-overlay {
    opacity: 1;
}

.upload-form {
    margin-bottom: var(--halavany-antonony);
}

.upload-info {
    font-size: 12px;
    line-height: 1.4;
}

.homework-item {
    background: var(--loko-volom-bary);
    border-radius: var(--boribory-antonony);
    padding: var(--halavany-antonony);
    margin-bottom: var(--halavany-antonony);
    border-left: 4px solid var(--loko-volomparasy);
}

.homework-item.rendered {
    border-left-color: var(--loko-maitso);
    background: var(--loko-maitso-mazava);
}

.homework-item.overdue {
    border-left-color: var(--loko-mena);
    background: #fef2f2;
}

.homework-item.pending {
    border-left-color: var(--loko-manga);
    background: var(--loko-manga-malemy);
}

.status-badge {
    font-size: 12px;
    padding: 4px 8px;
    border-radius: var(--boribory-kely);
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.status-badge.rendered {
    background: var(--loko-maitso);
    color: var(--loko-fotsy);
}

.status-badge.overdue {
    background: var(--loko-mena);
    color: var(--loko-fotsy);
}

.status-badge.pending {
    background: var(--loko-manga);
    color: var(--loko-fotsy);
}

.homework-title {
    font-weight: 600;
    margin: var(--halavany-kely) 0;
}

.homework-content {
    color: var(--loko-text-secondary);
    margin-bottom: var(--halavany-antonony);
}

.homework-dates {
    display: flex;
    flex-direction: column;
    gap: 4px;
    margin-bottom: var(--halavany-antonony);
    font-size: 14px;
}

.homework-date, .homework-deadline {
    display: flex;
    align-items: center;
    gap: var(--halavany-kely);
    color: var(--loko-text-secondary);
}

.homework-deadline.overdue {
    color: var(--loko-mena);
}

.homework-actions {
    display: flex;
    gap: var(--halavany-kely);
    flex-wrap: wrap;
}

.rendered-info {
    font-size: 12px;
    color: var(--loko-maitso);
    display: flex;
    align-items: center;
    gap: 4px;
}

.show-all-container {
    margin-top: var(--halavany-antonony);
}

/* Modal styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: var(--loko-fotsy);
    border-radius: var(--boribory-lehibe);
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: var(--aloka-tena-lehibe);
}

.modal-header {
    padding: var(--halavany-lehibe);
    border-bottom: 1px solid var(--loko-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    display: flex;
    align-items: center;
    gap: var(--halavany-kely);
}

.close-btn {
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    color: var(--loko-text-secondary);
    padding: 4px;
    border-radius: var(--boribory-kely);
}

.close-btn:hover {
    background: var(--loko-volom-bary);
}

.modal-body {
    padding: var(--halavany-lehibe);
}

.homework-info-card {
    background: var(--loko-volom-bary);
    border-radius: var(--boribory-antonony);
    padding: var(--halavany-antonony);
    margin-bottom: var(--halavany-lehibe);
}

.homework-info-card h4 {
    margin: 0 0 var(--halavany-kely) 0;
    font-weight: 600;
}

.homework-info-card p {
    margin: 0;
    color: var(--loko-text-secondary);
}

.modal-actions {
    margin-top: var(--halavany-lehibe);
}

.form-help {
    margin-top: var(--halavany-kely);
}

/* Responsive */
@media (max-width: 768px) {
    .profile-header {
        flex-direction: column;
        text-align: center;
    }
    
    .header-actions {
        width: 100%;
        justify-content: center;
    }
    
    .course-info {
        flex-direction: column;
        gap: var(--halavany-kely);
    }
    
    .homework-actions {
        flex-direction: column;
    }
    
    .homework-dates {
        font-size: 12px;
    }
}
</style>
</body>
</html>