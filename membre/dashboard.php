<?php
require_once '../includes/header.php';

$conn = getDBConnection();

// Récupération des projets
$result = $conn->query("
    SELECT p.*, m.prenom, m.nom
    FROM projet p
    JOIN membre m ON p.id_membre = m.id_membre
    ORDER BY p.date_debut DESC
");
$projects = $result->fetch_all(MYSQLI_ASSOC);
?>

<div class="row">
    <div class="col-md-12">
        <h1>Tableau de bord Membre</h1>
        <p>Bienvenue dans votre espace membre.</p>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <h2>Projets en cours</h2>

        <?php if (count($projects) > 0): ?>
            <div class="row">
                <?php foreach ($projects as $projet): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title"><?php echo htmlspecialchars($projet['nom_projet']); ?></h5>
                            </div>
                            <div class="card-body">
                                <p class="card-text"><?php echo htmlspecialchars($projet['description']); ?></p>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">
                                        <strong>Date début:</strong> <?php echo date('d/m/Y', strtotime($projet['date_debut'])); ?>
                                    </li>
                                    <li class="list-group-item">
                                        <strong>Date fin:</strong>
                                        <?php echo $projet['date_fin'] ? date('d/m/Y', strtotime($projet['date_fin'])) : 'Non définie'; ?>
                                    </li>
                                    <li class="list-group-item">
                                        <strong>Statut:</strong>
                                        <span class="badge
                                            <?php echo $projet['statut'] === 'Terminé' ? 'bg-success' :
                                                ($projet['statut'] === 'En cours' ? 'bg-primary' : 'bg-warning'); ?>">
                                            <?php echo htmlspecialchars($projet['statut']); ?>
                                        </span>
                                    </li>
                                    <li class="list-group-item">
                                        <strong>Responsable:</strong> <?php echo htmlspecialchars($projet['prenom'] . ' ' . $projet['nom']); ?>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                Aucun projet en cours pour le moment.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
