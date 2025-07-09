<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: fidirana.php");
    exit;
}

$anarana_mpampiasa = $_SESSION['username'];
$anarana_feno = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : $anarana_mpampiasa;
$parts = explode(" ", $anarana_feno);
$anarana_voalohany = isset($parts[0]) ? $parts[0] : '';
$anarana_farany = isset($parts[1]) ? $parts[1] : '';

// Sary miaraka amin'ny avatar default
$sary_lalana = "photos/" . $anarana_mpampiasa . ".jpg";
if (!file_exists($sary_lalana)) {
    $litera_voalohany = strtoupper(substr($anarana_voalohany, 0, 1) . substr($anarana_farany, 0, 1));
    $sary_lalana = "data:image/svg+xml;base64," . base64_encode('
        <svg width="150" height="150" xmlns="http://www.w3.org/2000/svg">
            <circle cx="75" cy="75" r="75" fill="#9CA3AF"/>
            <text x="75" y="85" font-family="Arial, sans-serif" font-size="48" font-weight="bold" text-anchor="middle" fill="white">' . $litera_voalohany . '</text>
        </svg>
    ');
}

// Fifandraisana amin'ny MySQL
$mysqli = new mysqli("localhost", "root", "Basique12345", "educonnect");
if ($mysqli->connect_error) {
    die("Hadisoana MySQL : " . $mysqli->connect_error);
}

// Haka ny naoty rehetra an'ny mpianatra voavondrona araky ny taranja
$stmt_naoty = $mysqli->prepare("SELECT naoty, taranja, daty_fampidirana FROM naoty WHERE mpianatra = ? ORDER BY taranja, daty_fampidirana DESC");
$stmt_naoty->bind_param("s", $anarana_mpampiasa);
$stmt_naoty->execute();
$result_naoty = $stmt_naoty->get_result();

$naoty_araky_taranja = [];
$naoty_totaliny = 0;
$totalin_naoty = 0;

if ($result_naoty && $result_naoty->num_rows > 0) {
    while ($row = $result_naoty->fetch_assoc()) {
        $taranja = $row['taranja'];
        if (!isset($naoty_araky_taranja[$taranja])) {
            $naoty_araky_taranja[$taranja] = [];
        }
        $naoty_araky_taranja[$taranja][] = $row;
        $naoty_totaliny++;
        $totalin_naoty += floatval($row['naoty']);
    }
}

// Kajy ny salan'isa ankapobeny
$salan_isa_ankapobeny = $naoty_totaliny > 0 ? round($totalin_naoty / $naoty_totaliny, 2) : 0;

// Kajy ny salan'isa araky ny taranja
$salan_isa_araky_taranja = [];
foreach ($naoty_araky_taranja as $taranja => $naoty_list) {
    $totalin_taranja = 0;
    foreach ($naoty_list as $naoty) {
        $totalin_taranja += floatval($naoty['naoty']);
    }
    $salan_isa_araky_taranja[$taranja] = round($totalin_taranja / count($naoty_list), 2);
}
?>
<!DOCTYPE html>
<html lang="mg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ny naoty rehetra - <?php echo htmlspecialchars($anarana_feno); ?></title>
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
                <img src="<?php echo $sary_lalana; ?>" alt="Sary" class="avatar">
                <div class="status-indicator"></div>
            </div>
            <div class="user-details">
                <h1 class="user-name"><?php echo htmlspecialchars($anarana_voalohany . ' ' . $anarana_farany); ?></h1>
                <p class="username">@<?php echo htmlspecialchars($anarana_mpampiasa); ?></p>
                <div class="user-meta">
                    <span class="meta-item">
                        <i class="fas fa-chart-line"></i>
                        Ny naoty rehetra
                    </span>
                </div>
            </div>
        </div>
        <div class="header-actions">
            <a href="mpianatra.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i>
                Hiverina amin'ny mombamomba
            </a>
            <a href="logout.php" class="btn btn-danger">
                <i class="fas fa-sign-out-alt"></i>
                Hivoaka
            </a>
        </div>
    </header>

    <!-- Statistika ankapobeny -->
    <div class="stats-container grid grid-3">
        <div class="karatra text-center">
            <div class="stat-icon">
                <i class="fas fa-calculator"></i>
            </div>
            <div class="stat-info">
                <h3 class="lohateny-kely">Salan'isa ankapobeny</h3>
                <div class="stat-value <?php 
                    if ($salan_isa_ankapobeny >= 16) echo 'text-success';
                    elseif ($salan_isa_ankapobeny >= 12) echo 'text-primary';
                    elseif ($salan_isa_ankapobeny >= 10) echo 'text-warning';
                    else echo 'text-danger';
                ?>">
                    <?php echo $salan_isa_ankapobeny; ?>/20
                </div>
            </div>
        </div>
        
        <div class="karatra text-center">
            <div class="stat-icon">
                <i class="fas fa-list-ol"></i>
            </div>
            <div class="stat-info">
                <h3 class="lohateny-kely">Naoty rehetra</h3>
                <div class="stat-value"><?php echo $naoty_totaliny; ?></div>
            </div>
        </div>
        
        <div class="karatra text-center">
            <div class="stat-icon">
                <i class="fas fa-book"></i>
            </div>
            <div class="stat-info">
                <h3 class="lohateny-kely">Taranja</h3>
                <div class="stat-value"><?php echo count($naoty_araky_taranja); ?></div>
            </div>
        </div>
    </div>

    <!-- Naoty araky ny taranja -->
    <div class="subjects-container">
        <?php if (count($naoty_araky_taranja) > 0): ?>
            <?php foreach ($naoty_araky_taranja as $taranja => $naoty_list): ?>
                <section class="karatra subject-card mb-4">
                    <div class="card-header flex-between">
                        <h3 class="lohateny-kely">
                            <i class="fas fa-book-open"></i>
                            <?php echo htmlspecialchars($taranja); ?>
                        </h3>
                        <div class="subject-average <?php 
                            $moy = $salan_isa_araky_taranja[$taranja];
                            if ($moy >= 16) echo 'text-success';
                            elseif ($moy >= 12) echo 'text-primary';
                            elseif ($moy >= 10) echo 'text-warning';
                            else echo 'text-danger';
                        ?>">
                            Salan'isa: <?php echo $salan_isa_araky_taranja[$taranja]; ?>/20
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="grades-container">
                            <?php foreach ($naoty_list as $naoty): ?>
                                <?php
                                $naoty_isa = floatval($naoty['naoty']);
                                $grade_class = '';
                                if ($naoty_isa >= 16) {
                                    $grade_class = 'text-success';
                                } elseif ($naoty_isa >= 12) {
                                    $grade_class = 'text-primary';
                                } elseif ($naoty_isa >= 10) {
                                    $grade_class = 'text-warning';
                                } else {
                                    $grade_class = 'text-danger';
                                }
                                ?>
                                <div class="grade-item">
                                    <div class="subject-info">
                                        <span class="grade-date">
                                            <i class="fas fa-calendar"></i>
                                            <?php echo date('d/m/Y', strtotime($naoty['daty_fampidirana'])); ?>
                                        </span>
                                    </div>
                                    <div class="grade-value <?php echo $grade_class; ?>">
                                        <?php echo htmlspecialchars($naoty['naoty']); ?>/20
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            <?php endforeach; ?>
        <?php else: ?>
            <section class="karatra">
                <div class="card-body">
                    <p class="empty-state">Mbola tsy misy naoty nampidirina.</p>
                </div>
            </section>
        <?php endif; ?>
    </div>
</div>

<style>
    .stat-icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        color: var(--loko-maitso);
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 600;
    }

    .subject-average {
        font-weight: 600;
        padding: var(--halavany-kely) var(--halavany-antonony);
        border-radius: var(--boribory-kely);
        background: var(--loko-volom-bary);
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

    .grade-date {
        display: flex;
        align-items: center;
        gap: var(--halavany-kely);
        color: var(--loko-text-secondary);
        font-size: 14px;
    }

    .grade-value {
        font-weight: 600;
        font-size: 1.2rem;
    }
</style>
</body>
</html>