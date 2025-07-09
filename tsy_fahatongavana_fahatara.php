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

$vondrona_mpampiasa = isset($_SESSION['groups']) ? $_SESSION['groups'] : [];

// Fifandraisana amin'ny MySQL
$mysqli = new mysqli("localhost", "root", "Basique12345", "educonnect");
if ($mysqli->connect_error) {
    die("Hadisoana MySQL : " . $mysqli->connect_error);
}

// Kilasy
$kilasy = '';
if (in_array('G_L1G1', $vondrona_mpampiasa)) $kilasy = 'L1G1';
elseif (in_array('G_L1G2', $vondrona_mpampiasa)) $kilasy = 'L1G2';
elseif (in_array('G_L2G1', $vondrona_mpampiasa)) $kilasy = 'L2G1';
elseif (in_array('G_L2G2', $vondrona_mpampiasa)) $kilasy = 'L2G2';

// Makàna ny tsy fahatongavana sy fahatara rehetra an'ny mpianatra
$stmt_tsy_fahatongavana = $mysqli->prepare("
    SELECT id, daty, ora, karazana, antony, daty_fandraketana 
    FROM tsy_fahatongavana_fahatara 
    WHERE mpianatra = ? 
    ORDER BY daty DESC, ora DESC
");
$stmt_tsy_fahatongavana->bind_param("s", $username);
$stmt_tsy_fahatongavana->execute();
$result_tsy_fahatongavana = $stmt_tsy_fahatongavana->get_result();

$tsy_fahatongavana_rehetra = [];
$totalin_ny_tsy_fahatongavana = 0;
$isa_tsy_fahatongavana = 0;
$isa_fahatara = 0;

if ($result_tsy_fahatongavana && $result_tsy_fahatongavana->num_rows > 0) {
    while ($row = $result_tsy_fahatongavana->fetch_assoc()) {
        $tsy_fahatongavana_rehetra[] = $row;
        $totalin_ny_tsy_fahatongavana++;
        
        if ($row['karazana'] === 'tsy_fahatongavana') {
            $isa_tsy_fahatongavana++;
        } else {
            $isa_fahatara++;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="mg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tsy fahatongavana sy Fahatara - <?php echo htmlspecialchars($anarana_feno); ?></title>
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
        }
        
        .icon-antontan-isa.totaliny {
            background: var(--gradient-maitso);
        }
        
        .icon-antontan-isa.tsy-fahatongavana {
            background: var(--gradient-mena);
        }
        
        .icon-antontan-isa.fahatara {
            background: var(--gradient-mavo);
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
        
        .sandan-antontan-isa.mena {
            color: var(--loko-mena);
        }
        
        .sandan-antontan-isa.mavo {
            color: var(--loko-mavo);
        }
        
        .kaontenera-tsy-fahatongavana {
            display: flex;
            flex-direction: column;
            gap: var(--halavany-lehibe);
        }
        
        .karatra-tsy-fahatongavana {
            background: var(--loko-fotsy);
            border-radius: var(--boribory-lehibe);
            padding: var(--halavany-lehibe);
            box-shadow: var(--aloka-antonony);
            transition: var(--transition-antonony);
            border-left: 4px solid var(--loko-border);
        }
        
        .karatra-tsy-fahatongavana:hover {
            transform: translateY(-2px);
            box-shadow: var(--aloka-lehibe);
        }
        
        .karatra-tsy-fahatongavana.tsy-fahatongavana {
            border-left-color: var(--loko-mena);
            background: rgba(255, 59, 48, 0.05);
        }
        
        .karatra-tsy-fahatongavana.fahatara {
            border-left-color: var(--loko-mavo);
            background: rgba(255, 149, 0, 0.05);
        }
        
        .loha-tsy-fahatongavana {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--halavany-antonony);
            padding-bottom: var(--halavany-antonony);
            border-bottom: 1px solid var(--loko-border);
        }
        
        .fanazavana-loha-tsy-fahatongavana h3 {
            font-size: 18px;
            font-weight: 600;
            color: var(--loko-maizina);
            margin-bottom: var(--halavany-kely);
            display: flex;
            align-items: center;
            gap: var(--halavany-kely);
        }
        
        .fanazavana-loha-tsy-fahatongavana h3 i {
            color: var(--loko-mena);
        }
        
        .fanazavana-loha-tsy-fahatongavana h3 i.fahatara {
            color: var(--loko-mavo);
        }
        
        .famantarana-karazana {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 12px;
            font-weight: 500;
        }
        
        .famantarana-karazana.tsy-fahatongavana {
            background: var(--loko-mena);
            color: white;
        }
        
        .famantarana-karazana.fahatara {
            background: var(--loko-mavo);
            color: white;
        }
        
        .fampahalalana-tsy-fahatongavana {
            display: flex;
            gap: var(--halavany-lehibe);
            margin-bottom: var(--halavany-antonony);
            font-size: 14px;
            color: var(--loko-text-secondary);
        }
        
        .daty-tsy-fahatongavana,
        .ora-tsy-fahatongavana {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .antony-tsy-fahatongavana {
            color: var(--loko-maizina);
            line-height: 1.6;
            margin-bottom: var(--halavany-antonony);
            font-size: 15px;
        }
        
        .daty-fandraketana {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: var(--loko-text-secondary);
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
            
            .loha-tsy-fahatongavana {
                flex-direction: column;
                gap: var(--halavany-antonony);
                align-items: flex-start;
            }
            
            .fampahalalana-tsy-fahatongavana {
                flex-direction: column;
                gap: var(--halavany-kely);
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
                        <i class="fas fa-user-clock"></i>
                        Tsy fahatongavana sy Fahatara
                    </span>
                    <?php if ($kilasy): ?>
                        <span class="meta-zavatra">
                            <i class="fas fa-users"></i>
                            Kilasy <?php echo htmlspecialchars($kilasy); ?>
                        </span>
                    <?php endif; ?>
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
            <div class="icon-antontan-isa totaliny">
                <i class="fas fa-list"></i>
            </div>
            <div class="fanazavana-antontan-isa">
                <h3>Totaliny</h3>
                <div class="sandan-antontan-isa"><?php echo $totalin_ny_tsy_fahatongavana; ?></div>
            </div>
        </div>
        
        <div class="karatra-antontan-isa">
            <div class="icon-antontan-isa tsy-fahatongavana">
                <i class="fas fa-user-times"></i>
            </div>
            <div class="fanazavana-antontan-isa">
                <h3>Tsy fahatongavana</h3>
                <div class="sandan-antontan-isa mena"><?php echo $isa_tsy_fahatongavana; ?></div>
            </div>
        </div>
        
        <div class="karatra-antontan-isa">
            <div class="icon-antontan-isa fahatara">
                <i class="fas fa-clock"></i>
            </div>
            <div class="fanazavana-antontan-isa">
                <h3>Fahatara</h3>
                <div class="sandan-antontan-isa mavo"><?php echo $isa_fahatara; ?></div>
            </div>
        </div>
    </div>

    <!-- Tsy fahatongavana sy fahatara rehetra -->
    <div class="kaontenera-tsy-fahatongavana">
        <?php if (count($tsy_fahatongavana_rehetra) > 0): ?>
            <?php foreach ($tsy_fahatongavana_rehetra as $item): ?>
                <section class="karatra karatra-tsy-fahatongavana <?php echo $item['karazana']; ?>">
                    <div class="loha-tsy-fahatongavana">
                        <div class="fanazavana-loha-tsy-fahatongavana">
                            <h3>
                                <i class="fas fa-<?php echo $item['karazana'] === 'tsy_fahatongavana' ? 'user-times' : 'clock'; ?> <?php echo $item['karazana']; ?>"></i>
                                <?php echo $item['karazana'] === 'tsy_fahatongavana' ? 'Tsy fahatongavana' : 'Fahatara'; ?>
                            </h3>
                        </div>
                        <div class="famantarana-karazana <?php echo $item['karazana']; ?>">
                            <i class="fas fa-<?php echo $item['karazana'] === 'tsy_fahatongavana' ? 'times' : 'exclamation-triangle'; ?>"></i>
                            <?php echo $item['karazana'] === 'tsy_fahatongavana' ? 'Tsy tonga' : 'Tara'; ?>
                        </div>
                    </div>
                    <div class="votoatin-karatra">
                        <div class="fampahalalana-tsy-fahatongavana">
                            <div class="daty-tsy-fahatongavana">
                                <i class="fas fa-calendar"></i>
                                <?php echo date('d/m/Y', strtotime($item['daty'])); ?>
                            </div>
                            <div class="ora-tsy-fahatongavana">
                                <i class="fas fa-clock"></i>
                                <?php echo date('H:i', strtotime($item['ora'])); ?>
                            </div>
                        </div>
                        <?php if ($item['antony']): ?>
                            <div class="antony-tsy-fahatongavana">
                                <strong>Antony:</strong> <?php echo nl2br(htmlspecialchars($item['antony'])); ?>
                            </div>
                        <?php endif; ?>
                        <div class="daty-fandraketana">
                            <i class="fas fa-file-alt"></i>
                            Voarakitra ny <?php echo date('d/m/Y à H:i', strtotime($item['daty_fandraketana'])); ?>
                        </div>
                    </div>
                </section>
            <?php endforeach; ?>
        <?php else: ?>
            <section class="karatra">
                <div class="votoatin-karatra">
                    <div class="tsy-misy-zavatra">
                        <i class="fas fa-check-circle"></i>
                        <h4>Tsy misy tsy fahatongavana na fahatara</h4>
                        <p>Tsara! Tsy mbola misy tsy fahatongavana na fahatara voarakitra ho anao.</p>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </div>
</div>

<script>
// Fanavaozana ny pejy rehefa mandeha
document.addEventListener('DOMContentLoaded', function() {
    console.log('Pejy tsy fahatongavana sy fahatara voalohany');
});
</script>
</body>
</html>