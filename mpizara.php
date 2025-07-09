<?php
session_start();

// ✅ FANARAHA-MASO NY FIDIRANA - Ny mpizara ihany no afaka miditra
require_once 'check_access.php';
checkAccess(['G_Tous_Personnel_Admin']);

// Manamarina raha tafiditra ny mpampiasa
if (!isset($_SESSION['username'])) {
    // Mandefa ny mpampiasa mankany amin'ny pejin'ny fidirana raha tsy tafiditra
    header("Location: fidirana.php");
    exit();
}

// Maka ny anarana feno an'ny mpampiasa tafiditra (raha voafaritra ao amin'ny session)
$anarana_feno = $_SESSION['fullname'] ?? 'Anarana tsy voafaritra';
$anarana_mpampiasa = $_SESSION['username'];

// Fanapahana anarana sy fanampin'anarana
$ampahany = explode(" ", $anarana_feno);
$anarana_voalohany = $ampahany[0] ?? '';
$anarana_farany = $ampahany[1] ?? '';

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
    $litera_voalohany = strtoupper(substr($anarana_voalohany, 0, 1) . substr($anarana_farany, 0, 1));
    $lalana_sary = "data:image/svg+xml;base64," . base64_encode('
        <svg width="150" height="150" xmlns="http://www.w3.org/2000/svg">
            <circle cx="75" cy="75" r="75" fill="#9CA3AF"/>
            <text x="75" y="85" font-family="Arial, sans-serif" font-size="48" font-weight="bold" text-anchor="middle" fill="white">' . $litera_voalohany . '</text>
        </svg>
    ');
}

// Fampisehoana ny hafatra session (hadisoana na fahombiazana)
if (isset($_SESSION['hafatra'])) {
    echo '<div class="hafatra">' . $_SESSION['hafatra'] . '</div>';
    unset($_SESSION['hafatra']);
}

// =================== Fakana ny kilasy LDAP ===================
$lisitry_kilasy = [];
$ldapconn = ldap_connect("ldap://192.168.40.132");

if ($ldapconn) {
    ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
    $ldapbind = ldap_bind($ldapconn, "EDUCONNECT\\AndrypL1G2", "Test@1234");

    if ($ldapbind) {
        // Fikarohana ao amin'ny OU=Groupes
        $base_dn = "OU=Groupes,DC=educonnect,DC=mg"; // Base DN ho an'ny OU Groupes
        $filter = "(cn=G_L*)"; // Fikarohana ny vondrona G_L
        $search = ldap_search($ldapconn, $base_dn, $filter, ["cn"]);

        if ($search) {
            $entries = ldap_get_entries($ldapconn, $search);
            // Fanaraha-maso sy fakana ny kilasy
            for ($i = 0; $i < $entries["count"]; $i++) {
                $cn = $entries[$i]["cn"][0];
                if (preg_match('/^G_([A-Za-z0-9]+)/', $cn, $match)) {
                    $lisitry_kilasy[] = $match[1]; // Maka sy manampy ny kilasy
                }
            }
        } else {
            echo "Hadisoana fikarohana LDAP : " . ldap_error($ldapconn);
        }

        ldap_unbind($ldapconn);
    } else {
        echo "Hadisoana fifamatoana LDAP : " . ldap_error($ldapconn);
    }
}
?>

<!DOCTYPE html>
<html lang="mg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sehatry ny Mpizara - <?php echo htmlspecialchars($anarana_feno); ?></title>
    <link rel="stylesheet" href="mpizara.css">
    <link rel="stylesheet" href="fampidirana_rakitra.css">
    <link rel="stylesheet" href="ankapobe.css">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<div class="container">
<header class="profile-header">
    <div class="profile-info">
        <div class="avatar-container">
            <img src="<?php echo $lalana_sary . (str_starts_with($lalana_sary, 'data:image') ? '' : '?v=' . time()); ?>" alt="Sarin'ny profil" class="avatar" id="sarinProfil">
            <div class="status-indicator"></div>
        </div>
        <div class="user-details">
            <h1 class="user-name"><?php echo htmlspecialchars($anarana_voalohany . ' ' . $anarana_farany); ?></h1>
            <p class="username">@<?php echo htmlspecialchars($anarana_mpampiasa); ?></p>
            <div class="user-meta">
                <span class="meta-item">
                    <i class="fas fa-user-shield"></i>
                    Mpikambana ao amin'ny Scolarité
                </span>
                <span class="meta-item">
                    <i class="fas fa-cog"></i>
                    Mpizara
                </span>
            </div>
        </div>
    </div>
    <div class="header-actions">
        <a href="mpitantana_filazana.php" class="btn btn-primary">
            <i class="fas fa-history"></i>
            Tantaran'ny Filazana
        </a>
        <!-- Bokotra vaovao ho an'ny tsy fahatongavana sy ny fahatara -->
        <a href="mpitantana_tsy_fahatongavana.php" class="btn btn-warning">
            <i class="fas fa-clock"></i>
            Tsy fahatongavana/Fahatara
        </a>
        <a href="mivoaka.php" class="btn btn-danger">
            <i class="fas fa-sign-out-alt"></i>
            Fivoahana
        </a>
    </div>
