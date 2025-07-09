<?php
session_start();

// ✅ FANARAHA-MASO NY FIDIRANA - Ny mpampianatra ihany no afaka miditra
require_once 'check_access.php';
checkAccess(['G_Tous_Professeurs']);

// Fanamarinana raha tafiditra ny mpampiasa
if (!isset($_SESSION['username'])) {
    header("Location: fidirana.php");
    exit;
}

// Makà ny hafatra avy amin'ny session, raha misy
$hafatra = isset($_SESSION['hafatra']) ? $_SESSION['hafatra'] : "";
unset($_SESSION['hafatra']);

$anarana_mpampiasa = $_SESSION['username'];
$anarana_feno = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : $anarana_mpampiasa;
$vondrona_mpampiasa = isset($_SESSION['groups']) ? $_SESSION['groups'] : [];

// Fanasarahana anarana sy fanampin'anarana
$ampahany = explode(" ", $anarana_feno);
$anarana_kely = $ampahany[0] ?? '';
$anarana_fanamarihana = $ampahany[1] ?? '';

// Fikarohana ny sary miaraka amin'ny extensions mety
$extensions = ['jpg', 'png', 'gif'];
$lalana_sary = '';
foreach ($extensions as $ext) {
    if (file_exists("sary/$anarana_mpampiasa.$ext")) {
        $lalana_sary = "sary/$anarana_mpampiasa.$ext";
        break;
    }
}

