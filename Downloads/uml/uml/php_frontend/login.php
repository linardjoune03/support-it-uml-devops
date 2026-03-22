<?php
session_start(); // On démarre la mémoire du navigateur
require_once 'db.php';

// Si l'utilisateur a soumis le formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password']; // Note: dans un vrai projet pro, on crypte les mots de passe !

    // On cherche l'utilisateur dans la base
    $sql = "SELECT * FROM Utilisateur WHERE email = ? AND mot_de_passe = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email, $password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Connexion réussie ! On sauvegarde ses infos en session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nom'] = $user['nom'];
        $_SESSION['user_role'] = $user['role'];
        
        // On l'envoie sur la page d'accueil
        header('Location: index.php');
        exit;
    } else {
        $erreur = "Identifiants incorrects.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Support IT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <div class="row justify-content-center mt-5">
        <div class="col-md-5">
            <h2 class="text-center mb-4">Connexion au Support</h2>
            
            <?php if (isset($erreur)): ?>
                <div class="alert alert-danger"><?= $erreur ?></div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Adresse Email</label>
                            <input type="email" name="email" class="form-control" required placeholder="lina@example.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Mot de passe</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Se connecter</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>