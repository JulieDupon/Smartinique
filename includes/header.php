<?php
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smartinique - <?php echo $_SESSION['user_role']; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- CSS personnalisé -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Smartinique</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="../admin/dashboard.php">Tableau de bord</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../admin/membres.php">Membres</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../admin/projets.php">Projets</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../admin/taches.php">Tâches</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="../membre/dashboard.php">Tableau de bord</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../membre/mes_taches.php">Mes tâches</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <span class="navbar-text me-3">Bonjour, <?php echo $_SESSION['user_name']; ?> (<?php echo $_SESSION['user_role']; ?>)</span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Déconnexion</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
