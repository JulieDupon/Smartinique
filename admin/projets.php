<?php
$pageTitle = "Gestion des projets";
require_once '../includes/header.php';
require_once '../includes/auth.php';
redirectIfNotAdmin();

// Gérer la création d'un nouveau projet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_project'])) {
    $nom_projet = $_POST['nom_projet'] ?? '';
    $description = $_POST['description'] ?? '';
    $date_debut = $_POST['date_debut'] ?? '';
    $date_fin = $_POST['date_fin'] ?? null;
    $statut = $_POST['statut'] ?? 'En cours';
    $id_membre = $_POST['id_membre'] ?? 0;

    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO projet (nom_projet, description, date_debut, date_fin, statut, id_membre) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $nom_projet, $description, $date_debut, $date_fin, $statut, $id_membre);
    $stmt->execute();

    $success = "Projet créé avec succès!";
}

// Gérer la modification d'un projet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_project'])) {
    $id_projet = $_POST['id_projet'] ?? 0;
    $nom_projet = $_POST['nom_projet'] ?? '';
    $description = $_POST['description'] ?? '';
    $date_debut = $_POST['date_debut'] ?? '';
    $date_fin = $_POST['date_fin'] ?? null;
    $statut = $_POST['statut'] ?? 'En cours';
    $id_membre = $_POST['id_membre'] ?? 0;

    $conn = getDBConnection();
    $stmt = $conn->prepare("UPDATE projet SET nom_projet = ?, description = ?, date_debut = ?, date_fin = ?, statut = ?, id_membre = ? WHERE id_projet = ?");
    $stmt->bind_param("sssssii", $nom_projet, $description, $date_debut, $date_fin, $statut, $id_membre, $id_projet);
    $stmt->execute();

    $success = "Projet mis à jour avec succès!";
}

// Gérer la suppression d'un projet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_project'])) {
    $id_projet = intval($_POST['id_projet']);

    $conn = getDBConnection();

    // 1. Supprimer les tâches associées
    $stmt = $conn->prepare("DELETE FROM tache WHERE id_projet = ?");
    $stmt->bind_param("i", $id_projet);
    $stmt->execute();

    // 2. Supprimer le projet
    $stmt = $conn->prepare("DELETE FROM projet WHERE id_projet = ?");
    $stmt->bind_param("i", $id_projet);

    if ($stmt->execute()) {
        $success = "Projet supprimé avec succès!";
    } else {
        $error = "Erreur lors de la suppression du projet.";
    }
}

