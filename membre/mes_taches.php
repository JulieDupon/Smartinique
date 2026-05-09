<?php
require_once '../includes/header.php';

$conn = getDBConnection();
$userId = $_SESSION['user_id'];

// Traitement de la mise à jour du statut
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $stmt = $conn->prepare("UPDATE tache SET statut = ? WHERE id_tache = ? AND id_membre = ?");
    $stmt->bind_param("sii", $_POST['statut'], $_POST['id_tache'], $userId);
    $stmt->execute();

    // Redirection pour éviter la resoumission du formulaire
    header("Location: mes_taches.php");
    exit();
}

// Récupération des tâches du membre
$result = $conn->query("
    SELECT t.*, p.nom_projet
    FROM tache t
    JOIN projet p ON t.id_projet = p.id_projet
    WHERE t.id_membre = $userId
    ORDER BY t.date_creation DESC
");
$tasks = $result->fetch_all(MYSQLI_ASSOC);
?>

<div class="row">
    <div class="col-md-12">
        <h1>Mes tâches</h1>

        <?php if (count($tasks) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Description</th>
                            <th>Projet</th>
                            <th>Date fin</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tasks as $tache): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($tache['titre']); ?></td>
                                <td><?php echo htmlspecialchars($tache['description']); ?></td>
                                <td><?php echo htmlspecialchars($tache['nom_projet']); ?></td>
                                <td>
                                    <?php echo $tache['date_fin'] ? date('d/m/Y', strtotime($tache['date_fin'])) : 'Non définie'; ?>
                                </td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="id_tache" value="<?php echo $tache['id_tache']; ?>">
                                        <select name="statut" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="à faire" <?php echo $tache['statut'] === 'à faire' ? 'selected' : ''; ?>>À faire</option>
                                            <option value="En cours" <?php echo $tache['statut'] === 'En cours' ? 'selected' : ''; ?>>En cours</option>
                                            <option value="Terminé" <?php echo $tache['statut'] === 'Terminé' ? 'selected' : ''; ?>>Terminé</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                </td>
                                <td>
                                    <!-- Pas d'actions supplémentaires pour les membres -->
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                Vous n'avez aucune tâche assignée pour le moment.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
