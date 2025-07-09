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

$anarana_mpampiasa = $_SESSION['username'];
$anarana_feno = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : $anarana_mpampiasa;
$taranja_mpampianatra = isset($_SESSION['matiere']) ? $_SESSION['matiere'] : '';
$vondrona_mpampiasa = isset($_SESSION['groups']) ? $_SESSION['groups'] : [];

// Fanamarinana raha voafaritra ny taranja
if (!isset($_SESSION['matiere'])) {
    echo "❌ Taranja tsy voafaritra ao amin'ny session.";
    exit;
}

// Fisarahana anarana sy fanampiny
$ampahan_anarana = explode(" ", $anarana_feno);
$fanampiny = isset($ampahan_anarana[0]) ? $ampahan_anarana[0] : '';
$anarana = isset($ampahan_anarana[1]) ? $ampahan_anarana[1] : '';

// Sary profil miaraka amin'ny avatar default
$lalana_sary = "photos/" . $anarana_mpampiasa . ".jpg";
if (!file_exists($lalana_sary)) {
    // Mamorona avatar default miaraka amin'ny litera voalohany
    $litera_voalohany = strtoupper(substr($fanampiny, 0, 1) . substr($anarana, 0, 1));
    $lalana_sary = "data:image/svg+xml;base64," . base64_encode('
        <svg width="150" height="150" xmlns="http://www.w3.org/2000/svg">
            <circle cx="75" cy="75" r="75" fill="#9CA3AF"/>
            <text x="75" y="85" font-family="Arial, sans-serif" font-size="48" font-weight="bold" text-anchor="middle" fill="white">' . $litera_voalohany . '</text>
        </svg>
    ');
}

// Fifandraisana MySQL
$mysqli = new mysqli("localhost", "root", "Basique12345", "educonnect");
$mysqli->set_charset("utf8mb4");
if ($mysqli->connect_error) {
    die("Fahadisoana MySQL : " . $mysqli->connect_error);
}

// Normalisation ny taranja
$taranja_normalise = $taranja_mpampianatra;
if ($taranja_mpampianatra === 'Mathematique') {
    $taranja_normalise = 'Matematika';
}

// Makà ny kilasy samihafa ho an'ny taranjan'ny mpampianatra miaraka amin'ny statistika
$fanontaniana_kilasy = $mysqli->prepare("
    SELECT 
        kilasy,
        COUNT(*) as isan_asa,
        SUM(CASE WHEN daty_farany >= NOW() THEN 1 ELSE 0 END) as asa_misokatra,
        SUM(CASE WHEN daty_farany < NOW() THEN 1 ELSE 0 END) as asa_tapitra
    FROM asa 
    WHERE taranja = ? AND mpampianatra = ?
    GROUP BY kilasy
");
$fanontaniana_kilasy->bind_param("ss", $taranja_normalise, $anarana_feno);
$fanontaniana_kilasy->execute();
$valiny_kilasy = $fanontaniana_kilasy->get_result();

$kilasy_rehetra = [];
while ($andalana = $valiny_kilasy->fetch_assoc()) {
    $kilasy_rehetra[] = $andalana;
}

$fanontaniana_kilasy->close();
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="mg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mpitantana ny Asa - <?php echo htmlspecialchars($anarana_feno); ?></title>
    <link rel="stylesheet" href="ankapobe.css">
    <link rel="stylesheet" href="mpitantana_naoty.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Header miaraka amin'ny profil -->
        <header class="karatra">
            <div class="flex-between">
                <div class="flex gap-lehibe">
                    <div class="position-relative">
                        <img src="<?php echo $lalana_sary; ?>" alt="Sary profil" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;">
                        <div style="position: absolute; bottom: 0; right: 0; width: 20px; height: 20px; background: #22c55e; border-radius: 50%; border: 3px solid white;"></div>
                    </div>
                    <div>
                        <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--loko-maizina); margin-bottom: 0.25rem;">
                            <?php echo htmlspecialchars($fanampiny . ' ' . $anarana); ?>
                        </h1>
                        <p style="color: var(--loko-manga); font-weight: 500;">@<?php echo htmlspecialchars($anarana_mpampiasa); ?></p>
                        <div class="flex gap-antonony mt-1">
                            <span class="text-muted">
                                <i class="fas fa-chalkboard-teacher"></i>
                                Mpampianatra <?php echo htmlspecialchars($taranja_mpampianatra); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex gap-antonony">
                    <a href="mpampianatra.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Hiverina
                    </a>
                </div>
            </div>
        </header>

        <!-- Seho fototra: lisitry ny kilasy -->
        <div id="seho-kilasy">
            <div class="lohateny-pejy">
                <h2>
                    <i class="fas fa-tasks"></i>
                    Mpitantana ny Asa - <?php echo htmlspecialchars($taranja_mpampianatra); ?>
                </h2>
                <p>Safidio kilasy iray hitantanana ny asa</p>
            </div>

            <?php if (empty($kilasy_rehetra)): ?>
                <div class="karatra">
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h4>Tsy misy asa hita</h4>
                        <p>Tsy misy asa hita ho an'ity taranja ity.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="grid-kilasy">
                    <?php foreach ($kilasy_rehetra as $kilasy): ?>
                        <div class="karatra-kilasy" onclick="ampidiro_antsipiriany_kilasy('<?php echo htmlspecialchars($kilasy['kilasy']); ?>')">
                            <div class="sary-kilasy">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <div class="fampahalalana-kilasy">
                                <h3><?php echo htmlspecialchars($kilasy['kilasy']); ?></h3>
                                <p>Kilasy <?php echo htmlspecialchars($taranja_mpampianatra); ?></p>
                            </div>
                            <div class="statistika-kilasy">
                                <div class="singa-statistika">
                                    <i class="fas fa-list"></i>
                                    <span><?php echo $kilasy['isan_asa']; ?> asa</span>
                                </div>
                                <div class="singa-statistika">
                                    <i class="fas fa-clock"></i>
                                    <span><?php echo $kilasy['asa_misokatra']; ?> misokatra</span>
                                </div>
                                <div class="singa-statistika">
                                    <i class="fas fa-times-circle"></i>
                                    <span><?php echo $kilasy['asa_tapitra']; ?> tapitra</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Seho antsipiriany kilasy (miafina amin'ny voalohany) -->
        <div id="seho-antsipiriany-kilasy" class="seho-antsipiriany-kilasy" style="display: none;">
            <div class="lohateny-kilasy">
                <div class="lohateny-kilasy-title">
                    <h2 id="anarana-kilasy">Kilasy</h2>
                    <div class="famintinana-kilasy">
                        <div class="singa-famintinana">
                            <i class="fas fa-list"></i>
                            <span id="isan-asa-kilasy">0 asa</span>
                        </div>
                        <div class="singa-famintinana">
                            <i class="fas fa-clock"></i>
                            <span id="asa-misokatra-kilasy">0 misokatra</span>
                        </div>
                        <div class="singa-famintinana">
                            <i class="fas fa-times-circle"></i>
                            <span id="asa-tapitra-kilasy">0 tapitra</span>
                        </div>
                        <div class="singa-famintinana">
                            <i class="fas fa-book"></i>
                            <span><?php echo htmlspecialchars($taranja_mpampianatra); ?></span>
                        </div>
                    </div>
                </div>
                <button class="btn btn-primary" onclick="aseho_lisitry_kilasy()">
                    <i class="fas fa-arrow-left"></i>
                    Hiverina amin'ny kilasy
                </button>
            </div>

            <div class="fitoerana-mpianatra" id="fitoerana-asa">
                <!-- Ny asa dia ho ampidirina eto amin'ny JavaScript -->
            </div>
        </div>
    </div>

    <!-- Modal fanamarinana fafana -->
    <div id="modal-fafana" class="modal" style="display: none;">
        <div class="votoaty-modal">
            <div class="lohateny-modal">
                <h3>
                    <i class="fas fa-exclamation-triangle"></i>
                    Hamarino ny fafana
                </h3>
            </div>
            <div class="votoaty-modal-body">
                <p>Tena hofafana ve ity asa ity?</p>
                <div class="fampahalalana-naoty-modal" id="antsipiriany-asa">
                    <!-- Antsipiriany ny asa hofafana -->
                </div>
            </div>
            <div class="tongotra-modal">
                <button class="btn btn-secondary" onclick="hidio_modal_fafana()">
                    <i class="fas fa-times"></i>
                    Tsia
                </button>
                <button class="btn btn-danger" onclick="hamarino_fafana()">
                    <i class="fas fa-trash"></i>
                    Eny, fafao
                </button>
            </div>
        </div>
    </div>

    <!-- Modal seho rakitra -->
    <div id="modal-rakitra" class="modal" style="display: none;">
        <div class="votoaty-modal">
            <div class="lohateny-modal">
                <h3>
                    <i class="fas fa-file"></i>
                    Rakitra
                </h3>
            </div>
            <div class="votoaty-modal-body" id="votoaty-rakitra">
                <!-- Votoaty ny rakitra -->
            </div>
            <div class="tongotra-modal">
                <button class="btn btn-secondary" onclick="hidio_modal_rakitra()">
                    <i class="fas fa-times"></i>
                    Hidio
                </button>
            </div>
        </div>
    </div>

    <script src="mpitantana_asa.js"></script>
</body>
</html>