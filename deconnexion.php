<?php
// 1. On récupère la session en cours
session_start();
$fichier = 'json/cart.json';
$donne = [];
file_put_contents($fichier,json_encode($donne, JSON_PRETTY_PRINT));

// 2. On vide toutes les variables de session (nom, type, etc.)
$_SESSION = array();

// 3. On détruit complètement la session
session_destroy();

// 4. On redirige instantanément vers la page d'accueil (index.php)
header('Location: index.php');
exit();
?>