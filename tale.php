<?php
session_start();

// Fanamarinam-pidirana - raha tsy misy session na tsy mety ny vondrona
if (!isset($_SESSION['username']) || !isset($_SESSION['groups']) || !in_array("G_Admin_Direction", $_SESSION['groups'])) {
    header("Location: fidirana.php");
    exit;
}

// Fampidirana ny angon-drakitra LDAP
$ldap_host = "192.168.40.132";
$ldap_domain = "Educonnect.mg";
$search_base = "DC=Educonnect,DC=mg";

// Asa ho an'ny fikarohana mpampiasa ao amin'ny AD
function fakaAngonDrakitraMpampiasa($ldap_conn, $search_base) {
    $mpampianatra = [];
    $mpianatra = [];
    
    try {
        // Fikarohana ny vondrona G_Tous_Professeurs
        $filter_prof = "(cn=G_Tous_Professeurs)";
        $result_prof = @ldap_search($ldap_conn, "OU=Groupes," . $search_base, $filter_prof, ["member"]);
        
        if ($result_prof) {
            $entries_prof = ldap_get_entries($ldap_conn, $result_prof);
            
            if ($entries_prof["count"] > 0 && isset($entries_prof[0]["member"])) {
                for ($i = 0; $i < $entries_prof[0]["member"]["count"]; $i++) {
                    $member_dn = $entries_prof[0]["member"][$i];
                    
                    // Faka ny angon-drakitra ny mpampianatra
                    $user_result = @ldap_read($ldap_conn, $member_dn, "(objectClass=*)", ["cn", "sAMAccountName", "mail", "memberOf"]);
                    
                    if ($user_result) {
                        $user_entries = ldap_get_entries($ldap_conn, $user_result);
                        
                        if ($user_entries["count"] > 0) {
                            $user = $user_entries[0];
                            $anarana_feno = isset($user["cn"][0]) ? $user["cn"][0] : $user["samaccountname"][0];
                            $anarana_mpampiasa = $user["samaccountname"][0];
                            $mailaka = isset($user["mail"][0]) ? $user["mail"][0] : "";
                            
                            // Fikarohana ny taranja
                            $taranja = "Tsy voafaritra";
                            if (isset($user["memberof"])) {
                                for ($j = 0; $j < $user["memberof"]["count"]; $j++) {
                                    if (preg_match("/CN=G_([^_,]+)/", $user["memberof"][$j], $matches)) {
                                        $group_name = $matches[1];
                                        if (!in_array("G_" . $group_name, ['G_Tous_Professeurs', 'G_Tous_Eleves', 'G_Tous_Personnel_Admin', 'G_Admin_Direction'])) {
                                            $taranja = $group_name;
                                            break;
                                        }
                                    }
                                }
                            }
                            
                            $mpampianatra[] = [
                                'anarana_feno' => $anarana_feno,
                                'anarana_mpampiasa' => $anarana_mpampiasa,
                                'mailaka' => $mailaka,
                                'taranja' => $taranja
                            ];
                        }
                    }
                }
            }
        }
        
        // Fikarohana ny vondrona G_Tous_Eleves
        $filter_eleve = "(cn=G_Tous_Eleves)";
        $result_eleve = @ldap_search($ldap_conn, "OU=Groupes," . $search_base, $filter_eleve, ["member"]);
        
        if ($result_eleve) {
            $entries_eleve = ldap_get_entries($ldap_conn, $result_eleve);
            
            if ($entries_eleve["count"] > 0 && isset($entries_eleve[0]["member"])) {
                for ($i = 0; $i < $entries_eleve[0]["member"]["count"]; $i++) {
                    $member_dn = $entries_eleve[0]["member"][$i];
                    
                    // Faka ny angon-drakitra ny mpianatra
                    $user_result = @ldap_read($ldap_conn, $member_dn, "(objectClass=*)", ["cn", "sAMAccountName", "mail", "memberOf"]);
                    
                    if ($user_result) {
                        $user_entries = ldap_get_entries($ldap_conn, $user_result);
                        
                        if ($user_entries["count"] > 0) {
                            $user = $user_entries[0];
                            $anarana_feno = isset($user["cn"][0]) ? $user["cn"][0] : $user["samaccountname"][0];
                            $anarana_mpampiasa = $user["samaccountname"][0];
                            $mailaka = isset($user["mail"][0]) ? $user["mail"][0] : "";
                            
                            // Fikarohana ny kilasy
                            $kilasy = "Tsy voafaritra";
                            if (isset($user["memberof"])) {
                                for ($j = 0; $j < $user["memberof"]["count"]; $j++) {
                                    if (preg_match("/CN=G_(L[0-9]+G[0-9]+)/", $user["memberof"][$j], $matches)) {
                                        $kilasy = $matches[1];
                                        break;
                                    }
                                }
                            }
                            
                            $mpianatra[] = [
                                'anarana_feno' => $anarana_feno,
                                'anarana_mpampiasa' => $anarana_mpampiasa,
                                'mailaka' => $mailaka,
                                'kilasy' => $kilasy
                            ];
                        }
                    }
                }
            }
        }
        
    } catch (Exception $e) {
        error_log("Hadisoana LDAP: " . $e->getMessage());
    }
    
    return ['mpampianatra' => $mpampianatra, 'mpianatra' => $mpianatra];
}

