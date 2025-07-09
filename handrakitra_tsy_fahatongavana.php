<?php
session_start();

// Manamarina raha tafiditra ny mpampiasa
if (!isset($_SESSION['username'])) {
    header("Location: fidirana.php");
    exit;
}

// Fifandraisana amin'ny angon-drakitra MySQL
$mysqli = new mysqli("localhost", "root", "Basique12345", "educonnect");

if ($mysqli->connect_error) {
    die("Tsy afaka nifandray: " . $mysqli->connect_error);
}

// Manamarina fa misy ny angon-drakitra ao amin'ny formulaire
if (!isset($_POST['mpianatra'], $_POST['karazana'], $_POST['daty'])) {
    $_SESSION['hafatra'] = "Tsy feno ny angon-drakitra ao amin'ny formulaire.";
    header("Location: mpizara.php");
    exit();
}

// Maka ny angon-drakitra avy amin'ny formulaire
$anarana_mpianatra = $_POST['mpianatra'];
$karazana = $_POST['karazana'];
$daty = $_POST['daty'];
$ora = $_POST['ora'] ?? null; // Ny ora dia tsy tsy maintsy
$antony = $_POST['antony'] ?? ''; // Ny antony dia tsy tsy maintsy

// Fifandraisana LDAP amin'ny adiresy IP mivantana
$ldapconn = ldap_connect("ldap://192.168.40.132");

if (!$ldapconn) {
    die("❌ Tsy afaka nifandray tamin'ny serveur LDAP.");
}

// Fandrindrana LDAP
ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);

// Fitsapana ny fifandraisana amin'ny mari-pamantarana
$ldapbind = ldap_bind($ldapconn, "EDUCONNECT\\ElimProf", "12345Orion");

if (!$ldapbind) {
    // Mampiseho fampahalalana bebe kokoa momba ny hadisoana
    die("❌ Hadisoana fifamatoana LDAP. Hadisoana : " . ldap_error($ldapconn));
}

// Fikarohana ny mpampiasa ao amin'ny AD
$search = ldap_search($ldapconn, "dc=educonnect,dc=mg", "(sAMAccountName=$anarana_mpianatra)", ["memberOf"]);
if (!$search) {
    die("❌ Hadisoana nandritra ny fikarohana LDAP : " . ldap_error($ldapconn));
}

$entries = ldap_get_entries($ldapconn, $search);

// Fanaraha-maso ny isan'ny fidirana hita
if ($entries["count"] <= 0) {
    $_SESSION['hafatra'] = "Tsy hita ao amin'ny Active Directory ny mpianatra.";
    header("Location: mpizara.php");
    exit();
}

// Famaritana ny kilasy amin'ny alalan'ny vondrona AD
$kilasy = "Tsy fantatra"; // Sanda default
foreach ($entries[0]["memberof"] as $group) {
    if (preg_match("/CN=G_(L[1-2]G[1-2])/", $group, $matches)) {
        $kilasy = $matches[1]; // Ohatra: L1G1
        break;
    }
}

// Fampidirana ny tsy fahatongavana/fahatara ao amin'ny angon-drakitra
$query = "INSERT INTO tsy_fahatongavana_fahatara (mpianatra, daty, ora, karazana, antony, kilasy) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("ssssss", $anarana_mpianatra, $daty, $ora, $karazana, $antony, $kilasy);

if ($stmt->execute()) {
    $_SESSION['hafatra'] = "✅ $karazana voarakitra ho an'ny mpianatra : $anarana_mpianatra (Kilasy : $kilasy).";
} else {
    $_SESSION['hafatra'] = "❌ Tsy nahomby ny fandraketana.";
}

// Fanakatonana ny fifandraisana
$stmt->close();
$mysqli->close();
ldap_unbind($ldapconn);

// Fiverenana any amin'ny pejin'ny mpizara
header("Location: mpizara.php");
exit();
?>