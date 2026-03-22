<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    die("Erreur : Aucun ticket sélectionné.");
}
$id_ticket = $_GET['id'];

// --- ACTION TECHNICIEN : CHANGER LE STATUT ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nouveau_statut'])) {
    if ($_SESSION['user_role'] === 'Technicien') {
        $statut = $_POST['statut'];
        $sql_update = "UPDATE Ticket SET statut = ? WHERE id = ?";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute([$statut, $id_ticket]);
        header("Location: voir_ticket.php?id=" . $id_ticket);
        exit;
    }
}

// --- ACTION TECHNICIEN : S'ASSIGNER LE TICKET ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assigner_ticket'])) {
    if ($_SESSION['user_role'] === 'Technicien') {
        $id_tech = $_SESSION['user_id'];
        $sql_assign = "UPDATE Ticket SET id_technicien = ?, statut = 'En cours' WHERE id = ?";
        $stmt_assign = $pdo->prepare($sql_assign);
        $stmt_assign->execute([$id_tech, $id_ticket]);
        header("Location: voir_ticket.php?id=" . $id_ticket);
        exit;
    }
}

// --- ACTION : AJOUTER UN COMMENTAIRE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nouveau_commentaire'])) {
    $contenu = $_POST['contenu'];
    $sql_insert = "INSERT INTO Commentaire (contenu, id_ticket) VALUES (?, ?)";
    $stmt_insert = $pdo->prepare($sql_insert);
    $stmt_insert->execute([$contenu, $id_ticket]);
    header("Location: voir_ticket.php?id=" . $id_ticket);
    exit;
}

// --- NOUVEAU : RÉCUPÉRATION DES DONNÉES (AVEC L'ÉQUIPEMENT) ---
$sql = "SELECT t.*, u.nom AS auteur, c.libelle AS categorie, tech_u.nom AS technicien_nom, 
               e.type AS equipement_type, e.numserie AS equipement_sn
        FROM Ticket t 
        JOIN Utilisateur u ON t.id_auteur = u.id 
        JOIN Categorie c ON t.id_categorie = c.id
        LEFT JOIN Utilisateur tech_u ON t.id_technicien = tech_u.id
        LEFT JOIN Equipement e ON t.id_equipement = e.id
        WHERE t.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_ticket]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    die("Erreur : Ce ticket n'existe pas.");
}

// --- RÉCUPÉRATION DES COMMENTAIRES ---
$sql_commentaires = "SELECT * FROM Commentaire WHERE id_ticket = ? ORDER BY dateEnvoi ASC";
$stmt_commentaires = $pdo->prepare($sql_commentaires);
$stmt_commentaires->execute([$id_ticket]);
$commentaires = $stmt_commentaires->fetchAll(PDO::FETCH_ASSOC);

// On prépare la couleur du badge statut
$badge_color = 'bg-info text-dark';
if ($ticket['statut'] === 'Résolu') $badge_color = 'bg-success';
if ($ticket['statut'] === 'En cours') $badge_color = 'bg-warning text-dark';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ticket #<?= $ticket['id'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5 mb-5">
    
    <a href="index.php" class="btn btn-outline-secondary mb-4">← Retour à la liste</a>
    
    <div class="card shadow-sm border-primary mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h3 class="mb-0">Ticket #<?= $ticket['id'] ?> : <?= htmlspecialchars($ticket['titre']) ?></h3>
            <span class="badge <?= $badge_color ?> fs-6"><?= htmlspecialchars($ticket['statut']) ?></span>
        </div>
        <div class="card-body">
            <p class="text-muted mb-2">
                Ouvert par <strong><?= htmlspecialchars($ticket['auteur']) ?></strong> 
                | Catégorie : <span class="badge bg-secondary"><?= htmlspecialchars($ticket['categorie']) ?></span>
            </p>
            
            <p class="mb-2">
                <strong>Technicien en charge :</strong> 
                <?php if ($ticket['id_technicien']): ?>
                    <span class="badge bg-success"><?= htmlspecialchars($ticket['technicien_nom']) ?></span>
                <?php else: ?>
                    <span class="badge bg-danger">Non assigné</span>
                <?php endif; ?>
            </p>

            <p class="mb-4">
                <strong>Équipement concerné :</strong> 
                <?php if ($ticket['equipement_type']): ?>
                    <?= htmlspecialchars($ticket['equipement_type']) ?> 
                    <span class="text-muted">(N° Série : <?= htmlspecialchars($ticket['equipement_sn']) ?>)</span>
                <?php else: ?>
                    <span class="text-muted text-italic">Aucun équipement spécifié</span>
                <?php endif; ?>
            </p>

            <div class="p-3 bg-light border rounded mb-3">
                <?= nl2br(htmlspecialchars($ticket['description'])) ?>
            </div>

            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Technicien' && $ticket['statut'] !== 'Résolu'): ?>
                <div class="p-3 bg-warning bg-opacity-10 border border-warning rounded d-flex justify-content-between align-items-center">
                    <form method="POST" action="" class="d-flex align-items-center gap-2 m-0">
                        <input type="hidden" name="nouveau_statut" value="1">
                        <label class="fw-bold text-warning-emphasis">Statut :</label>
                        <select name="statut" class="form-select w-auto">
                            <option value="Ouvert" <?= $ticket['statut'] == 'Ouvert' ? 'selected' : '' ?>>Ouvert</option>
                            <option value="En cours" <?= $ticket['statut'] == 'En cours' ? 'selected' : '' ?>>En cours</option>
                            <option value="Résolu" <?= $ticket['statut'] == 'Résolu' ? 'selected' : '' ?>>Résolu</option>
                        </select>
                        <button type="submit" class="btn btn-warning btn-sm">Mettre à jour</button>
                    </form>

                    <?php if (!$ticket['id_technicien']): ?>
                        <form method="POST" action="" class="m-0">
                            <input type="hidden" name="assigner_ticket" value="1">
                            <button type="submit" class="btn btn-success btn-sm">Prendre en charge ce ticket</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <h4>Fil de discussion</h4>
    <div class="mb-4">
        <?php if (count($commentaires) === 0): ?>
            <p class="text-muted">Aucun commentaire pour le moment.</p>
        <?php else: ?>
            <?php foreach ($commentaires as $com): ?>
                <div class="card mb-2 shadow-sm">
                    <div class="card-body py-2">
                        <small class="text-muted">Le <?= date('d/m/Y à H:i', strtotime($com['dateenvoi'])) ?></small>
                        <p class="mb-0 mt-1"><?= nl2br(htmlspecialchars($com['contenu'])) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if ($ticket['statut'] !== 'Résolu'): ?>
        <div class="card shadow-sm border-success">
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="nouveau_commentaire" value="1">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Ajouter une réponse :</label>
                        <textarea name="contenu" class="form-control" rows="3" required placeholder="Tapez votre message ici..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">Envoyer la réponse</button>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-secondary text-center">
            🔒 Ce ticket est résolu. Le fil de discussion est fermé.
        </div>
    <?php endif; ?>

</body>
</html>