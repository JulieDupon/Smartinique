<?php
require_once '../includes/header.php';
redirectIfNotAdmin();

$conn = getDBConnection();

// Traitement des formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        // Création d'un membre
        $stmt = $conn->prepare("INSERT INTO membre (nom, prenom, email, mot_de_passe, id_role, actif) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssii", $_POST['nom'], $_POST['prenom'], $_POST['email'], $_POST['mot_de_passe'], $_POST['id_role'], isset($_POST['actif']) ? 1 : 0);
        $stmt->execute();
    } elseif (isset($_POST['update'])) {
        // Mise à jour d'un membre
        $stmt = $conn->prepare("UPDATE membre SET nom = ?, prenom = ?, email = ?, mot_de_passe = ?, id_role = ?, actif = ? WHERE id_membre = ?");
        $stmt->bind_param("ssssiii", $_POST['nom'], $_POST['prenom'], $_POST['email'], $_POST['mot_de_passe'], $_POST['id_role'], isset($_POST['actif']) ? 1 : 0, $_POST['id_membre']);
        $stmt->execute();
    } elseif (isset($_POST['delete'])) {
        // Suppression d'un membre
        $stmt = $conn->prepare("DELETE FROM membre WHERE id_membre = ?");
        $stmt->bind_param("i", $_POST['id_membre']);
        $stmt->execute();
    }

    // Redirection pour éviter la resoumission du formulaire
    header("Location: membres.php");
    exit();
}

// Récupération des membres
$result = $conn->query("SELECT m.*, r.nom_role FROM membre m JOIN role r ON m.id_role = r.id_role");
$members = $result->fetch_all(MYSQLI_ASSOC);

// Récupération des rôles
$roles = $conn->query("SELECT * FROM role")->fetch_all(MYSQLI_ASSOC);

// Récupération d'un membre spécifique pour édition
$member = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM membre WHERE id_membre = ?");
    $stmt->bind_param("i", $_GET['edit']);
    $stmt->execute();
    $result = $stmt->get_result();
    $member = $result->fetch_assoc();

    // Bloquer l'édition si ce n'est pas un admin
    if ($member && $member['id_role'] != 1) {
        header("Location: membres.php?error=unauthorized");
        exit();
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <h1>Gestion des membres</h1>

        <!-- Bouton pour afficher le modal de création -->
        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#memberModal">
            Ajouter un membre
        </button>

        <!-- Tableau des membres -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($members as $membre): ?>
                        <tr>
                            <td><?php echo $membre['id_membre']; ?></td>
                            <td><?php echo htmlspecialchars($membre['nom']); ?></td>
                            <td><?php echo htmlspecialchars($membre['prenom']); ?></td>
                            <td><?php echo htmlspecialchars($membre['email']); ?></td>
                            <td><?php echo htmlspecialchars($membre['nom_role']); ?></td>
                            <td>
                                <span class="badge <?php echo $membre['actif'] ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo $membre['actif'] ? 'Actif' : 'Inactif'; ?>
                                </span>
                            </td>
                            <td>
                                <a href="?edit=<?php echo $membre['id_membre']; ?>" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#memberModal">Modifier</a>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="id_membre" value="<?php echo $membre['id_membre']; ?>">
                                    <button type="submit" name="delete" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce membre ?')">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal pour création/modification -->
<div class="modal fade" id="memberModal" tabindex="-1" aria-labelledby="memberModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="memberModalLabel">
                        <?php echo isset($member) ? 'Modifier le membre' : 'Ajouter un membre'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (isset($member)): ?>
                        <input type="hidden" name="id_membre" value="<?php echo $member['id_membre']; ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">Nom</label>
                        <input type="text" class="form-control" name="nom" value="<?php echo htmlspecialchars($member['nom'] ?? ''); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Prénom</label>
                        <input type="text" class="form-control" name="prenom" value="<?php echo htmlspecialchars($member['prenom'] ?? ''); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($member['email'] ?? ''); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" name="mot_de_passe" <?php echo isset($member) ? '' : 'required'; ?>>
                        <?php if (isset($member)): ?>
                            <div class="form-text">Laisser vide pour ne pas modifier le mot de passe</div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Rôle</label>
                        <select class="form-select" name="id_role" required>
                            <?php foreach ($roles as $role): ?>
                                <?php // Masquer le rôle "Compte désactivé" si présent ?>
                                <?php if ($role['nom_role'] != 'Compte désactivé'): ?>
                                    <option value="<?php echo $role['id_role']; ?>"
                                        <?php echo (isset($member) && $member['id_role'] == $role['id_role']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($role['nom_role']); ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>


                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="actif" id="actif" <?php echo (isset($member) && $member['actif']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="actif">Compte actif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" name="<?php echo isset($member) ? 'update' : 'create'; ?>" class="btn btn-primary">
                        <?php echo isset($member) ? 'Mettre à jour' : 'Créer'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