</header>

    <div class="main-content">
        <div class="content-left">
            <section class="karatra">
                <div class="card-header">
                    <h3><i class="fas fa-bullhorn"></i> Handefa filazana ho an'ny mpianatra rehetra</h3>
                </div>
                <div class="card-body">
                    <form action="handefa_filazana.php" method="POST" enctype="multipart/form-data" class="form-modern">
                        <div class="form-group">
                            <label for="lohateny"><i class="fas fa-heading"></i> Lohateny ny filazana</label>
                            <input type="text" name="lohateny" id="lohateny" required placeholder="Ohatra: Fivoriana ray aman-dreny sy mpampianatra">
                        </div>
                        <div class="form-group">
                            <label for="votoaty"><i class="fas fa-file-alt"></i> Votoatin'ny hafatra</label>
                            <textarea name="votoaty" id="votoaty" rows="5" required placeholder="Soraty ny filazanareo..."></textarea>
                        </div>
                        <div class="form-group">
                            <label for="rakitra"><i class="fas fa-paperclip"></i> Hampiraikitra rakitra (tsy tsy maintsy)</label>
                            <div class="file-input-container">
                                <div class="file-input-wrapper" id="fileInputWrapper">
                                    <input type="file" name="rakitra" id="rakitra" accept=".pdf,.doc,.docx,.jpg,.png" class="file-input-hidden">
                                    <div class="file-input-content">
                                        <div class="file-input-icon">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                        </div>
                                        <div class="file-input-text">
                                            <div class="file-input-title">Tsindrio mba hisafidy rakitra</div>
                                            <div class="file-input-subtitle">na ataovy eto ny rakitrareo</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="file-info" id="fileInfo">
                                    <div class="file-info-icon">
                                        <i class="fas fa-file"></i>
                                    </div>
                                    <div class="file-info-details">
                                        <div class="file-info-name" id="fileName"></div>
                                        <div class="file-info-size" id="fileSize"></div>
                                    </div>
                                    <button type="button" class="file-info-remove" id="removeFile">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="file-constraints">
                                    <i class="fas fa-info-circle"></i>
                                    Karazana ekena: PDF, DOC, DOCX, JPG, PNG • Haben'ny rakitra: 10MB
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-full">
                            <i class="fas fa-paper-plane"></i> Alefaso ny filazana
                        </button>
                    </form>
                </div>
            </section>

            <section class="karatra">
                <div class="card-header">
                    <h3><i class="fas fa-clock"></i> Handrakitra fahatara na tsy fahatongavana</h3>
                </div>
                <div class="card-body">
                    <form action="handrakitra_tsy_fahatongavana.php" method="POST" class="form-modern">
                        <div class="form-group">
                            <label for="mpianatra"><i class="fas fa-user-graduate"></i> Anaran'ny mpampiasa an'ny mpianatra</label>
                            <input type="text" name="mpianatra" id="mpianatra" required placeholder="Ohatra: marie.dubois">
                        </div>
                        
                        <div class="form-group">
                            <label for="daty"><i class="fas fa-calendar"></i> Daty</label>
                            <input type="date" name="daty" id="daty" required>
                        </div>
                        <div class="form-group">
                            <label for="ora"><i class="fas fa-clock"></i> Ora (tsy tsy maintsy)</label>
                            <input type="time" name="ora" id="ora">
                        </div>
                        <div class="form-group">
                            <label for="karazana"><i class="fas fa-exclamation-triangle"></i> Karazana</label>
                            <select name="karazana" id="karazana" required>
                                <option value="">-- Safidio ny karazana --</option>
                                <option value="fahatara">Fahatara</option>
                                <option value="tsy_fahatongavana">Tsy fahatongavana</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="antony"><i class="fas fa-comment"></i> Antony</label>
                            <textarea name="antony" id="antony" rows="3" placeholder="Lazao ny antony (tsy tsy maintsy)..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-warning btn-full">
                            <i class="fas fa-save"></i> Tehirizo
                        </button>
                    </form>
                </div>
            </section>
            <section class="karatra">
                <div class="card-header">
                    <h3><i class="fas fa-calendar-alt"></i> Fitantanana ny fandaharam-potoana</h3>
                </div>
                <div class="card-body">
                    <form action="fitantanana_fandaharam_potoana.php" method="get" class="form-modern">
                        <div class="form-group">
                            <label for="kilasy"><i class="fas fa-users"></i> Safidio kilasy iray</label>
                            <select name="kilasy" id="kilasy" required>
                                <option value="">-- Safidio kilasy iray --</option>
                                <?php
                                foreach ($lisitry_kilasy as $kilasy) {
                                    echo "<option value=\"$kilasy\">$kilasy</option>";
                                }
                                ?>

                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-full">
                            <i class="fas fa-calendar-check"></i> Tantano ity kilasy ity
                        </button>
                    </form>
                </div>
            </section>

        </div>

        <div class="content-right">
            <section class="karatra photo-card">
                <div class="card-header">
                    <h3><i class="fas fa-camera"></i> Sarin'ny profil</h3>
                </div>
                <div class="card-body">
                    <div class="photo-upload-container">
                        <div class="current-photo">
                            <img src="<?php echo $lalana_sary . (str_starts_with($lalana_sary, 'data:image') ? '' : '?v=' . time()); ?>" alt="Sarin'ny profil" id="sarinAnkehitriny">
                            <div class="photo-overlay"><i class="fas fa-camera"></i></div>
                        </div>
                        <form action="hampiditra_sary.php" method="POST" enctype="multipart/form-data" class="upload-form">
                            <input type="file" name="sary" accept="image/*" id="sarinFampidirana" required>
                            <input type="hidden" name="miverina_any" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                            <label for="sarinFampidirana" class="btn btn-secondary">
                                <i class="fas fa-upload"></i> Ovay ny sary
                            </label>
                            <button type="submit" class="btn btn-primary" id="uploadBtn" style="display: none;">
                                <i class="fas fa-check"></i> Ekeo
                            </button>
                        </form>
                        <p class="upload-info">
                            Karazana ekena: JPG, PNG, GIF<br>
                            Haben'ny rakitra: 5MB
                        </p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
