<?php
session_start();

// 1. SÉCURITÉ : L'utilisateur doit être connecté
if (!isset($_SESSION['connecte'])) {
    header('Location: connexion.php');
    exit();
}

// 2. VÉRIFICATION DE LA COMMANDE
$cmd_id = $_GET['id'] ?? ''; // On récupère l'ID passé dans l'URL par le profil
if (empty($cmd_id)) {
    header('Location: profil.php'); // S'il n'y a pas d'ID, on le renvoie au profil
    exit();
}

$erreur = "";
$commande_trouvee = null;
$chemin_commandes = 'json/commandes.json';

// On lit le fichier pour trouver la commande correspondante
if (file_exists($chemin_commandes)) {
    $json_data = file_get_contents($chemin_commandes);
    $toutes_les_commandes = json_decode($json_data, true);

    if ($toutes_les_commandes) {
        foreach ($toutes_les_commandes as $cmd) {
            if ($cmd['id'] === $cmd_id) {
                $commande_trouvee = $cmd;
                break;
            }
        }
    }
}

// Si quelqu'un essaie de noter une commande qui n'existe pas
if (!$commande_trouvee) {
    header('Location: profil.php');
    exit();
}

// 3. TRAITEMENT DU FORMULAIRE (Quand l'utilisateur clique sur "Envoyer mon avis")
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // On récupère les notes (en s'assurant que ce sont bien des nombres entiers)
    $note_livraison = isset($_POST['livraison']) ? (int)$_POST['livraison'] : 0;
    $note_produits = isset($_POST['produits']) ? (int)$_POST['produits'] : 0;
    $commentaire = $_POST['commentaire'] ?? '';

    // On vérifie qu'il a bien coché au moins une étoile pour chaque catégorie
    if ($note_livraison > 0 && $note_produits > 0) {
        
        // On met à jour la commande dans notre tableau PHP
        foreach ($toutes_les_commandes as &$cmd) { // Le "&" permet de modifier directement la ligne
            if ($cmd['id'] === $cmd_id) {
                // Structure demandée : [Note Cuisine, Note Livraison, Commentaire]
                $cmd['note'] = [$note_produits, $note_livraison, $commentaire];
                break;
            }
        }

        // On sauvegarde le fichier JSON mis à jour
        file_put_contents($chemin_commandes, json_encode($toutes_les_commandes, JSON_PRETTY_PRINT));
        
        // ====================================================================
        // NOUVEAU CODE : MISE À JOUR DE LA MOYENNE DU LIVREUR
        // ====================================================================
        $chemin_users = 'json/users.json';
        if (file_exists($chemin_users)) {
            $users_data = file_get_contents($chemin_users);
            $utilisateurs = json_decode($users_data, true);

            if ($utilisateurs) {
                // 1. On cherche le livreur qui a fait cette commande
                foreach ($utilisateurs as &$user) { // Le "&" pour modifier directement
                    if (isset($user['type']) && $user['type'] === 'livreur' && in_array($cmd_id, $user['commandes'])) {
                        
                        $somme_notes = 0;
                        $nombre_notes = 0;

                        // 2. On parcourt toutes les commandes de CE livreur
                        foreach ($user['commandes'] as $id_cmd_livreur) {
                            // On cherche les détails de cette commande
                            foreach ($toutes_les_commandes as $c) {
                                if ($c['id'] === $id_cmd_livreur) {
                                    // La note de livraison est le 2ème chiffre du tableau (index 1)
                                    if (isset($c['note']) && isset($c['note'][1]) && $c['note'][1] > 0) {
                                        $somme_notes += $c['note'][1];
                                        $nombre_notes++;
                                    }
                                    break;
                                }
                            }
                        }

                        // 3. On calcule la moyenne et on met à jour le profil du livreur
                        if ($nombre_notes > 0) {
                            $moyenne = $somme_notes / $nombre_notes;
                            $user['note'] = round($moyenne, 1); // Arrondi à 1 chiffre après la virgule (ex: 4.5)
                        }

                        break; // On a trouvé et mis à jour le livreur, on arrête de chercher
                    }
                }

                // 4. On sauvegarde les utilisateurs avec la nouvelle note du livreur
                file_put_contents($chemin_users, json_encode($utilisateurs, JSON_PRETTY_PRINT));
            }
        }
        // ====================================================================

        // Succès ! On le redirige vers son profil
        header('Location: profil.php');
        exit();
    } else {
        $erreur = "Veuillez donner une note pour la livraison et les produits.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Noter ma commande - Bien Harr</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;600;800&display=swap" rel="stylesheet">
</head>
<body class="user-connected">

    <header>
        <nav>
            <input type="checkbox" id="menu-toggle">
            <label for="menu-toggle" class="menu-icon">
                <span></span><span></span><span></span>
            </label>
            <label for="menu-toggle" class="menu-overlay"></label>
            <div class="logo">BIEN <span>HARR</span></div>

            <div class="header-actions">
                <?php if (isset($_SESSION['type']) && $_SESSION['type'] == 'admin'): ?>
                    <a href="admin.php" class="icon-btn"><i class="fas fa-user-shield"></i> <span class="desktop-only">Admin</span></a>
                <?php endif; ?>
                <?php if (isset($_SESSION['type']) && $_SESSION['type'] == 'restaurateur'): ?>
                    <a href="commandes.php" class="icon-btn"><i class="fas fa-utensils"></i> <span class="desktop-only">Cuisine</span></a>
                <?php endif; ?>
                <?php if (isset($_SESSION['type']) && $_SESSION['type'] == 'livreur'): ?>
                    <a href="livraison.php" class="icon-btn"><i class="fas fa-motorcycle"></i> <span class="desktop-only">Livreur</span></a>
                <?php endif; ?>
            </div>

            <ul class="menu-links">
                <li><div class="menu-header">Menu</div></li>
                <li><a href="index.php">Accueil</a></li>
                <li class="has-submenu">
                    <a href="carte.php">La Carte <span class="arrow">➤</span></a>
                    <ul class="submenu">
                        <li><a href="carte.php#entrees">Entrées</a></li>
                        <li><a href="carte.php#plats">Plats Traditionnels</a></li>
                        <li><a href="carte.php#boissons">Boissons</a></li>
                        <li><a href="carte.php#desserts">Desserts</a></li>
                    </ul>
                </li>
                <li><a href="profil.php">Mon Profil</a></li>
                <li><a href="deconnexion.php" style="color: var(--accent-red);">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <section class="auth-container">
        <div class="auth-card notation-card">
            <h2>Votre avis compte !</h2>
            
            <p>Commande #<?php echo htmlspecialchars($cmd_id); ?> du <?php echo date('d/m/Y', strtotime($commande_trouvee['date'])); ?></p>
            <p class="subtitle">Aidez-nous à améliorer l'expérience Bien Harr.</p>

            <?php if (!empty($erreur)): ?>
                <p style="color: white; background-color: var(--accent-red); padding: 10px; border-radius: 5px; text-align: center;">
                    <?php echo $erreur; ?>
                </p>
            <?php endif; ?>

            <form action="notation.php?id=<?php echo htmlspecialchars($cmd_id); ?>" method="POST" class="auth-form">
                
                <div class="rating-box">
                    <label class="rating-label">Comment s'est passée la livraison ?</label>
                    <div class="stars">
                        <input type="radio" name="livraison" id="livraison-5" value="5"><label for="livraison-5">★</label>
                        <input type="radio" name="livraison" id="livraison-4" value="4"><label for="livraison-4">★</label>
                        <input type="radio" name="livraison" id="livraison-3" value="3"><label for="livraison-3">★</label>
                        <input type="radio" name="livraison" id="livraison-2" value="2"><label for="livraison-2">★</label>
                        <input type="radio" name="livraison" id="livraison-1" value="1"><label for="livraison-1">★</label>
                    </div>
                </div>

                <div class="rating-box">
                    <label class="rating-label">La qualité des plats ?</label>
                    <div class="stars">
                        <input type="radio" name="produits" id="produits-5" value="5"><label for="produits-5">★</label>
                        <input type="radio" name="produits" id="produits-4" value="4"><label for="produits-4">★</label>
                        <input type="radio" name="produits" id="produits-3" value="3"><label for="produits-3">★</label>
                        <input type="radio" name="produits" id="produits-2" value="2"><label for="produits-2">★</label>
                        <input type="radio" name="produits" id="produits-1" value="1"><label for="produits-1">★</label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="commentaire">Un petit commentaire ? (Optionnel)</label>
                    <textarea id="commentaire" name="commentaire" rows="4" placeholder="Dites-nous ce que vous avez aimé ou ce qu'on peut améliorer..."></textarea>
                </div>

                <button type="submit" class="btn-order">Envoyer mon avis</button>
                <a href="profil.php" class="cancel-link">Annuler</a>
            </form>
        </div>
    </section>

    <footer>
        <p>Bien Harr © 2026 - Projet Yumland</p>
    </footer>

</body>
</html>