<?php
session_start();

$ldap_host = "192.168.40.132"; // IP an'ny serveur AD
$ldap_domain = "Educonnect.mg";

$username = $_POST['username'];
$password = $_POST['password'];
$ldap_user = $username . "@" . $ldap_domain;

// Fifandraisana amin'ny serveur LDAP
$ldap_conn = ldap_connect($ldap_host);
ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

// Fanamarinam-pifandraisana LDAP
if (!$ldap_conn) {
    $_SESSION['login_error'] = 'ldap_connection';
    $_SESSION['login_error_message'] = 'Tsy afaka mifandray amin\'ny serveur LDAP';
    file_put_contents("debug_ldap_error.txt", "Hadisoana fifandraisana LDAP: " . $ldap_host . "\n", FILE_APPEND);
    header("Location: fidirana.php");
    exit;
}

// Fifandraisana amin'ny mari-pamantarana nomena
if (!@ldap_bind($ldap_conn, $ldap_user, $password)) {
    $_SESSION['login_error'] = 'invalid_credentials';
    $_SESSION['login_error_message'] = 'Diso ny anarana mpampiasa na ny teny miafina';
    file_put_contents("debug_ldap_error.txt", "Hadisoana bind ho an'i $ldap_user amin'ny teny miafina.\n", FILE_APPEND);
    header("Location: fidirana.php");
    exit;
}

// Fikarohana ny mpampiasa ao amin'ny AD
$search_base = "DC=Educonnect,DC=mg";
$filter = "(sAMAccountName=$username)";
$result = ldap_search($ldap_conn, $search_base, $filter, ["cn", "memberOf"]);

if (!$result) {
    $_SESSION['login_error'] = 'ldap_search';
    $_SESSION['login_error_message'] = 'Hadisoana tamin\'ny fikarohana LDAP';
    file_put_contents("debug_ldap_error.txt", "Hadisoana fikarohana LDAP ho an'i $username.\n", FILE_APPEND);
    header("Location: fidirana.php");
    exit;
}

$entries = ldap_get_entries($ldap_conn, $result);

// Fanomanana ny angon-drakitra
$groups = [];
$fullname = $username;

if ($entries["count"] > 0) {
    if (isset($entries[0]["cn"][0])) {
        $fullname = $entries[0]["cn"][0];
    }

    if (isset($entries[0]["memberof"])) {
        for ($i = 0; $i < $entries[0]["memberof"]["count"]; $i++) {
            if (preg_match("/CN=([^,]+)/", $entries[0]["memberof"][$i], $matches)) {
                $groups[] = $matches[1];
            }
        }
    }
} else {
    $_SESSION['login_error'] = 'no_user_found';
    $_SESSION['login_error_message'] = 'Tsy misy mpampiasa hita ao amin\'ny AD';
    file_put_contents("debug_ldap_error.txt", "Tsy misy mpampiasa hita ho an'i $username.\n", FILE_APPEND);
    header("Location: fidirana.php");
    exit;
}

// Fitahirizana ao amin'ny session
$_SESSION['username'] = $username;
$_SESSION['fullname'] = $fullname;
$_SESSION['groups'] = $groups;

// ✅ Famaritana ny taranja avy amin'ny vondrona G_<taranja>
foreach ($groups as $group) {
    if (preg_match('/^G_([^_]+)$/', $group, $match) && !in_array($group, ['G_Tous_Professeurs', 'G_Tous_Eleves', 'G_Tous_Personnel_Admin'])) {
        $_SESSION['matiere'] = $match[1];
        break;
    }
}

// 🔍 Firaketana ho an'ny debug raha ilaina
file_put_contents("debug_auth_session.txt", json_encode($_SESSION, JSON_PRETTY_PRINT));

// Famindrana araka ny vondrona
if (in_array("G_Tous_Personnel_Admin", $groups)) {
    header("Location: mpizara.php");
} elseif (in_array("G_Tous_Professeurs", $groups)) {
    header("Location: mpampianatra.php");
} elseif (in_array("G_Tous_Eleves", $groups)) {
    header("Location: mpianatra.php");
} elseif (in_array("G_Tous_Personnel_Admin", $groups)) {
    header("Location: mpizara.php");
} elseif (in_array("G_Admin_Direction", $groups)) {
    header("Location: tale.php");
} else {
    $_SESSION['login_error'] = 'no_group';
    $_SESSION['login_error_message'] = 'Ity mpampiasa ity dia tsy ao amin\'ny vondrona misy';
    file_put_contents("debug_ldap_error.txt", "Ny mpampiasa $username dia tsy ao amin\'ny vondrona manan-kery.\n", FILE_APPEND);
    header("Location: fidirana.php");
}

exit;
?>