<?php
$host = "localhost";
$port = "5432";
$dbname = "support_it";
$user = "mon_admin"; // Le nom qu'on a créé dans PostgreSQL
$password = "azerty123"; // Ton mot de passe

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>