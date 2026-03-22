<?php
session_start();
session_destroy(); // On vide la mémoire
header('Location: login.php'); // On renvoie vers la page de connexion
exit;
?>