// Raha tsy misy sary hita, mamorona avatar SVG miaraka amin'ny litera voalohany
if (!$lalana_sary) {
    $litera_voalohany = strtoupper(substr($anarana_kely, 0, 1) . substr($anarana_fanamarihana, 0, 1));
    $lalana_sary = "data:image/svg+xml;base64," . base64_encode('
        <svg width="150" height="150" xmlns="http://www.w3.org/2000/svg">
            <circle cx="75" cy="75" r="75" fill="#8b5cf6"/>
            <text x="75" y="85" font-family="Arial, sans-serif" font-size="48" font-weight="bold" text-anchor="middle" fill="white">' . $litera_voalohany . '</text>
        </svg>
    ');
}

// Mamaritra ny taranja
$taranja = "";
if (in_array("G_Mathematique", $vondrona_mpampiasa)) {
    $_SESSION['taranja'] = "Matematika";
    $taranja = "Matematika";
} elseif (in_array("G_Francais", $vondrona_mpampiasa)) {
    $_SESSION['taranja'] = "Frantsay";
    $taranja = "Frantsay";
} elseif (in_array("G_Histoire", $vondrona_mpampiasa)) {
    $_SESSION['taranja'] = "Tantara";
    $taranja = "Tantara";
} elseif (in_array("G_Physique", $vondrona_mpampiasa)) {
    $_SESSION['taranja'] = "Fizika";
    $taranja = "Fizika";
} else {
    $_SESSION['taranja'] = "Taranja hafa";
    $taranja = "Taranja hafa";
}

// Mamaritra ny kilasy araka ny vondrona AD
$kilasy = "";
foreach (["L1G1", "L1G2", "L2G1", "L2G2"] as $k) {
    if (in_array("G_" . $k, $vondrona_mpampiasa)) {
        $_SESSION['kilasy'] = $k;
        $kilasy = $k;
        break;
    }
}

// Fifandraisana MySQL ho an'ny fandaharam-potoana
$mysqli = new mysqli("localhost", "root", "Basique12345", "educonnect");
$mysqli->set_charset("utf8mb4");
if ($mysqli->connect_error) {
    die("Fahadisoana MySQL : " . $mysqli->connect_error);
}

function maka_toetrandro($tanàna = "Antananarivo") {
    $apiKey = "TA_CLE_API_ETO"; // ⭐ Mets ta clé ici
    $url = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($tanàna) . "&appid=" . $apiKey . "&units=metric&lang=mg";

    $valiny = @file_get_contents($url);
    if (!$valiny) return null;

    $toetrandro = json_decode($valiny, true);

    return [
        'maripana' => $toetrandro['main']['temp'] ?? null,
        'famaritana' => $toetrandro['weather'][0]['description'] ?? '',
        'kisary' => $toetrandro['weather'][0]['icon'] ?? '',
        'tanàna' => $toetrandro['name'] ?? $tanàna
    ];
}



$andro_mg = [
    'Monday' => 'Alatsinainy',
    'Tuesday' => 'Talata',
    'Wednesday' => 'Alarobia',
    'Thursday' => 'Alakamisy',
    'Friday' => 'Zoma',
    'Saturday' => 'Sabotsy',
    'Sunday' => 'Alahady'
];

$andro_ankehitriny = $andro_mg[date('l')];

// Fandaharam-potoana androany
$fanontaniana_edt_androany = $mysqli->prepare("SELECT * FROM emplois_du_temps WHERE professeur = ? AND jour_semaine = ? ORDER BY heure_debut");
$fanontaniana_edt_androany->bind_param("ss", $anarana_feno, $andro_ankehitriny);
$fanontaniana_edt_androany->execute();
$valiny_edt_androany = $fanontaniana_edt_androany->get_result();

// Manisa ny isan'ny fampianarana amin'ny herinandro
$fanontaniana_isa_edt = $mysqli->prepare("SELECT COUNT(*) as totalina FROM emplois_du_temps WHERE professeur = ?");
$fanontaniana_isa_edt->bind_param("s", $anarana_feno);
$fanontaniana_isa_edt->execute();
$valiny_isa_edt = $fanontaniana_isa_edt->get_result();
$totalin_ny_fampianarana = $valiny_isa_edt->fetch_assoc()['totalina'];
?>

<!DOCTYPE html>
<html lang="mg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mpampianatra - <?php echo htmlspecialchars($anarana_feno); ?></title>
    
    <link rel="stylesheet" href="ankapobe.css">
    <link rel="stylesheet" href="mpampianatra.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
            <div class="lohateny-mpampianatra">
                <div class="sary-mpampianatra-lehibe">
                    <img src="<?php echo $lalana_sary . (str_starts_with($lalana_sary, 'data:image') ? '' : '?v=' . time()); ?>" alt="Sarin'ny mpampianatra" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                </div>
                <div class="fampahalalana-fototra-mpampianatra">
                    <h2><?php echo htmlspecialchars($anarana_kely . ' ' . $anarana_fanamarihana); ?></h2>
                    <p class="username">@<?php echo htmlspecialchars($anarana_mpampiasa); ?></p>
                    <div class="toe-mpampianatra">
                        <span class="badge-toe mpampianatra">
                            <i class="fas fa-chalkboard-teacher"></i>
                            Mpampianatra
                        </span>
                        <span class="badge-toe taranja">
                            <i class="fas fa-book"></i>
                            <?php echo htmlspecialchars($taranja); ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="header-actions">
                <a href="mpitantana_naoty.php" class="btn btn-primary">
                    <i class="fas fa-clipboard-list"></i>
                    Mpitantana ny Naoty
                </a>
                <a href="mpitantana_asa.php" class="btn btn-success">
                    <i class="fas fa-tasks"></i>
                    Mpitantana ny Asa
                </a>
                <a href="mivoaka.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i>
                    Hivoaka
                </a>
            </div>
        </header>

        <?php if ($hafatra): ?>
            <div class="fampandrenesana fampandrenesana-fahombiazana">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($hafatra); ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-2 mt-3">
            <!-- MANDEFA NAOTY -->
            <div class="karatra">
                <div class="lohateny-antonony">
                    <i class="fas fa-star"></i> Mandefa naoty ho an'ny mpianatra
                </div>
                <form action="mampiditra_naoty.php" method="post" class="form-modern">
                    <div class="form-group">
                        <label for="mpianatra"><i class="fas fa-user-graduate"></i> Anaran'ny mpianatra</label>
                        <input type="text" name="mpianatra" id="mpianatra" required placeholder="Ohatra: marie.dubois">
                    </div>
                    <input type="hidden" name="taranja" value="<?php echo htmlspecialchars($taranja); ?>">
                    <input type="hidden" name="kilasy" value="<?php echo htmlspecialchars($kilasy); ?>">
                    <div class="form-group">
                        <label for="naoty"><i class="fas fa-chart-line"></i> Naoty amin'ny 20</label>
                        <input type="number" name="naoty" id="naoty" min="0" max="20" step="0.5" required placeholder="Ohatra: 15.5">
                    </div>
                    <button type="submit" class="btn btn-primary btn-full">
                        <i class="fas fa-paper-plane"></i> Alefaso ny naoty
                    </button>
                </form>
            </div>

            <!-- MANDEFA ASA -->
            <div class="karatra">
                <div class="lohateny-antonony">
                    <i class="fas fa-tasks"></i> Mandefa asa ho an'ny kilasy
                </div>
                <form action="mampiditra_asa.php" method="post" enctype="multipart/form-data" class="form-modern">
                    <input type="hidden" name="taranja" value="<?php echo htmlspecialchars($taranja); ?>">
                    <div class="form-group">
                        <label for="kilasy"><i class="fas fa-users"></i> Kilasy kendrena</label>
                        <select name="kilasy" id="kilasy" required>
                            <option value="L1G1">L1G1</option>
                            <option value="L1G2">L1G2</option>
                            <option value="L2G1">L2G1</option>
                            <option value="L2G2">L2G2</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="lohateny"><i class="fas fa-heading"></i> Lohatenin'ny asa</label>
                        <input type="text" name="lohateny" id="lohateny" required placeholder="Ohatra: Fanazaran-tsoratra toko 5">
                    </div>
                    <div class="form-group">
                        <label for="votoaty"><i class="fas fa-file-alt"></i> Votoaty / torolalana</label>
                        <textarea name="votoaty" id="votoaty" rows="5" required placeholder="Lazao ny torolalana momba ny asa..."></textarea>
                    </div>
                    <div class="form-group">
                        <label for="rakitra"><i class="fas fa-paperclip"></i> Rakitra miaraka (azo atao)</label>
                        <input type="file" name="rakitra" id="rakitra" accept=".pdf,.doc,.docx,.txt,.jpg,.png">
                    </div>
                    <div class="form-group">
                        <label for="daty_farany"><i class="fas fa-calendar-alt"></i> Daty farany fandefasana</label>
                        <input type="datetime-local" name="daty_farany" id="daty_farany" required>
                    </div>
                    <button type="submit" class="btn btn-success btn-full">
                        <i class="fas fa-share"></i> Alefaso ny asa
                    </button>
                </form>
            </div>
        </div>

        <!-- FANDAHARAM-POTOANA -->
        <div class="karatra mt-3">
            <div class="lohateny-antonony">
                <i class="fas fa-calendar-day"></i> Ny fandaharam-potoanako - <?php echo $andro_ankehitriny; ?>
            </div>
            
            <?php if ($valiny_edt_androany->num_rows > 0): ?>
                <div class="grid grid-3 mt-2">
                    <?php 
                    $ora_ankehitriny = date('H:i');
                    while ($fampianarana = $valiny_edt_androany->fetch_assoc()): 
                        $mandeha_izao = ($ora_ankehitriny >= $fampianarana['heure_debut'] && $ora_ankehitriny <= $fampianarana['heure_fin']);
                        $ho_avy = ($ora_ankehitriny < $fampianarana['heure_debut']);
                        $vita = ($ora_ankehitriny > $fampianarana['heure_fin']);
                        
                        $kilasy_toe = '';
                        $soratra_toe = '';
                        $kisary_toe = '';
                        
                        if ($mandeha_izao) {
                            $kilasy_toe = 'mandeha';
                            $soratra_toe = 'Mandeha izao';
                            $kisary_toe = 'fas fa-play-circle';
                        } elseif ($ho_avy) {
                            $kilasy_toe = 'ho-avy';
                            $soratra_toe = 'Ho avy';
                            $kisary_toe = 'fas fa-clock';
                        } else {
                            $kilasy_toe = 'vita';
                            $soratra_toe = 'Vita';
                            $kisary_toe = 'fas fa-check-circle';
                        }
                    ?>
                        <div class="karatra karatra-fampianarana <?php echo $kilasy_toe; ?>">
                            <div class="ora-fampianarana">
                                <i class="<?php echo $kisary_toe; ?>"></i>
                                <?php echo htmlspecialchars($fampianarana['heure_debut'] . ' - ' . $fampianarana['heure_fin']); ?>
                            </div>
                            <div class="antsipirihan-fampianarana">
                                <div class="taranja-fampianarana">
                                    <?php echo htmlspecialchars($fampianarana['matiere']); ?>
                                </div>
                                <div class="kilasy-fampianarana">
                                    <?php echo htmlspecialchars($fampianarana['classe']); ?>
                                </div>
                                <div class="efitrano-fampianarana">
                                    <i class="fas fa-door-open"></i>
                                    Efitrano <?php echo htmlspecialchars($fampianarana['salle']); ?>
                                </div>
                            </div>
                            <div class="toe-fampianarana <?php echo $kilasy_toe; ?>">
                                <?php echo $soratra_toe; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <?php if ($totalin_ny_fampianarana > 0): ?>
                    <div class="text-center mt-3">
                        <a href="fandaharam_potoana_feno.php" class="btn btn-secondary">
                            <i class="fas fa-calendar-week"></i> Jereo ny fandaharam-potoana feno (<?php echo $totalin_ny_fampianarana; ?> fampianarana)
                        </a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <h4>Tsy misy fampianarana voalahatra androany</h4>
                    <p>Tsy manana fampianarana voalahatra amin'ity andro ity ianao.</p>
                    <?php if ($totalin_ny_fampianarana > 0): ?>
                        <a href="fandaharam_potoana_feno.php" class="btn btn-primary">
                            <i class="fas fa-calendar-week"></i> Jereo ny fandaharam-potoana feno
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- FAMPAHALALANA MPAMPIANATRA -->
        <div class="karatra mt-3">
            <div class="lohateny-antonony">
                <i class="fas fa-info-circle"></i> Ny mombamomba ahy
            </div>
            <div class="grid grid-4 mt-2">
                <div class="singa-fampahalalana">
                    <i class="fas fa-user"></i>
                    <span class="label">Anarana</span>
                    <span class="sanda"><?php echo htmlspecialchars($anarana_kely); ?></span>
                </div>
                <div class="singa-fampahalalana">
                    <i class="fas fa-user"></i>
                    <span class="label">Fanampin'anarana</span>
                    <span class="sanda"><?php echo htmlspecialchars($anarana_fanamarihana); ?></span>
                </div>
                <div class="singa-fampahalalana">
                    <i class="fas fa-book"></i>
                    <span class="label">Taranja</span>
                    <span class="sanda"><?php echo htmlspecialchars($taranja); ?></span>
                </div>
                <div class="singa-fampahalalana">
                    <i class="fas fa-id-badge"></i>
                    <span class="label">Famantarana</span>
                    <span class="sanda">@<?php echo htmlspecialchars($anarana_mpampiasa); ?></span>
                </div>
            </div>
        </div>
    </div>
    

<!-- Botpress Webchat -->


<script src="https://cdn.botpress.cloud/webchat/v3.1/inject.js" defer></script>
<script src="https://files.bpcontent.cloud/2025/07/09/03/20250709032746-FN59BA3W.js" defer></script>
    
    



   

</body>
</html>