<script src="js/mpianatra.js"></script>
<script>
// Fitantanana ny fampidirana sary
document.getElementById('sarinFampidirana').addEventListener('change', function () {
  document.getElementById('uploadBtn').style.display = 'inline-block';
});

// Fitantanana ny rakitra manokana ho an'ny filazana
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('rakitra');
    const fileInputWrapper = document.getElementById('fileInputWrapper');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const removeFile = document.getElementById('removeFile');

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function updateFileDisplay(file) {
        if (file) {
            fileInputWrapper.classList.add('has-file');
            fileInfo.classList.add('show');
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            
            // Ovay ny kisary sy ny soratra
            const icon = fileInputWrapper.querySelector('.file-input-icon i');
            const title = fileInputWrapper.querySelector('.file-input-title');
            const subtitle = fileInputWrapper.querySelector('.file-input-subtitle');
            
            icon.className = 'fas fa-check-circle';
            title.textContent = 'Rakitra voasafidy';
            subtitle.textContent = 'Tsindrio mba hanova rakitra';
        } else {
            fileInputWrapper.classList.remove('has-file');
            fileInfo.classList.remove('show');
            
            // Avereno ny kisary sy ny soratra taloha
            const icon = fileInputWrapper.querySelector('.file-input-icon i');
            const title = fileInputWrapper.querySelector('.file-input-title');
            const subtitle = fileInputWrapper.querySelector('.file-input-subtitle');
            
            icon.className = 'fas fa-cloud-upload-alt';
            title.textContent = 'Tsindrio mba hisafidy rakitra';
            subtitle.textContent = 'na ataovy eto ny rakitrareo';
        }
    }

    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        updateFileDisplay(file);
    });

    removeFile.addEventListener('click', function() {
        fileInput.value = '';
        updateFileDisplay(null);
    });

    // Fanohanana ny drag-and-drop
    fileInputWrapper.addEventListener('dragover', function(e) {
        e.preventDefault();
        fileInputWrapper.style.borderColor = '#3b82f6';
        fileInputWrapper.style.backgroundColor = 'rgba(59, 130, 246, 0.1)';
    });

    fileInputWrapper.addEventListener('dragleave', function(e) {
        e.preventDefault();
        fileInputWrapper.style.borderColor = '';
        fileInputWrapper.style.backgroundColor = '';
    });

    fileInputWrapper.addEventListener('drop', function(e) {
        e.preventDefault();
        fileInputWrapper.style.borderColor = '';
        fileInputWrapper.style.backgroundColor = '';
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            updateFileDisplay(files[0]);
        }
    });
});
</script>
</body>
</html>