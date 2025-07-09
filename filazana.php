<?php
session_start();

// ✅ FANARAHA-MASO NY FIDIRANA - Ny mpianatra ihany no afaka miditra
require_once 'check_access.php';
checkAccess(['G_Tous_Eleves']);

if (!isset($_SESSION['username'])) {
    header("Location: fidirana.php");
    exit;
}

$username = $_SESSION['username'];
$anarana_feno = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : $username;
$ampahany = explode(" ", $anarana_feno);
$fanampiny = isset($ampahany[0]) ? $ampahany[0] : '';
$anarana = isset($ampahany[1]) ? $ampahany[1] : '';

// Sary miaraka amin'ny avatar default
$extensions = ['jpg', 'png', 'gif'];
$sary_path = '';
foreach ($extensions as $ext) {
    if (file_exists("sary/$username.$ext")) {
        $sary_path = "sary/$username.$ext";
        break;
    }
}

if (!$sary_path) {
    $litera_voalohany = strtoupper(substr($fanampiny, 0, 1) . substr($anarana, 0, 1));
    $sary_path = "data:image/svg+xml;base64," . base64_encode('
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

// Makàna ny filazana rehetra
$stmt_filazana = $mysqli->prepare("SELECT id, lohateny, votoaty, rakitra, daty_fandefasana, mpandefa FROM filazana ORDER BY daty_fandefasana DESC");
$stmt_filazana->execute();
$result_filazana = $stmt_filazana->get_result();

$filazana_rehetra = [];
$totalin_ny_filazana = 0;

if ($result_filazana && $result_filazana->num_rows > 0) {
    while ($row = $result_filazana->fetch_assoc()) {
        $filazana_rehetra[] = $row;
        $totalin_ny_filazana++;
    }
}
?>
<!DOCTYPE html>
<html lang="mg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filazana - <?php echo htmlspecialchars($anarana_feno); ?></title>
    <link rel="stylesheet" href="ankapobe.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .loha-mpianatra {
            background: linear-gradient(135deg, #34c759 0%, #30d158 100%);
            color: white;
            padding: var(--halavany-tena-lehibe);
            border-radius: var(--boribory-lehibe);
            margin-bottom: var(--halavany-tena-lehibe);
            box-shadow: var(--aloka-lehibe);
        }
        
        .info-mpianatra {
            display: flex;
            align-items: center;
            gap: var(--halavany-lehibe);
            margin-bottom: var(--halavany-lehibe);
        }
        
        .sary-mpianatra {
            width: 80px;
            height: 80px;
            border-radius: var(--boribory-feno);
            border: 3px solid rgba(255, 255, 255, 0.3);
            object-fit: cover;
        }
        
        .antsipirihan-mpianatra h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: var(--halavany-kely);
        }
        
        .antsipirihan-mpianatra p {
            opacity: 0.9;
            font-size: 15px;
            margin-bottom: var(--halavany-kely);
        }
        
        .meta-mpianatra {
            display: flex;
            gap: var(--halavany-antonony);
            flex-wrap: wrap;
        }
        
        .meta-zavatra {
            background: rgba(255, 255, 255, 0.2);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .hetsika-loha {
            display: flex;
            gap: var(--halavany-antonony);
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .hetsika-loha .btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .hetsika-loha .btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .hetsika-loha .btn-danger {
            background: rgba(255, 59, 48, 0.8);
        }
        
        .antontan-isa-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--halavany-lehibe);
            margin-bottom: var(--halavany-tena-lehibe);
        }
        
        .karatra-antontan-isa {
            background: var(--loko-fotsy);
            border-radius: var(--boribory-lehibe);
            padding: var(--halavany-lehibe);
            box-shadow: var(--aloka-antonony);
            display: flex;
            align-items: center;
            gap: var(--halavany-antonony);
            transition: var(--transition-antonony);
        }
        
        .karatra-antontan-isa:hover {
            transform: translateY(-2px);
            box-shadow: var(--aloka-lehibe);
        }
        
        .icon-antontan-isa {
            width: 48px;
            height: 48px;
            border-radius: var(--boribory-antonony);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
            background: var(--gradient-maitso);
        }
        
        .fanazavana-antontan-isa h3 {
            font-size: 14px;
            font-weight: 500;
            color: var(--loko-text-secondary);
            margin-bottom: 4px;
        }
        
        .sandan-antontan-isa {
            font-size: 24px;
            font-weight: 700;
            color: var(--loko-maizina);
        }
        
        .kaontenera-filazana {
            display: flex;
            flex-direction: column;
            gap: var(--halavany-lehibe);
        }
        
        .karatra-filazana {
            background: var(--loko-fotsy);
            border-radius: var(--boribory-lehibe);
            padding: var(--halavany-lehibe);
            box-shadow: var(--aloka-antonony);
            transition: var(--transition-antonony);
            border-left: 4px solid var(--loko-maitso);
        }
        
        .karatra-filazana:hover {
            transform: translateY(-2px);
            box-shadow: var(--aloka-lehibe);
        }
        
        .karatra-filazana.vaovao {
            border-left-color: var(--loko-manga);
            background: var(--loko-manga-malemy);
        }
        
        .loha-filazana {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: var(--halavany-antonony);
            padding-bottom: var(--halavany-antonony);
            border-bottom: 1px solid var(--loko-border);
        }
        
        .loha-filazana h3 {
            font-size: 20px;
            font-weight: 600;
            color: var(--loko-maizina);
            margin-bottom: var(--halavany-kely);
            display: flex;
            align-items: center;
            gap: var(--halavany-kely);
        }
        
        .loha-filazana h3 i {
            color: var(--loko-maitso);
        }
        
        .fampahalalana-filazana {
            display: flex;
            flex-direction: column;
            gap: 4px;
            font-size: 13px;
            color: var(--loko-text-secondary);
        }
        
        .mpandefa-filazana {
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
        }
        
        .daty-filazana {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .votoatin-filazana {
            color: var(--loko-maizina);
            line-height: 1.6;
            margin-bottom: var(--halavany-antonony);
            font-size: 15px;
        }
        
        .hetsika-filazana {
            display: flex;
            gap: var(--halavany-kely);
            flex-wrap: wrap;
        }
        
        .famantarana-vaovao {
            background: var(--loko-manga);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .tsy-misy-zavatra {
            text-align: center;
            padding: var(--halavany-goavana);
            color: var(--loko-text-secondary);
        }
        
        .tsy-misy-zavatra i {
            font-size: 64px;
            margin-bottom: var(--halavany-lehibe);
            opacity: 0.5;
        }
        
        .tsy-misy-zavatra h4 {
            font-size: 21px;
            font-weight: 600;
            margin-bottom: var(--halavany-kely);
            color: var(--loko-maizina);
        }
        
        .tsy-misy-zavatra p {
            font-size: 15px;
            margin-bottom: var(--halavany-lehibe);
        }
        
        @media (max-width: 768px) {
            .info-mpianatra {
                flex-direction: column;
                text-align: center;
            }
            
            .hetsika-loha {
                flex-direction: column;
                align-items: stretch;
            }
            
            .antontan-isa-container {
                grid-template-columns: 1fr;
            }
            
            .loha-filazana {
                flex-direction: column;
                gap: var(--halavany-antonony);
                align-items: flex-start;
            }
            
            .hetsika-filazana {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <header class="loha-mpianatra">
        <div class="info-mpianatra">
            <img src="<?php echo $sary_path; ?>" alt="Sary mombamomba" class="sary-mpianatra">
            <div class="antsipirihan-mpianatra">
                <h1><?php echo htmlspecialchars($fanampiny . ' ' . $anarana); ?></h1>
                <p>@<?php echo htmlspecialchars($username); ?></p>
                <div class="meta-mpianatra">
                    <span class="meta-zavatra">
                        <i class="fas fa-bullhorn"></i>
                        Filazana
                    </span>
                </div>
            </div>
        </div>
        <div class="hetsika-loha">
            <a href="mpianatra.php" class="btn">
                <i class="fas fa-arrow-left"></i>
                Hiverina
            </a>
            <a href="fivoahana.php" class="btn btn-danger">
                <i class="fas fa-sign-out-alt"></i>
                Fivoahana
            </a>
        </div>
    </header>

    <!-- Antontan-isa ankapobeny -->
    <div class="antontan-isa-container">
        <div class="karatra-antontan-isa">
            <div class="icon-antontan-isa">
                <i class="fas fa-bullhorn"></i>
            </div>
            <div class="fanazavana-antontan-isa">
                <h3>Totalin'ny filazana</h3>
                <div class="sandan-antontan-isa"><?php echo $totalin_ny_filazana; ?></div>
            </div>
        </div>
        
        <div class="karatra-antontan-isa">
            <div class="icon-antontan-isa">
                <i class="fas fa-eye"></i>
            </div>
            <div class="fanazavana-antontan-isa">
                <h3>Novakiana</h3>
                <div class="sandan-antontan-isa" id="isa-novakiana">0</div>
            </div>
        </div>
        
        <div class="karatra-antontan-isa">
            <div class="icon-antontan-isa">
                <i class="fas fa-bell"></i>
            </div>
            <div class="fanazavana-antontan-isa">
                <h3>Tsy mbola novakiana</h3>
                <div class="sandan-antontan-isa" id="isa-tsy-novakiana"><?php echo $totalin_ny_filazana; ?></div>
            </div>
        </div>
    </div>

    <!-- Filazana rehetra -->
    <div class="kaontenera-filazana">
        <?php if (count($filazana_rehetra) > 0): ?>
            <?php foreach ($filazana_rehetra as $filazana): ?>
                <section class="karatra karatra-filazana" data-filazana-id="<?php echo $filazana['id']; ?>">
                    <div class="loha-filazana">
                        <div class="fanazavana-loha-filazana">
                            <h3>
                                <i class="fas fa-bullhorn"></i>
                                <?php echo htmlspecialchars($filazana['lohateny']); ?>
                            </h3>
                            <div class="fampahalalana-filazana">
                                <div class="mpandefa-filazana">
                                    <i class="fas fa-user"></i>
                                    Nandefa: <?php echo htmlspecialchars($filazana['mpandefa']); ?>
                                </div>
                                <div class="daty-filazana">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('d/m/Y à H:i', strtotime($filazana['daty_fandefasana'])); ?>
                                </div>
                            </div>
                        </div>
                        <div class="famantarana-vaovao" id="badge-<?php echo $filazana['id']; ?>" style="display: none;">
                            <i class="fas fa-star"></i>
                            Vaovao
                        </div>
                    </div>
                    <div class="votoatin-karatra">
                        <div class="votoatin-filazana">
                            <?php echo nl2br(htmlspecialchars($filazana['votoaty'])); ?>
                        </div>
                        <div class="hetsika-filazana">
                            <?php if ($filazana['rakitra']): ?>
                                <a href="rakitra_filazana/<?php echo htmlspecialchars($filazana['rakitra']); ?>" target="_blank" class="btn btn-secondary">
                                    <i class="fas fa-paperclip"></i>
                                    Halaina ny rakitra
                                </a>
                            <?php endif; ?>
                            <button class="btn btn-primary" onclick="markahoVakiana(<?php echo $filazana['id']; ?>)">
                                <i class="fas fa-check"></i>
                                Markahy ho vakiana
                            </button>
                        </div>
                    </div>
                </section>
            <?php endforeach; ?>
        <?php else: ?>
            <section class="karatra">
                <div class="votoatin-karatra">
                    <div class="tsy-misy-zavatra">
                        <i class="fas fa-bullhorn"></i>
                        <h4>Tsy misy filazana</h4>
                        <p>Tsy mbola misy filazana nampidirina.</p>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </div>
</div>

<script>
// Fitahirizana ny filazana novakiana ao amin'ny localStorage
let filazana_novakiana = JSON.parse(localStorage.getItem('filazana_novakiana') || '[]');

// Fanavaozana ny antontan-isa
function havaozinyAntontan_isa() {
    const totaliny = <?php echo $totalin_ny_filazana; ?>;
    const novakiana = filazana_novakiana.length;
    const tsy_novakiana = Math.max(0, totaliny - novakiana);
    
    document.getElementById('isa-novakiana').textContent = novakiana;
    document.getElementById('isa-tsy-novakiana').textContent = tsy_novakiana;
}

// Fanamarihana filazana ho vakiana
function markahoVakiana(filazana_id) {
    if (!filazana_novakiana.includes(filazana_id)) {
        filazana_novakiana.push(filazana_id);
        localStorage.setItem('filazana_novakiana', JSON.stringify(filazana_novakiana));
        
        // Asehoy ny fampandrenesana
        asehoyFampandrenesana('✅ Voamariky ho vakiana ny filazana', 'fahombiazana');
        
        // Havaozina ny antontan-isa
        havaozinyAntontan_isa();
        
        // Esory ny badge vaovao
        const badge = document.getElementById('badge-' + filazana_id);
        if (badge) {
            badge.style.display = 'none';
        }
        
        // Ovay ny loko karatra
        const karatra = document.querySelector(`[data-filazana-id="${filazana_id}"]`);
        if (karatra) {
            karatra.classList.remove('vaovao');
        }
    }
}

// Fampisehoana fampandrenesana
function asehoyFampandrenesana(hafatra, karazana) {
    const fampandrenesana = document.createElement('div');
    fampandrenesana.className = `fampandrenesana fampandrenesana-${karazana}`;
    fampandrenesana.innerHTML = `
        <i class="fas fa-${karazana === 'fahombiazana' ? 'check' : 'exclamation'}"></i>
        ${hafatra}
    `;
    
    document.body.appendChild(fampandrenesana);
    
    setTimeout(() => {
        fampandrenesana.remove();
    }, 3000);
}

// Fanavaozana ny filazana vaovao rehefa mandeha ny pejy
document.addEventListener('DOMContentLoaded', function() {
    havaozinyAntontan_isa();
    
    // Asehoy ny badge vaovao ho an'ny filazana tsy mbola novakiana
    <?php foreach ($filazana_rehetra as $filazana): ?>
        if (!filazana_novakiana.includes(<?php echo $filazana['id']; ?>)) {
            const badge = document.getElementById('badge-<?php echo $filazana['id']; ?>');
            const karatra = document.querySelector('[data-filazana-id="<?php echo $filazana['id']; ?>"]');
            if (badge) badge.style.display = 'flex';
            if (karatra) karatra.classList.add('vaovao');
        }
    <?php endforeach; ?>
});
</script>
</body>
</html>