// Fifandraisana amin'ny LDAP
$ldap_conn = ldap_connect($ldap_host);
$angon_drakitra = ['mpampianatra' => [], 'mpianatra' => []];

if ($ldap_conn) {
    ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
    ldap_set_option($ldap_conn, LDAP_OPT_NETWORK_TIMEOUT, 10);
    
    // Fampiasana ny mari-pamantarana avy amin'ny session na service account
    $bind_user = $_SESSION['username'] . "@" . $ldap_domain;
    $bind_password = ""; // Raha tsy misy teny miafina voatahiry, ampiasao service account
    
    // Raha tsy misy teny miafina, ampiasao service account
    if (empty($bind_password)) {
        $bind_user = "EDUCONNECT\\ElimProf"; // Service account
        $bind_password = "12345Orion";
    }
    
    if (@ldap_bind($ldap_conn, $bind_user, $bind_password)) {
        $angon_drakitra = fakaAngonDrakitraMpampiasa($ldap_conn, $search_base);
    } else {
        error_log("Tsy afaka nifandray tamin'ny LDAP: " . ldap_error($ldap_conn));
    }
    
    ldap_close($ldap_conn);
} else {
    error_log("Tsy afaka nifandray tamin'ny serveur LDAP");
}

$isan_mpampianatra = count($angon_drakitra['mpampianatra']);
$isan_mpianatra = count($angon_drakitra['mpianatra']);
?>