// Récupérer tous les projets
$conn = getDBConnection();
$result = $conn->query("
    SELECT p.*, m.nom as responsable_nom, m.prenom as responsable_prenom
    FROM projet p
    JOIN membre m ON p.id_membre = m.id_membre
    JOIN role r ON m.id_role = r.id_role
    WHERE r.nom_role = 'Admin' 
    ORDER BY p.id_projet DESC
");
$projects = $result->fetch_all(MYSQLI_ASSOC);

// Récupérer les membres pour le formulaire
$members = $conn->query("
    SELECT m.id_membre, m.nom, m.prenom
    FROM membre m
    JOIN role r ON m.id_role = r.id_role
    WHERE m.actif = 1 AND r.nom_role = 'Admin'  
")->fetch_all(MYSQLI_ASSOC);
?>

<div class="row">
    <div class="col-md-12">
        <h1>Gestion des projets</h1>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Bouton pour créer un nouveau projet -->
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createProjectModal">
            <i class="fas fa-plus"></i> Nouveau projet
        </button>

        <!-- Tableau des projets -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Début</th>
                        <th>Fin</th>
                        <th>Statut</th>
                        <th>Responsable</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $project): ?>
                        <tr>
                            <td><?php echo $project['id_projet']; ?></td>
                            <td><?php echo htmlspecialchars($project['nom_projet']); ?></td>
                            <td><?php echo htmlspecialchars($project['description']); ?></td>
                            <td><?php echo (new DateTime($project['date_debut']))->format('d/m/Y'); ?></td>
                            <td>
                                <?php echo $project['date_fin'] ? (new DateTime($project['date_fin']))->format('d/m/Y') : 'Non défini'; ?>
                            </td>
                            <td>
                                <span class="badge
                                    <?php
                                    switch ($project['statut']) {
                                        case 'En cours': echo 'bg-primary'; break;
                                        case 'Terminé': echo 'bg-success'; break;
                                        case 'Annulé': echo 'bg-danger'; break;
                                        default: echo 'bg-secondary';
                                    }
                                    ?>">
                                    <?php echo htmlspecialchars($project['statut']); ?>
                                </span>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($project['responsable_prenom'] . ' ' . $project['responsable_nom']); ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editProjectModal<?php echo $project['id_projet']; ?>">
                                    <i class="fas fa-edit"></i> Modifier
                                </button>
                                    <form method="POST" action="projets.php" style="display: inline;">
                                        <input type="hidden" name="id_projet" value="<?= $project['id_projet'] ?>">
                                        <button type="submit" name="delete_project" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Voulez-vous vraiment supprimer ce projet ?')">
                                        Supprimer
                                </button>
                                </form>
                            </td>
                        </tr>

                        <!-- Modal pour modifier le projet -->
                        <div class="modal fade" id="editProjectModal<?php echo $project['id_projet']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Modifier le projet</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="id_projet" value="<?php echo $project['id_projet']; ?>">

                                            <div class="mb-3">
                                                <label class="form-label">Nom du projet</label>
                                                <input type="text" class="form-control" name="nom_projet" value="<?php echo htmlspecialchars($project['nom_projet']); ?>" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Description</label>
                                                <textarea class="form-control" name="description"><?php echo htmlspecialchars($project['description']); ?></textarea>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Date de début</label>
                                                <input type="date" class="form-control" name="date_debut" value="<?php echo $project['date_debut']; ?>" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Date de fin (optionnelle)</label>
                                                <input type="date" class="form-control" name="date_fin" value="<?php echo $project['date_fin']; ?>">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Statut</label>
                                                <select class="form-select" name="statut" required>
                                                    <option value="En cours" <?php echo $project['statut'] === 'En cours' ? 'selected' : ''; ?>>En cours</option>
                                                    <option value="Terminé" <?php echo $project['statut'] === 'Terminé' ? 'selected' : ''; ?>>Terminé</option>
                                                    <option value="Annulé" <?php echo $project['statut'] === 'Annulé' ? 'selected' : ''; ?>>Annulé</option>
                                                </select>
                                            </div>

                                        <div class="mb-3">
                                                <label class="form-label">Responsable</label>
                                                <select class="form-select" name="id_membre" required>
                                                    <?php foreach ($members as $member): ?>
                                                        <option value="<?php echo $member['id_membre']; ?>" <?php echo $project['id_membre'] == $member['id_membre'] ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($member['prenom'] . ' ' . $member['nom']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" name="update_project" class="btn btn-primary">Enregistrer</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal pour créer un nouveau projet -->
<div class="modal fade" id="createProjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Nouveau projet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nom du projet</label>
                        <input type="text" class="form-control" name="nom_projet" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Date de début</label>
                        <input type="date" class="form-control" name="date_debut" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Date de fin (optionnelle)</label>
                        <input type="date" class="form-control" name="date_fin">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Statut</label>
                        <select class="form-select" name="statut" required>
                            <option value="En cours" selected>En cours</option>
                            <option value="Terminé">Terminé</option>
                            <option value="Annulé">Annulé</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Responsable</label>
                        <select class="form-select" name="id_membre" required>
                            <?php foreach ($members as $member): ?>
                                <option value="<?php echo $member['id_membre']; ?>">
                                    <?php echo htmlspecialchars($member['prenom'] . ' ' . $member['nom']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" name="create_project" class="btn btn-primary">Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
