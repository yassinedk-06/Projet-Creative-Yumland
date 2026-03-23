<?php
session_start();

// 1. SÉCURITÉ : Accès strictement réservé à l'Admin et au Livreur
if (!isset($_SESSION['connecte']) || !in_array($_SESSION['type'], ['admin', 'livreur'])) {
    header('Location: index.php');
    exit();
}

$chemin_commandes = 'json/commandes.json';
$chemin_users = 'json/users.json';

// ====================================================================
// TRAITEMENT : QUAND LE LIVREUR CHOISIT UNE COURSE
// ====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'choisir') {
    $id_commande_choisie = $_POST['id_commande'];
    $id_livreur_connecte = $_SESSION['id']; // L'ID du livreur actuellement connecté

    // On ouvre le fichier users.json
    $users = file_exists($chemin_users) ? json_decode(file_get_contents($chemin_users), true) : [];
    
    // On cherche le livreur dans le tableau
    $livreur_trouve = false;
    foreach ($users as &$user) { // Le "&" permet de modifier la ligne directement
        if ($user['id'] === $id_livreur_connecte) {
            
            // Si le livreur n'a pas encore le tableau "commandes" (pour son historique), on le crée
            if (!isset($user['commandes'])) {
                $user['commandes'] = [];
            }
            
            // On ajoute la commande à son historique (s'il ne l'a pas déjà prise par erreur)
            if (!in_array($id_commande_choisie, $user['commandes'])) {
                $user['commandes'][] = $id_commande_choisie;
            }
            $livreur_trouve = true;
            break;
        }
    }

    // On sauvegarde la modification dans users.json
    if ($livreur_trouve) {
        file_put_contents($chemin_users, json_encode($users, JSON_PRETTY_PRINT));
    }

    // MAINTENANT on l'envoie sur la page de la course avec l'ID dans l'URL !
    header('Location: details_livraison.php?id=' . urlencode($id_commande_choisie));
    exit();
}
// ====================================================================


// 2. LECTURE DES DONNÉES (Pour l'affichage de la page)
$commandes = file_exists($chemin_commandes) ? json_decode(file_get_contents($chemin_commandes), true) : [];
$users = file_exists($chemin_users) ? json_decode(file_get_contents($chemin_users), true) : [];

// 3. PRÉPARATION DES COURSES
$courses_disponibles = [];

foreach ($commandes as $cmd) {
    if ($cmd['etat'] === 'en attente' &&  $cmd['livraison'] === true) {
        
        $adresse_client = "Adresse non précisée";
        $nom_client = "Client Inconnu";

        foreach ($users as $user) {
            // On s'assure qu'on cherche dans les clients (pas les livreurs !)
            if (isset($user['type']) && $user['type'] === 'client' || $user['type'] === 'admin' ) {
                if (isset($user['commandes']) && in_array($cmd['id'], $user['commandes'])) {
                    $adresse_client = $user['address'] ?? "Adresse introuvable";
                    $nom_client = $user['nom'] . ' ' . $user['prenom'];
                    break;
                }
            }
        }

        $cmd['adresse_client'] = $adresse_client;
        $cmd['nom_client'] = $nom_client;
        $courses_disponibles[] = $cmd;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Espace Livreur - Bien Harr</title>
    <link rel="stylesheet" href="style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;800&display=swap" rel="stylesheet">
</head>
<body class="body-livreur">

    <header class="header-livreur">
        <a href="carte.php" class="btn-retour"><i class="fas fa-home"></i></a>
        <h1>COURSES EN ATTENTE</h1>
        <div style="width: 40px;"></div>
    </header>

    <div class="livraison-container">

        <?php if (!empty($courses_disponibles)): ?>
            
            <?php foreach ($courses_disponibles as $course): ?>
                <div class="course-card">
                    <div class="course-id">
                        <span>#<?= strtoupper($course['id']) ?></span>
                        <span class="course-prix"><?= number_format($course['prix'], 2, ',', ' ') ?> €</span>
                    </div>
                    
                    <div class="course-adresse">
                        <i class="fas fa-map-marker-alt"></i> 
                        <strong><?= htmlspecialchars($course['adresse_client']) ?></strong>
                    </div>
                    
                    <div class="course-details">
                        <i class="fas fa-box"></i> <?= count($course['selection']) ?> article(s) à récupérer
                    </div>
                    
                    <form action="livraison.php" method="POST" style="margin: 0;">
                        <input type="hidden" name="action" value="choisir">
                        <input type="hidden" name="id_commande" value="<?= htmlspecialchars($course['id']) ?>">
                        
                        <button type="submit" class="btn-massive" style="cursor: pointer;">
                            CHOISIR <i class="fas fa-motorcycle" style="margin-left: 10px;"></i>
                        </button>
                    </form>

                </div>
            <?php endforeach; ?>

        <?php else: ?>
            
            <div class="livreur-empty-state">
                <i class="fas fa-mug-hot"></i>
                <p>Aucune course disponible pour le moment.<br>Revenez plus tard !</p>
            </div>

        <?php endif; ?>

    </div>

</body>
</html>