<!DOCTYPE html>
<html lang="mg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sehatry ny Tale - EduConnect</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Fomba fanao CSS -->
    <link rel="stylesheet" href="ankapobe.css">
    <link rel="stylesheet" href="fomba_fanao_tale.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-school"></i>
                EduConnect - Sehatry ny Tale
            </a>
            <div class="ms-auto">
                <span class="me-3 text-muted">
                    <i class="fas fa-user-tie me-1"></i>
                    <?php echo htmlspecialchars($_SESSION['fullname']); ?>
                </span>
                <a href="mivoaka.php" class="btn btn-logout">
                <a href="mivoaka.php" class="btn btn-hivoaka">
                    <i class="fas fa-sign-out-alt me-1"></i>
                    Hivoaka
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <!-- Carte d'accueil -->
        <div class="karatra-tongasoa">
            <h1 class="lohateny-tongasoa">
                <i class="fas fa-crown me-2"></i>
                Tongasoa, <?php echo htmlspecialchars($_SESSION['fullname']); ?>
            </h1>
            <p class="subtitle-tongasoa">
                <i class="fas fa-briefcase me-1"></i>
                Tale ny sekoly - Sehatry ny fitantanana sy ny fanaraha-maso
            </p>
        </div>

        <!-- Statistiques -->
        <div class="row">
            <div class="col-md-6">
                <div class="karatra-statistika mpampianatra position-relative" onclick="showTab('mpampianatra')">
                    <i class="fas fa-chalkboard-teacher sary-statistika"></i>
                    <div class="isa-statistika"><?php echo $isan_mpampianatra; ?></div>
                    <div class="label-statistika">
                        <i class="fas fa-users me-1"></i>
                        Mpampianatra
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="karatra-statistika mpianatra position-relative" onclick="showTab('mpianatra')">
                    <i class="fas fa-user-graduate sary-statistika"></i>
                    <div class="isa-statistika"><?php echo $isan_mpianatra; ?></div>
                    <div class="label-statistika">
                        <i class="fas fa-users me-1"></i>
                        Mpianatra
                    </div>
                </div>
            </div>
        </div>

        <!-- Zone de contenu -->
        <div class="faritra-votoaty">
            <!-- Message d'erreur si problème LDAP -->
            <?php if ($isan_mpampianatra == 0 && $isan_mpianatra == 0): ?>
                <div class="hafatra-hadisoana">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Fampandrenesana:</strong> Mety misy olana amin'ny fifandraisana amin'ny Active Directory. 
                    Azafady, hamarino ny fifandraisana na mifandraisa amin'ny mpitantana ny rafitra.
                </div>
            <?php endif; ?>

            <!-- Onglets -->
            <ul class="nav nav-tabs" id="mainTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="mpampianatra-tab" data-bs-toggle="tab" data-bs-target="#mpampianatra-pane" type="button" role="tab">
                        <i class="fas fa-chalkboard-teacher me-1"></i>
                        Mpampianatra (<?php echo $isan_mpampianatra; ?>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="mpianatra-tab" data-bs-toggle="tab" data-bs-target="#mpianatra-pane" type="button" role="tab">
                        <i class="fas fa-user-graduate me-1"></i>
                        Mpianatra (<?php echo $isan_mpianatra; ?>)
                    </button>
                </li>
            </ul>

            <!-- Contenu des onglets -->
            <div class="tab-content" id="mainTabsContent">
                <!-- Onglet Mpampianatra -->
                <div class="tab-pane fade show active" id="mpampianatra-pane" role="tabpanel">
                    <div class="mt-3">
                        <?php if (!empty($angon_drakitra['mpampianatra'])): ?>
                            <div class="boaty-fikarohana">
                                <i class="fas fa-search"></i>
                                <input type="text" class="form-control" id="searchMpampianatra" placeholder="Karohy mpampianatra...">
                            </div>
                        <?php endif; ?>
                        
                        <?php if (empty($angon_drakitra['mpampianatra'])): ?>
                            <div class="empty-state">
                                <i class="fas fa-chalkboard-teacher"></i>
                                <h4>Tsy misy mpampianatra hita</h4>
                                <p>Tsy misy mpampianatra voasoratra ao amin'ny Active Directory na misy olana amin'ny fifandraisana</p>
                            </div>
                        <?php else: ?>
                            <div class="tabilao-responsive">
                                <table class="tabilao" id="tableMpampianatra">
                                    <thead>
                                        <tr>
                                            <th><i class="fas fa-user me-1"></i>Anarana feno</th>
                                            <th><i class="fas fa-id-card me-1"></i>Anaran'ny mpampiasa</th>
                                            <th><i class="fas fa-envelope me-1"></i>Mailaka</th>
                                            <th><i class="fas fa-book me-1"></i>Taranja</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($angon_drakitra['mpampianatra'] as $mpampianatra): ?>
                                            <tr>
                                                <td>
                                                    <i class="fas fa-user-tie me-2 text-success"></i>
                                                    <?php echo htmlspecialchars($mpampianatra['anarana_feno']); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($mpampianatra['anarana_mpampiasa']); ?></td>
                                                <td>
                                                    <?php if (!empty($mpampianatra['mailaka'])): ?>
                                                        <a href="mailto:<?php echo htmlspecialchars($mpampianatra['mailaka']); ?>" class="text-decoration-none">
                                                            <?php echo htmlspecialchars($mpampianatra['mailaka']); ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">Tsy misy</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge-taranja">
                                                        <?php echo htmlspecialchars($mpampianatra['taranja']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Onglet Mpianatra -->
                <div class="tab-pane fade" id="mpianatra-pane" role="tabpanel">
                    <div class="mt-3">
                        <?php if (!empty($angon_drakitra['mpianatra'])): ?>
                            <div class="boaty-fikarohana">
                                <i class="fas fa-search"></i>
                                <input type="text" class="form-control" id="searchMpianatra" placeholder="Karohy mpianatra...">
                            </div>
                        <?php endif; ?>
                        
                        <?php if (empty($angon_drakitra['mpianatra'])): ?>
                            <div class="empty-state">
                                <i class="fas fa-user-graduate"></i>
                                <h4>Tsy misy mpianatra hita</h4>
                                <p>Tsy misy mpianatra voasoratra ao amin'ny Active Directory na misy olana amin'ny fifandraisana</p>
                            </div>
                        <?php else: ?>
                            <div class="tabilao-responsive">
                                <table class="tabilao" id="tableMpianatra">
                                    <thead>
                                        <tr>
                                            <th><i class="fas fa-user me-1"></i>Anarana feno</th>
                                            <th><i class="fas fa-id-card me-1"></i>Anaran'ny mpampiasa</th>
                                            <th><i class="fas fa-envelope me-1"></i>Mailaka</th>
                                            <th><i class="fas fa-graduation-cap me-1"></i>Kilasy</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($angon_drakitra['mpianatra'] as $mpianatra): ?>
                                            <tr>
                                                <td>
                                                    <i class="fas fa-user-graduate me-2 text-info"></i>
                                                    <?php echo htmlspecialchars($mpianatra['anarana_feno']); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($mpianatra['anarana_mpampiasa']); ?></td>
                                                <td>
                                                    <?php if (!empty($mpianatra['mailaka'])): ?>
                                                        <a href="mailto:<?php echo htmlspecialchars($mpianatra['mailaka']); ?>" class="text-decoration-none">
                                                            <?php echo htmlspecialchars($mpianatra['mailaka']); ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">Tsy misy</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge-kilasy">
                                                        <?php echo htmlspecialchars($mpianatra['kilasy']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Asa ho an'ny fanehoana tab
        function showTab(tabName) {
            const tab = document.getElementById(tabName + '-tab');
            if (tab) {
                tab.click();
            }
        }

        // Asa ho an'ny fikarohana
        function setupSearch(inputId, tableId) {
            const searchInput = document.getElementById(inputId);
            const table = document.getElementById(tableId);
            
            if (searchInput && table) {
                searchInput.addEventListener('input', function() {
                    const filter = this.value.toLowerCase();
                    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
                    
                    for (let i = 0; i < rows.length; i++) {
                        const cells = rows[i].getElementsByTagName('td');
                        let found = false;
                        
                        for (let j = 0; j < cells.length; j++) {
                            if (cells[j].textContent.toLowerCase().includes(filter)) {
                                found = true;
                                break;
                            }
                        }
                        
                        rows[i].style.display = found ? '' : 'none';
                    }
                });
            }
        }

        // Fanomanana ny fikarohana
        document.addEventListener('DOMContentLoaded', function() {
            setupSearch('searchMpampianatra', 'tableMpampianatra');
            setupSearch('searchMpianatra', 'tableMpianatra');
            
            // Fihetsiketsehana ho an'ny stats cards
            document.querySelectorAll('.stats-card').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
        });

        // Fampisehoana fampandrenesana
        function showNotification(message, type = 'success') {
            console.log(`${type}: ${message}`);
        }
    </script>
</body>
</html>