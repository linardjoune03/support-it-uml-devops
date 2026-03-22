<?php
session_start();
require_once 'db.php';

// Si l'utilisateur n'est pas connecté ou est un technicien (les techniciens ne créent pas de tickets ici)
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Employe') {
    header('Location: index.php');
    exit;
}

// --- RÉCUPÉRATION DES LISTES POUR LE FORMULAIRE ---
$stmt_cat = $pdo->query("SELECT * FROM Categorie ORDER BY libelle ASC");
$categories = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);

$stmt_eq = $pdo->query("SELECT * FROM Equipement ORDER BY numSerie ASC");
$equipements = $stmt_eq->fetchAll(PDO::FETCH_ASSOC);

// --- TRAITEMENT DU FORMULAIRE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = $_POST['titre'];
    $description = $_POST['description'];
    $id_categorie = $_POST['categorie'];
    
    // Si l'utilisateur a choisi "Aucun équipement", on envoie NULL à PostgreSQL
    $id_equipement = !empty($_POST['equipement']) ? $_POST['equipement'] : null;
    
    $id_auteur = $_SESSION['user_id']; 
    
    // L'ACTION SUR LA BASE DE DONNÉES (On ajoute l'équipement)
    $sql = "INSERT INTO Ticket (titre, description, id_auteur, id_categorie, id_equipement) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$titre, $description, $id_auteur, $id_categorie, $id_equipement]);

    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouveau Ticket - Support IT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5 mb-5">
    
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1 class="mb-4">Ouvrir un ticket d'incident</h1>
            
            <div class="card shadow-sm border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Décrivez votre problème</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Titre du problème</label>
                            <input type="text" name="titre" class="form-control" placeholder="Ex: Impossible d'imprimer" required>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Catégorie</label>
                                <select name="categorie" class="form-select" required>
                                    <option value="">-- Choisir une catégorie --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['libelle']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Équipement concerné (Optionnel)</label>
                                <select name="equipement" class="form-select">
                                    <option value="">-- Aucun équipement spécifique --</option>
                                    <?php foreach ($equipements as $eq): ?>
                                        <option value="<?= $eq['id'] ?>">
                                            <?= htmlspecialchars($eq['type']) ?> (<?= htmlspecialchars($eq['numserie']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Description détaillée</label>
                            <textarea name="description" class="form-control" rows="5" placeholder="Expliquez ce qu'il se passe..." required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-success">Envoyer le ticket</button>
                        <a href="index.php" class="btn btn-outline-secondary ms-2">Annuler</a>
                    </form>
                </div>
            </div>

        </div>
    </div>
</body>
</html>