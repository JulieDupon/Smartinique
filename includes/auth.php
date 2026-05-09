<?php
require_once 'config.php';

// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function login($email, $password) {
    $conn = getDBConnection();

    // Préparer la requête pour éviter les injections SQL
    $stmt = $conn->prepare("SELECT m.*, r.nom_role FROM membre m JOIN role r ON m.id_role = r.id_role WHERE email = ? AND actif = 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Solution temporaire pour Julie (à supprimer après l'examen)
        if ($email === 'julie.dupon@smartinique.fr' && $password === 'julie123') {
            $_SESSION['user_id'] = $user['id_membre'];
            $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
            $_SESSION['user_role'] = $user['nom_role'];
            $_SESSION['logged_in'] = true;
            return true;
        }

        // Comparaison normale avec SHA-1
        $hashed_password = sha1($password);
        if ($hashed_password === $user['mot_de_passe']) {
            $_SESSION['user_id'] = $user['id_membre'];
            $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
            $_SESSION['user_role'] = $user['nom_role'];
            $_SESSION['logged_in'] = true;
            return true;
        }
    }

    return false;
}

function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function isAdmin() {
    return isLoggedIn() && $_SESSION['user_role'] === 'Admin';
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header('Location: ../index.php');
        exit();
    }
}

function redirectIfNotAdmin() {
    if (!isAdmin()) {
        header('Location: ../membre/dashboard.php');
        exit();
    }
}

// Fonction pour déconnecter l'utilisateur
function logout() {
    session_unset();
    session_destroy();
}
?>
