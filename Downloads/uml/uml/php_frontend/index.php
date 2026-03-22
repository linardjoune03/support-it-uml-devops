<?php 
session_start();

// Si l'utilisateur n'est pas connecté, on le vire vers la page de login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'db.php'; 
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Support IT - Tableau de bord</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    
    <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded mb-4 shadow-sm">
        <div>
            Connecté en tant que : <strong><?= htmlspecialchars($_SESSION['user_nom']) ?></strong> 
            <span class="badge bg-secondary"><?= htmlspecialchars($_SESSION['user_role']) ?></span>
        </div>
        <a href="logout.php" class="btn btn-sm btn-danger">Déconnexion</a>
    </div>

    <h1 class="mb-4">Tickets d'incidents</h1>
    
    <?php if ($_SESSION['user_role'] === 'Employe'): ?>
        <a href="creer_ticket.php" class="btn btn-success mb-3">+ Nouveau Ticket</a>
    <?php endif; ?>

    <?php if ($_SESSION['user_role'] === 'Admin'): ?>
        <a href="admin.php" class="btn btn-dark mb-3">⚙️ Gestion du Parc</a>
    <?php endif; ?>

    <table class="table table-striped shadow-sm border">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Statut</th>
                <th>Auteur</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // On prépare la requête en fonction du rôle
            if ($_SESSION['user_role'] === 'Employe') {
                // Règle 1 : L'employé ne voit que SES tickets
                $sql = "SELECT t.id, t.titre, t.statut, u.nom 
                        FROM Ticket t 
                        JOIN Utilisateur u ON t.id_auteur = u.id 
                        WHERE t.id_auteur = ? 
                        ORDER BY t.id DESC";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_SESSION['user_id']]);
            } else {
                // Règle 2 : Le technicien (Kenza ou John) voit TOUS les tickets
                $sql = "SELECT t.id, t.titre, t.statut, u.nom 
                        FROM Ticket t 
                        JOIN Utilisateur u ON t.id_auteur = u.id 
                        ORDER BY t.id DESC";
                $stmt = $pdo->query($sql);
            }

            // On affiche les résultats ligne par ligne
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                
                // On met une couleur différente selon le statut
                $badge_color = 'bg-info text-dark'; // Bleu clair par défaut
                if ($row['statut'] === 'Résolu') {
                    $badge_color = 'bg-success'; // Vert
                } elseif ($row['statut'] === 'En cours') {
                    $badge_color = 'bg-warning text-dark'; // Jaune
                }

                echo "<tr>
                        <td>{$row['id']}</td>
                        <td>" . htmlspecialchars($row['titre']) . "</td>
                        <td><span class='badge {$badge_color}'>{$row['statut']}</span></td>
                        <td>" . htmlspecialchars($row['nom']) . "</td>
                        <td>
                            <a href='voir_ticket.php?id={$row['id']}' class='btn btn-sm btn-primary'>Voir les détails</a>
                        </td>
                      </tr>";
            }
            ?>
        </tbody>
    </table>

</body>
</html>