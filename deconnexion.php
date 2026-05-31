<?php
// 1. On récupère la session en cours
session_start();
require_once 'fonctions.php';
ajouterLog($_SESSION['id'], $_SESSION['type'], "DECONNEXION", "L'utilisateur s'est déconnecté.");
// 2. On vide toutes les variables de session (nom, type, et... LE PANIER !)
$_SESSION = array();

// 3. On détruit complètement la session du serveur
session_destroy();

// 4. On redirige instantanément vers la page d'accueil
header('Location: index.php');
exit();
?>