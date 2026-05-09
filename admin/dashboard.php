<?php
require_once '../includes/header.php';
redirectIfNotAdmin();
?>

<div class="row">
    <div class="col-md-12">
        <h1>Tableau de bord Admin</h1>
        <p>Bienvenue dans l'interface d'administration de Smartinique.</p>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-4">
        <div class="card text-white bg-primary mb-3">
            <div class="card-header">Membres</div>
            <div class="card-body">
                <h5 class="card-title">Gestion des membres</h5>
                <p class="card-text">Ajouter, modifier ou supprimer des membres.</p>
                <a href="membres.php" class="btn btn-light">Accéder</a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card text-white bg-success mb-3">
            <div class="card-header">Projets</div>
            <div class="card-body">
                <h5 class="card-title">Gestion des projets</h5>
                <p class="card-text">Créer et suivre les projets en cours.</p>
                <a href="projets.php" class="btn btn-light">Accéder</a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card text-white bg-warning mb-3">
            <div class="card-header">Tâches</div>
            <div class="card-body">
                <h5 class="card-title">Gestion des tâches</h5>
                <p class="card-text">Organiser et assigner les tâches.</p>
                <a href="taches.php" class="btn btn-light">Accéder</a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
