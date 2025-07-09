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

// Asa rehetra - CORRECTED TO USE entin_mody_miverina
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
");

if ($stmt === false) {
    die("Hadisoana SQL: " . $mysqli->error);
}

$stmt->bind_param("ss", $anarana_mpampiasa, $kilasy);
$stmt->execute();
$result = $stmt->get_result();

$asa_araky_taranja = [];
$asa_totaliny = 0;
$asa_nomena = 0;
$asa_tara = 0;

while ($row = $result->fetch_assoc()) {
    $taranja = $row['taranja'];
    if (!isset($asa_araky_taranja[$taranja])) {
        $asa_araky_taranja[$taranja] = [];
    }
    $asa_araky_taranja[$taranja][] = $row;
    $asa_totaliny++;

    if ($row['nomena']) {
        $asa_nomena++;
    } elseif (new DateTime($row['daty_farany']) < new DateTime()) {
        $asa_tara++;
    }
}
?>
<!DOCTYPE html>
<html lang="mg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ny asa rehetra - <?php echo htmlspecialchars($anarana_feno); ?></title>
    <link rel="stylesheet" href="ankapobe.css">
    <link rel="stylesheet" href="fitantanana_fandaharam_potoana.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header class="profile-header">
            <div class="profile-info">
                <div class="avatar-container">
                    <img src="<?php echo $sary_lalana . (str_starts_with($sary_lalana, 'data:image') ? '' : '?v=' . time()); ?>" alt="Sary" class="avatar">
                    <div class="status-indicator"></div>
                </div>
                <div class="user-details">
                    <h1 class="user-name"><?php echo htmlspecialchars($anarana_voalohany . ' ' . $anarana_farany); ?></h1>
                    <p class="username">@<?php echo htmlspecialchars($anarana_mpampiasa); ?></p>
                    <div class="user-meta">
                        <span class="meta-item"><i class="fas fa-tasks"></i> Ny asa rehetra</span>
                        <?php if ($kilasy): ?>
                            <span class="meta-item"><i class="fas fa-users"></i> Kilasy <?php echo htmlspecialchars($kilasy); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="header-actions">
                <a href="mpianatra.php" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Hiverina amin'ny mombamomba</a>
                <a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Hivoaka</a>
            </div>
        </header>

        <div class="stats-container grid grid-4">
            <div class="karatra text-center">
                <div class="stat-icon"><i class="fas fa-tasks"></i></div>
                <div class="stat-info">
                    <h3 class="lohateny-kely">Asa rehetra</h3>
                    <div class="stat-value text-primary"><?php echo $asa_totaliny; ?></div>
                </div>
            </div>
            <div class="karatra text-center">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-info">
                    <h3 class="lohateny-kely">Asa nomena</h3>
                    <div class="stat-value text-success"><?php echo $asa_nomena; ?></div>
                </div>
            </div>
            <div class="karatra text-center">
                <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="stat-info">
                    <h3 class="lohateny-kely">Tara</h3>
                    <div class="stat-value text-danger"><?php echo $asa_tara; ?></div>
                </div>
            </div>
            <div class="karatra text-center">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-info">
                    <h3 class="lohateny-kely">Homena</h3>
                    <div class="stat-value"><?php echo $asa_totaliny - $asa_nomena - $asa_tara; ?></div>
                </div>
            </div>
        </div>

        <div class="homework-list-container">
            <?php if (count($asa_araky_taranja) > 0): ?>
                <?php foreach ($asa_araky_taranja as $taranja => $asa_list): ?>
                    <section class="matiere-section mb-4">
                        <h2 class="lohateny-antonony"><?php echo htmlspecialchars($taranja); ?></h2>
                        <?php foreach ($asa_list as $asa): ?>
                            <?php
                            $tara = new DateTime($asa['daty_farany']) < new DateTime() && !$asa['nomena'];
                            $statusClass = $asa['nomena'] ? 'rendered' : ($tara ? 'overdue' : 'pending');
                            $daty_farany_lasa = new DateTime($asa['daty_farany']) < new DateTime();
                            ?>
                            <section class="karatra homework-detail-card <?php echo $statusClass; ?> mb-3">
                                <div class="card-header">
                                    <div class="homework-header-info flex-between">
                                        <h3 class="lohateny-kely"><?php echo htmlspecialchars($asa['lohateny']); ?></h3>
                                        <div class="homework-status">
                                            <?php if ($asa['nomena']): ?>
                                                <span class="status-badge rendered"><i class="fas fa-check-circle"></i> Nomena ny <?php echo date('d/m/Y \a\m\i\n\y H:i', strtotime($asa['daty_fanatolotrana'])); ?></span>
                                            <?php elseif ($tara): ?>
                                                <span class="status-badge overdue"><i class="fas fa-times-circle"></i> Tara</span>
                                            <?php else: ?>
                                                <span class="status-badge pending"><i class="fas fa-clock"></i> Homena</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="homework-content mb-3">
                                        <p><?php echo nl2br(htmlspecialchars($asa['votoaty'])); ?></p>
                                    </div>
                                    <div class="homework-dates mb-3">
                                        <div class="homework-date"><i class="fas fa-calendar"></i> Noforonina ny <?php echo date('d/m/Y \a\m\i\n\y H:i', strtotime($asa['daty_fampidirana'])); ?></div>
                                        <div class="homework-deadline <?php echo $tara ? 'overdue' : ''; ?>">
                                            <i class="fas fa-hourglass-end"></i> Homena alohan'ny <?php echo date('d/m/Y \a\m\i\n\y H:i', strtotime($asa['daty_farany'])); ?>
                                        </div>
                                    </div>
                                    <div class="homework-actions flex gap-antonony">
                                        <?php if ($asa['rakitra']): ?>
                                            <a href="pieces_jointes/<?php echo htmlspecialchars($asa['rakitra']); ?>" target="_blank" class="btn btn-secondary">
                                                <i class="fas fa-paperclip"></i> Hisintona ny rakitra ampiny
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!$asa['nomena']): ?>
                                            <?php if ($daty_farany_lasa): ?>
                                                <button class="btn btn-secondary" disabled style="opacity: 0.6; cursor: not-allowed;" onclick="alert('Efa lasa ny fe-potoana fanomezana ity asa ity.')">
                                                    <i class="fas fa-clock"></i> Efa lasa ny fe-potoana
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-primary" onclick="showSubmitModal(<?php echo $asa['id']; ?>, '<?php echo htmlspecialchars($asa['lohateny'], ENT_QUOTES); ?>', '<?php echo $asa['daty_farany']; ?>')" data-deadline="<?php echo $asa['daty_farany']; ?>">
                                                    <i class="fas fa-upload"></i> Hanome ny asa
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </section>
                        <?php endforeach; ?>
                    </section>
                <?php endforeach; ?>
            <?php else: ?>
                <section class="karatra">
                    <div class="card-body">
                        <p class="empty-state">Mbola tsy misy asa nomena ho an'ny kilasinareo.</p>
                    </div>
                </section>
            <?php endif; ?>
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
                    <input type="hidden" name="redirect" value="asa_rehetra.php">
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
        let currentHomeworkId = null;

        function showSubmitModal(homeworkId, title, deadline) {
            currentHomeworkId = homeworkId;
            
            document.getElementById('homework-info').innerHTML = `
                <div class="homework-info-card">
                    <h4>${title}</h4>
                    <p>Safidio ny rakitra izay tianareo hanome ho an'ity asa ity.</p>
                    <div class="deadline-warning">
                        <i class="fas fa-clock"></i> Homena alohan'ny ${new Date(deadline).toLocaleDateString('mg')} ${new Date(deadline).toLocaleTimeString('mg')}
                    </div>
                </div>
            `;
            
            document.getElementById('asa_id').value = homeworkId;
            document.getElementById('submitModal').style.display = 'flex';
        }

        function closeSubmitModal() {
            document.getElementById('submitModal').style.display = 'none';
            document.getElementById('submitForm').reset();
            currentHomeworkId = null;
        }

        function submitHomework() {
            const form = document.getElementById('submitForm');
            const fileInput = document.getElementById('rakitra_nomena');
            
            if (!fileInput.files[0]) {
                alert('Mba fidio ny rakitra hanome azafady.');
                return;
            }
            
            if (fileInput.files[0].size > 10 * 1024 * 1024) {
                alert('Ny haben\'ny rakitra dia tsy tokony hihoatra ny 10MB.');
                return;
            }
            
            form.submit();
        }

        document.addEventListener('click', function(e) {
            const modal = document.getElementById('submitModal');
            if (e.target === modal) {
                closeSubmitModal();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeSubmitModal();
            }
        });
    </script>

    <style>
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

        .stat-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: var(--loko-maitso);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 600;
        }

        .homework-detail-card {
            border-left: 4px solid var(--loko-volomparasy);
        }

        .homework-detail-card.rendered {
            border-left-color: var(--loko-maitso);
            background: var(--loko-maitso-mazava);
        }

        .homework-detail-card.overdue {
            border-left-color: var(--loko-mena);
            background: #fef2f2;
        }

        .homework-detail-card.pending {
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

        .deadline-warning {
            background: var(--loko-mavo);
            color: var(--loko-fotsy);
            padding: var(--halavany-kely);
            border-radius: var(--boribory-kely);
            margin-top: var(--halavany-antonony);
            display: flex;
            align-items: center;
            gap: var(--halavany-kely);
            font-size: 14px;
        }

        .homework-dates {
            display: flex;
            flex-direction: column;
            gap: 4px;
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

        .flex-between {
            display: flex;
            justify-content: space-between;
            align-items: center;
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