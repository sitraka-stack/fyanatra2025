<?php
session_start();

// Script de débogage pour vérifier les données
require_once 'check_access.php';
checkAccess(['G_Tous_Professeurs']);

if (!isset($_SESSION['username'])) {
    die("Session non définie");
}

$anarana_feno = $_SESSION['fullname'];
$taranja_mpampianatra = $_SESSION['matiere'];

echo "<h2>Informations de session :</h2>";
echo "Username: " . $_SESSION['username'] . "<br>";
echo "Fullname: " . $anarana_feno . "<br>";
echo "Matière: " . $taranja_mpampianatra . "<br>";

// Connexion MySQL
$mysqli = new mysqli("localhost", "root", "Basique12345", "educonnect");
$mysqli->set_charset("utf8mb4");

if ($mysqli->connect_error) {
    die("Erreur MySQL : " . $mysqli->connect_error);
}

echo "<h2>Vérification de la table naoty :</h2>";

// Vérifier toutes les notes
$query_all = "SELECT * FROM naoty LIMIT 10";
$result_all = $mysqli->query($query_all);
echo "<h3>Premières 10 entrées de la table naoty :</h3>";
if ($result_all->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Mpianatra</th><th>Taranja</th><th>Kilasy</th><th>Mpampianatra</th><th>Naoty</th><th>Date</th></tr>";
    while ($row = $result_all->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['mpianatra'] . "</td>";
        echo "<td>" . $row['taranja'] . "</td>";
        echo "<td>" . $row['kilasy'] . "</td>";
        echo "<td>" . $row['mpampianatra'] . "</td>";
        echo "<td>" . $row['naoty'] . "</td>";
        echo "<td>" . $row['daty_fampidirana'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Aucune note trouvée dans la table.";
}

// Vérifier les notes pour ce professeur et cette matière
echo "<h3>Notes pour ce professeur (" . $anarana_feno . ") et cette matière (" . $taranja_mpampianatra . ") :</h3>";
$query_prof = $mysqli->prepare("SELECT * FROM naoty WHERE mpampianatra = ? AND taranja = ?");
$query_prof->bind_param("ss", $anarana_feno, $taranja_mpampianatra);
$query_prof->execute();
$result_prof = $query_prof->get_result();

if ($result_prof->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Mpianatra</th><th>Kilasy</th><th>Naoty</th><th>Date</th></tr>";
    while ($row = $result_prof->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['mpianatra'] . "</td>";
        echo "<td>" . $row['kilasy'] . "</td>";
        echo "<td>" . $row['naoty'] . "</td>";
        echo "<td>" . $row['daty_fampidirana'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Aucune note trouvée pour ce professeur et cette matière.";
}

// Vérifier les variations possibles du nom
echo "<h3>Vérification des noms de professeurs dans la base :</h3>";
$query_profs = "SELECT DISTINCT mpampianatra FROM naoty";
$result_profs = $mysqli->query($query_profs);
echo "Professeurs trouvés dans la base :<br>";
while ($row = $result_profs->fetch_assoc()) {
    echo "- '" . $row['mpampianatra'] . "'<br>";
}

echo "<h3>Vérification des matières dans la base :</h3>";
$query_matieres = "SELECT DISTINCT taranja FROM naoty";
$result_matieres = $mysqli->query($query_matieres);
echo "Matières trouvées dans la base :<br>";
while ($row = $result_matieres->fetch_assoc()) {
    echo "- '" . $row['taranja'] . "'<br>";
}

$mysqli->close();
?>