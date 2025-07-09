<?php
// ===== FANARAHA-MASO NY FIDIRANA =====

function checkAccess($vondrona_ilaina) {
    if (!isset($_SESSION['groups'])) {
        $_SESSION['hafatra_diso'] = 'Tsy misy fahazoan-dalana.';
        header("Location: fidirana.php");
        exit;
    }
    
    $vondrona_mpampiasa = $_SESSION['groups'];
    $manana_fahazoan_dalana = false;
    
    foreach ($vondrona_ilaina as $vondrona) {
        if (in_array($vondrona, $vondrona_mpampiasa)) {
            $manana_fahazoan_dalana = true;
            break;
        }
    }
    
    if (!$manana_fahazoan_dalana) {
        $_SESSION['hafatra_diso'] = 'Tsy manana fahazoan-dalana amin\'ity pejy ity.';
        header("Location: fidirana.php");
        exit;
    }
}

// Fanamarinana ny vondrona manokana
function checkSpecificGroup($vondrona) {
    if (!isset($_SESSION['groups'])) {
        return false;
    }
    
    return in_array($vondrona, $_SESSION['groups']);
}

// Fanamarinana raha mpampianatra
function isMpampianatra() {
    return checkSpecificGroup('G_Tous_Professeurs');
}

// Fanamarinana raha mpianatra
function isMpianatra() {
    return checkSpecificGroup('G_Tous_Eleves');
}

// Fanamarinana raha mpizara
function isMpizara() {
    return checkSpecificGroup('G_Tous_Personnel_Admin');
}

// Fanamarinana raha tale
function isTale() {
    return checkSpecificGroup('G_Admin_Direction');
}
?>