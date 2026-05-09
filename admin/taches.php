<?php
require_once '../includes/header.php';
redirectIfNotAdmin();

$conn = getDBConnection();

// Traitement des formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        $stmt = $conn->prepare("INSERT INTO tache (titre, description, date_fin,
        statut, id_projet, id_membre) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssii", $_POST['titre'], $_POST['description'], 
        $_POST['date_fin'], $_POST['statut'], $_POST['id_projet'], 
        $_POST['id_membre']);
        $stmt->execute();
    } elseif (isset($_POST['update'])) {
        $stmt = $conn->prepare("UPDATE tache SET titre = ?, description = ?,
        date_fin = ?, statut = ?, id_projet = ?, id_membre = ? WHERE id_tache = 
        ?");
        $stmt->bind_param("ssssiii", $_POST['titre'], $_POST['description'], 
        $_POST['date_fin'], $_POST['statut'], $_POST['id_projet'], 
        $_POST['id_membre'], $_POST['id_tache']);
        $stmt->execute();
    } elseif (isset($_POST['delete'])) {
        $stmt = $conn->prepare("DELETE FROM tache WHERE id_tache = ?");
        $stmt->bind_param("i", $_POST['id_tache']);
        $stmt->execute();
    }
    header("Location: taches.php");
    exit();
}

