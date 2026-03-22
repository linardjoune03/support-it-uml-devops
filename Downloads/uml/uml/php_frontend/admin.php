<?php
session_start();
require_once 'db.php';

// SÉCURITÉ : Si la personne connectée n'est PAS un Admin, on la renvoie à l'accueil !
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Admin') {
    header('Location: index.php');
    exit;
}

// --- AJOUT D'UN ÉQUIPEMENT ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_equipement'])) {
    $type = $_POST['type'];
    $numSerie = $_POST['numSerie'];

    $sql = "INSERT INTO Equipement (type, numSerie) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    
    try {
        $stmt->execute([$type, $numSerie]);
        $message_succes = "L'équipement a bien été ajouté au parc !";
    } catch (PDOException $e) {
        $message_erreur = "Erreur : Ce numéro de série existe probablement déjà.";
    }
}

// --- LISTE DES ÉQUIPEMENTS ---
$stmt_eq = $pdo->query("SELECT * FROM Equipement ORDER BY id DESC");
$equipements = $stmt_eq->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - Support IT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5 mb-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>⚙️ Gestion du Parc Informatique</h1>
        <a href="index.php" class="btn btn-outline-secondary">← Retour aux tickets</a>
    </div>

    <?php if (isset($message_succes)): ?>
        <div class="alert alert-success"><?= $message_succes ?></div>
    <?php endif; ?>
    
    <?php if (isset($message_erreur)): ?>
        <div class="alert alert-danger"><?= $message_erreur ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-dark">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Nouvel Équipement</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="ajouter_equipement" value="1">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Type d'appareil</label>
                            <input type="text" name="type" class="form-control" placeholder="Ex: Imprimante 3D" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Numéro de Série</label>
                            <input type="text" name="numSerie" class="form-control" placeholder="Ex: IMP-3D-001" required>
                        </div>
                        <button type="submit" class="btn btn-dark w-100">Ajouter au parc</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Inventaire actuel</h5>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>N° Série</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($equipements as $eq): ?>
                                <tr>
                                    <td><?= $eq['id'] ?></td>
                                    <td><?= htmlspecialchars($eq['type']) ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($eq['numserie']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>
</html>