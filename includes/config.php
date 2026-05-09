<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'smartinique');

// Connexion à la base de données
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        die("Échec de la connexion : " . $conn->connect_error);
    }

    return $conn;
}

// Démarrer la session
session_start();
?>