// Récupération des données
$tasks = $conn->query("
    SELECT t.*, p.nom_projet, m.prenom, m.nom 
    FROM tache t 
    JOIN projet p ON t.id_projet = p.id_projet 
    JOIN membre m ON t.id_membre = m.id_membre 
    ORDER BY t.date_creation DESC
    ")->fetch_all(MYSQLI_ASSOC);
$projects = $conn->query("
    SELECT id_projet, nom_projet FROM projet
    ")->fetch_all(MYSQLI_ASSOC);
$members = $conn->query("
SELECT id_membre, prenom, nom 
FROM membre WHERE actif = 1
")->fetch_all(MYSQLI_ASSOC);

// Récupération d'une tâche pour édition (NE PAS MODIFIER)
$task = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM tache WHERE id_tache = ?");
    $stmt->bind_param("i", $_GET['edit']);
    $stmt->execute();
    $result = $stmt->get_result();
    $task = $result->fetch_assoc();
}
?>

<div class="container mt-4">
    <h1>Gestion des tâches</h1>

    <!-- Bouton Ajouter (réinitialise l'URL) -->
    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#taskModal" onclick="resetModal()">
        Ajouter une tâche
    </button>

    <!-- Tableau des tâches -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Titre</th>
                <th>Projet</th>
                <th>Membre</th>
                <th>Statut</th>
                <th>Date de fin</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tasks as $tache): ?>
                <tr>
                    <td><?= htmlspecialchars($tache['titre']) ?></td>
                    <td><?= htmlspecialchars($tache['nom_projet']) ?></td>
                    <td><?= htmlspecialchars($tache['prenom'] . ' ' . $tache['nom']) ?></td>
                    <td>
                        <span class="badge bg-<?= ($tache['statut'] === 'Terminé') ? 'success' : (($tache['statut'] === 'En cours') ? 'warning' : 'primary') ?>">
                            <?= htmlspecialchars($tache['statut']) ?>
                        </span>
                    </td>
                    <td>
                        <?php
                        // Vérifie si la date est valide (non NULL et différente de '0000-00-00')
                        if (!empty($tache['date_fin']) && $tache['date_fin'] != '0000-00-00') {
                            echo date('d/m/Y', strtotime($tache['date_fin']));
                        } else {
                            echo '<span class="text-muted">Non définie</span>';
                        }
                        ?>
                    </td>                   
                    <td>
                        <!-- Bouton Modifier (avec JavaScript pour recharger le modal) -->
                        <button type="button" class="btn btn-sm btn-warning" onclick="openEditModal(<?= $tache['id_tache'] ?>)">
                            Modifier
                        </button>

                        <!-- Bouton Supprimer -->
                        <form method="POST" action="taches.php" class="d-inline">
                            <input type="hidden" name="id_tache" value="<?= $tache['id_tache'] ?>">
                            <button type="submit" name="delete" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr ?')">
                                Supprimer
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal (vide par défaut) -->
<div class="modal fade" id="taskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="taches.php" id="taskForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="taskModalLabel">Ajouter une tâche</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="taskModalBody">
                    <!-- Contenu chargé dynamiquement par JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-primary" id="taskSubmitBtn">Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript pour gérer le modal -->
<script>
function resetModal() {
    // Réinitialise le modal en mode Ajout
    document.getElementById('taskModalLabel').textContent = 'Ajouter une tâche';
    document.getElementById('taskSubmitBtn').textContent = 'Créer';
    document.getElementById('taskSubmitBtn').name = 'create';
    document.getElementById('taskModalBody').innerHTML = `
        <div class="mb-3">
            <label class="form-label">Titre</label>
            <input type="text" class="form-control" name="titre" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="3"></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Projet</label>
            <select class="form-select" name="id_projet" required>
                <option value="">Sélectionnez un projet</option>
                <?php foreach ($projects as $project): ?>
                    <option value="<?= $project['id_projet'] ?>"><?= htmlspecialchars($project['nom_projet']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Membre assigné</label>
            <select class="form-select" name="id_membre" required>
                <option value="">Sélectionnez un membre</option>
                <?php foreach ($members as $member): ?>
                    <option value="<?= $member['id_membre'] ?>"><?= htmlspecialchars($member['prenom'] . ' ' . $member['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Date de fin (optionnelle)</label>
            <input type="date" class="form-control" name="date_fin">
        </div>
        <div class="mb-3">
            <label class="form-label">Statut</label>
            <select class="form-select" name="statut" required>
                <option value="À faire">À faire</option>
                <option value="En cours">En cours</option>
                <option value="Terminé">Terminé</option>
            </select>
        </div>
    `;
}

function openEditModal(id) {
    // Charge les données de la tâche via AJAX
    fetch(`taches.php?edit=${id}`)
        .then(response => response.text())
        .then(html => {
            // Parse le HTML pour extraire les données de la tâche
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const task = JSON.parse(doc.querySelector('script[data-task]').textContent);

            // Met à jour le modal
            document.getElementById('taskModalLabel').textContent = 'Modifier une tâche';
            document.getElementById('taskSubmitBtn').textContent = 'Mettre à jour';
            document.getElementById('taskSubmitBtn').name = 'update';

            document.getElementById('taskModalBody').innerHTML = `
                <input type="hidden" name="id_tache" value="${task.id_tache}">
                <div class="mb-3">
                    <label class="form-label">Titre</label>
                    <input type="text" class="form-control" name="titre" value="${task.titre}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="3">${task.description || ''}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Projet</label>
                    <select class="form-select" name="id_projet" required>
                        <option value="">Sélectionnez un projet</option>
                        <?php foreach ($projects as $project): ?>
                            <option value="<?= $project['id_projet'] ?>" ${task.id_projet == <?= $project['id_projet'] ?> ? 'selected' : ''}>
                                <?= htmlspecialchars($project['nom_projet']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Membre assigné</label>
                    <select class="form-select" name="id_membre" required>
                        <option value="">Sélectionnez un membre</option>
                        <?php foreach ($members as $member): ?>
                            <option value="<?= $member['id_membre'] ?>" ${task.id_membre == <?= $member['id_membre'] ?> ? 'selected' : ''}>
                                <?= htmlspecialchars($member['prenom'] . ' ' . $member['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Date de fin (optionnelle)</label>
                    <input type="date" class="form-control" name="date_fin"
                    value="${task.date_fin && task.date_fin != '0000-00-00' ? new Date(task.date_fin).toISOString().split('T')[0] : ''}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Statut</label>
                    <select class="form-select" name="statut" required>
                        <option value="À faire" ${task.statut === 'À faire' ? 'selected' : ''}>À faire</option>
                        <option value="En cours" ${task.statut === 'En cours' ? 'selected' : ''}>En cours</option>
                        <option value="Terminé" ${task.statut === 'Terminé' ? 'selected' : ''}>Terminé</option>
                    </select>
                </div>
            `;

            // Ouvre le modal
            const modal = new bootstrap.Modal(document.getElementById('taskModal'));
            modal.show();
        });
}
</script>

<!-- Script pour exposer les données de la tâche en JSON (si en mode édition) -->
<?php if (isset($task)): ?>
<script data-task>
    <?= json_encode($task) ?>
</script>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
