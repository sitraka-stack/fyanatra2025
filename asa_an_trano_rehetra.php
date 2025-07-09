<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: fidirana.php");
    exit;
}

$username = $_SESSION['username'];
$anarana_feno = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : $username;
$ampahany = explode(" ", $anarana_feno);
$fanampiny = isset($ampahany[0]) ? $ampahany[0] : '';
$anarana = isset($ampahany[1]) ? $ampahany[1] : '';

$sary_path = "sary/" . $username . ".jpg";
if (!file_exists($sary_path)) {
    $litera_voalohany = strtoupper(substr($fanampiny, 0, 1) . substr($anarana, 0, 1));
    $sary_path = "data:image/svg+xml;base64," . base64_encode('
        <svg width="150" height="150" xmlns="http://www.w3.org/2000/svg">
            <circle cx="75" cy="75" r="75" fill="#9CA3AF"/>
            <text x="75" y="85" font-family="Arial, sans-serif" font-size="48" font-weight="bold" text-anchor="middle" fill="white">' . $litera_voalohany . '</text>
        </svg>
    ');
}

$vondrona_mpampiasa = isset($_SESSION['groups']) ? $_SESSION['groups'] : [];

$mysqli = new mysqli("localhost", "root", "Basique12345", "educonnect");
if ($mysqli->connect_error) {
    die("Hadisoana MySQL : " . $mysqli->connect_error);
}

$kilasy = '';
if (in_array('G_L1G1', $vondrona_mpampiasa)) $kilasy = 'L1G1';
elseif (in_array('G_L1G2', $vondrona_mpampiasa)) $kilasy = 'L1G2';
elseif (in_array('G_L2G1', $vondrona_mpampiasa)) $kilasy = 'L2G1';
elseif (in_array('G_L2G2', $vondrona_mpampiasa)) $kilasy = 'L2G2';

$stmt = $mysqli->prepare("
    SELECT 
        a.id, 
        a.lohateny, 
        a.votoaty, 
        a.daty_fampidirana, 
        a.daty_farany, 
        a.rakitra, 
        ra.rakitra_natolotra, 
        ra.daty_fanatolotrana, 
        a.taranja, 
        CASE WHEN ra.rakitra_natolotra IS NOT NULL THEN 1 ELSE 0 END as natolotra 
    FROM asa a 
    LEFT JOIN rendus_asa ra ON a.id = ra.asa_id AND ra.mpianatra = ? 
    WHERE a.kilasy = ? 
    ORDER BY a.daty_fampidirana DESC
");
$stmt->bind_param("ss", $username, $kilasy);
$stmt->execute();
$result = $stmt->get_result();

$asa_an_trano_araka_taranja = [];
$totalin_ny_asa_an_trano = 0;
$asa_an_trano_natolotra = 0;
$asa_an_trano_tara = 0;

while ($row = $result->fetch_assoc()) {
    $taranja = $row['taranja'];
    if (!isset($asa_an_trano_araka_taranja[$taranja])) {
        $asa_an_trano_araka_taranja[$taranja] = [];
    }
    $asa_an_trano_araka_taranja[$taranja][] = $row;
    $totalin_ny_asa_an_trano++;

    if ($row['natolotra']) {
        $asa_an_trano_natolotra++;
    } elseif (new DateTime($row['daty_farany']) < new DateTime()) {
        $asa_an_trano_tara++;
    }
}
?>
<!DOCTYPE html>
<html lang="mg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asa an-trano rehetra - <?php echo htmlspecialchars($anarana_feno); ?></title>
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
        
        .sandan-antontan-isa.tena-tsara {
            color: var(--loko-maitso);
        }
        
        .sandan-antontan-isa.ratsy {
            color: var(--loko-mena);
        }
        
        .fizarana-taranja {
            margin-bottom: var(--halavany-goavana);
        }
        
        .fizarana-taranja h2 {
            font-size: 24px;
            font-weight: 600;
            color: var(--loko-maizina);
            margin-bottom: var(--halavany-lehibe);
            padding-bottom: var(--halavany-antonony);
            border-bottom: 2px solid var(--loko-maitso);
        }
        
        .karatra-antsipirihan-asa-an-trano {
            background: var(--loko-fotsy);
            border-radius: var(--boribory-lehibe);
            padding: var(--halavany-lehibe);
            margin-bottom: var(--halavany-lehibe);
            box-shadow: var(--aloka-antonony);
            border-left: 4px solid var(--loko-border);
            transition: var(--transition-antonony);
        }
        
        .karatra-antsipirihan-asa-an-trano:hover {
            transform: translateX(4px);
            box-shadow: var(--aloka-lehibe);
        }
        
        .karatra-antsipirihan-asa-an-trano.natolotra {
            border-left-color: var(--loko-maitso);
            background: var(--loko-maitso-mazava);
        }
        
        .karatra-antsipirihan-asa-an-trano.tara {
            border-left-color: var(--loko-mena);
            background: rgba(255, 59, 48, 0.05);
        }
        
        .karatra-antsipirihan-asa-an-trano.miandry {
            border-left-color: var(--loko-mavo);
            background: rgba(255, 149, 0, 0.05);
        }
        
        .loha-karatra {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--halavany-antonony);
            padding-bottom: var(--halavany-antonony);
            border-bottom: 1px solid var(--loko-border);
        }
        
        .fanazavana-loha-asa-an-trano h3 {
            font-size: 18px;
            font-weight: 600;
            color: var(--loko-maizina);
            margin-bottom: 8px;
        }
        
        .toe-javatra-asa-an-trano {
            display: flex;
            gap: var(--halavany-kely);
        }
        
        .famantarana-toe-javatra {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 12px;
            font-weight: 500;
        }
        
        .famantarana-toe-javatra.natolotra {
            background: var(--loko-maitso);
            color: white;
        }
        
        .famantarana-toe-javatra.tara {
            background: var(--loko-mena);
            color: white;
        }
        
        .famantarana-toe-javatra.miandry {
            background: var(--loko-mavo);
            color: white;
        }
        
        .votoatin-asa-an-trano {
            color: var(--loko-text-secondary);
            margin-bottom: var(--halavany-antonony);
            line-height: 1.5;
        }
        
        .daty-asa-an-trano {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: var(--halavany-antonony);
            font-size: 13px;
            color: var(--loko-text-secondary);
        }
        
        .daty-asa-an-trano-zavatra,
        .fe-fotoana-asa-an-trano {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .fe-fotoana-asa-an-trano.tara {
            color: var(--loko-mena);
            font-weight: 600;
        }
        
        .hetsika-asa-an-trano {
            display: flex;
            gap: var(--halavany-kely);
            flex-wrap: wrap;
        }
        
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            backdrop-filter: blur(5px);
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--halavany-lehibe);
            border-bottom: 1px solid var(--loko-border);
        }
        
        .modal-header h3 {
            display: flex;
            align-items: center;
            gap: var(--halavany-kely);
            color: var(--loko-maizina);
            font-size: 18px;
            font-weight: 600;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--loko-text-secondary);
            padding: 8px;
            border-radius: var(--boribory-antonony);
            transition: var(--transition-haingana);
        }
        
        .modal-close:hover {
            background: var(--loko-volom-bary);
            color: var(--loko-maizina);
        }
        
        .modal-body {
            padding: var(--halavany-lehibe);
        }
        
        .karatra-fanazavana-asa-an-trano {
            background: var(--loko-volom-bary);
            border-radius: var(--boribory-antonony);
            padding: var(--halavany-antonony);
            margin-bottom: var(--halavany-antonony);
        }
        
        .karatra-fanazavana-asa-an-trano h4 {
            color: var(--loko-maizina);
            margin-bottom: 8px;
            font-size: 16px;
            font-weight: 600;
        }
        
        .karatra-fanazavana-asa-an-trano p {
            color: var(--loko-text-secondary);
            font-size: 14px;
            line-height: 1.5;
        }
        
        .modal-actions {
            display: flex;
            gap: var(--halavany-antonony);
            justify-content: flex-end;
            margin-top: var(--halavany-lehibe);
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
            
            .daty-asa-an-trano {
                flex-direction: column;
                gap: 8px;
            }
            
            .hetsika-asa-an-trano {
                flex-direction: column;
            }
            
            .modal-content {
                width: 95%;
                margin: var(--halavany-kely);
            }
            
            .modal-actions {
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
                        <span class="meta-zavatra"><i class="fas fa-tasks"></i> Asa an-trano rehetra</span>
                        <?php if ($kilasy): ?>
                            <span class="meta-zavatra"><i class="fas fa-users"></i> Kilasy <?php echo htmlspecialchars($kilasy); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="hetsika-loha">
                <a href="mpianatra.php" class="btn"><i class="fas fa-arrow-left"></i> Hiverina</a>
                <a href="fivoahana.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Fivoahana</a>
            </div>
        </header>

        <div class="antontan-isa-container">
            <div class="karatra-antontan-isa">
                <div class="icon-antontan-isa"><i class="fas fa-tasks"></i></div>
                <div class="fanazavana-antontan-isa">
                    <h3>Totalin'ny asa an-trano</h3>
                    <div class="sandan-antontan-isa"><?php echo $totalin_ny_asa_an_trano; ?></div>
                </div>
            </div>
            <div class="karatra-antontan-isa">
                <div class="icon-antontan-isa"><i class="fas fa-check-circle"></i></div>
                <div class="fanazavana-antontan-isa">
                    <h3>Asa an-trano natolotra</h3>
                    <div class="sandan-antontan-isa tena-tsara"><?php echo $asa_an_trano_natolotra; ?></div>
                </div>
            </div>
            <div class="karatra-antontan-isa">
                <div class="icon-antontan-isa"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="fanazavana-antontan-isa">
                    <h3>Tara</h3>
                    <div class="sandan-antontan-isa ratsy"><?php echo $asa_an_trano_tara; ?></div>
                </div>
            </div>
            <div class="karatra-antontan-isa">
                <div class="icon-antontan-isa"><i class="fas fa-clock"></i></div>
                <div class="fanazavana-antontan-isa">
                    <h3>Hatolotra</h3>
                    <div class="sandan-antontan-isa"><?php echo $totalin_ny_asa_an_trano - $asa_an_trano_natolotra - $asa_an_trano_tara; ?></div>
                </div>
            </div>
        </div>

        <div class="kaontenera-lisitry-ny-asa-an-trano">
            <?php if (count($asa_an_trano_araka_taranja) > 0): ?>
                <?php foreach ($asa_an_trano_araka_taranja as $taranja => $asa_an_trano_list): ?>
                    <section class="fizarana-taranja">
                        <h2><?php echo htmlspecialchars($taranja); ?></h2>
                        <?php foreach ($asa_an_trano_list as $asa): ?>
                            <?php
                            $tara = new DateTime($asa['daty_farany']) < new DateTime() && !$asa['natolotra'];
                            $kilasy_toe_javatra = $asa['natolotra'] ? 'natolotra' : ($tara ? 'tara' : 'miandry');
                            $fe_fotoana_lasa = new DateTime($asa['daty_farany']) < new DateTime();
                            ?>
                            <section class="karatra karatra-antsipirihan-asa-an-trano <?php echo $kilasy_toe_javatra; ?>">
                                <div class="loha-karatra">
                                    <div class="fanazavana-loha-asa-an-trano">
                                        <h3><?php echo htmlspecialchars($asa['lohateny']); ?></h3>
                                        <div class="toe-javatra-asa-an-trano">
                                            <?php if ($asa['natolotra']): ?>
                                                <span class="famantarana-toe-javatra natolotra"><i class="fas fa-check-circle"></i> Natolotra ny <?php echo date('d/m/Y à H:i', strtotime($asa['daty_fanatolotrana'])); ?></span>
                                            <?php elseif ($tara): ?>
                                                <span class="famantarana-toe-javatra tara"><i class="fas fa-times-circle"></i> Tara</span>
                                            <?php else: ?>
                                                <span class="famantarana-toe-javatra miandry"><i class="fas fa-clock"></i> Hatolotra</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="votoatin-karatra">
                                    <div class="votoatin-asa-an-trano">
                                        <p><?php echo nl2br(htmlspecialchars($asa['votoaty'])); ?></p>
                                    </div>
                                    <div class="daty-asa-an-trano">
                                        <div class="daty-asa-an-trano-zavatra"><i class="fas fa-calendar"></i> Noforonina ny <?php echo date('d/m/Y à H:i', strtotime($asa['daty_fampidirana'])); ?></div>
                                        <div class="fe-fotoana-asa-an-trano <?php echo $tara ? 'tara' : ''; ?>">
                                            <i class="fas fa-hourglass-end"></i> Hatolotra alohan'ny <?php echo date('d/m/Y à H:i', strtotime($asa['daty_farany'])); ?>
                                        </div>
                                    </div>
                                    <div class="hetsika-asa-an-trano">
                                        <?php if ($asa['rakitra']): ?>
                                            <a href="rakitra_atokana/<?php echo htmlspecialchars($asa['rakitra']); ?>" target="_blank" class="btn btn-secondary">
                                                <i class="fas fa-paperclip"></i> Halaina ny rakitra atokana
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!$asa['natolotra']): ?>
                                            <?php if ($fe_fotoana_lasa): ?>
                                                <button class="btn btn-secondary" disabled style="opacity: 0.6; cursor: not-allowed;" onclick="alert('Lasa ny fe-fotoana hanatolotrana ity asa ity.')">
                                                    <i class="fas fa-clock"></i> Lasa ny fe-fotoana
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-primary" onclick="asehoyModalTolona(<?php echo $asa['id']; ?>, '<?php echo htmlspecialchars($asa['lohateny'], ENT_QUOTES); ?>', '<?php echo $asa['daty_farany']; ?>')" data-deadline="<?php echo $asa['daty_farany']; ?>">
                                                    <i class="fas fa-upload"></i> Hatolotra ny asa
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
                    <div class="votoatin-karatra">
                        <p class="tsy-misy-zavatra">Tsy mbola misy asa an-trano nomena ho an'ny kilasy.</p>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal ho an'ny fanatolotrana asa an-trano -->
    <div id="modalTolona" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-upload"></i> Hatolotra ny asa an-trano</h3>
                <button class="modal-close" onclick="hidyModalTolona()">×</button>
            </div>
            <div class="modal-body">
                <div id="fanazavana-asa-an-trano"></div>
                <form id="taratasyTolona" action="atolotra_asa.php" method="POST" enctype="multipart/form-data" class="form-modern">
                    <input type="hidden" name="asa_id" id="asa_id">
                    <div class="form-group">
                        <label for="rakitra_natolotra"><i class="fas fa-file"></i> Safidio ny rakitra hatolotra:</label>
                        <input type="file" name="rakitra_natolotra" id="rakitra_natolotra" required>
                        <p class="fanazavana-kely">Karazana azo ekena: PDF, DOC, DOCX, TXT, JPG, PNG, ZIP<br>Haben'ny rakitra: 10MB</p>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="hidyModalTolona()">Hanajanona</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Hatolotra
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let id_asa_an_trano_ankehitriny = null;

        function asehoyModalTolona(idAsaAnTrano, lohateny, fePootoana) {
            id_asa_an_trano_ankehitriny = idAsaAnTrano;
            
            document.getElementById('fanazavana-asa-an-trano').innerHTML = `
                <div class="karatra-fanazavana-asa-an-trano">
                    <h4>${lohateny}</h4>
                    <p>Safidio ny rakitra tianao hatolotra ho an'ity asa an-trano ity.</p>
                    <p><strong>Fe-potoana:</strong> ${fePootoana}</p>
                </div>
            `;
            
            document.getElementById('asa_id').value = idAsaAnTrano;
            document.getElementById('modalTolona').style.display = 'flex';
        }

        function hidyModalTolona() {
            document.getElementById('modalTolona').style.display = 'none';
            document.getElementById('taratasyTolona').reset();
            id_asa_an_trano_ankehitriny = null;
        }

        document.addEventListener('click', function(e) {
            const modal = document.getElementById('modalTolona');
            if (e.target === modal) {
                hidyModalTolona();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hidyModalTolona();
            }
        });
    </script>
</body>
</html>