<?php
$erreur = ""; // Pour afficher si le numéro existe déjà

// 1. On vérifie si l'utilisateur a cliqué sur "Créer mon compte"
if (isset($_POST['nom']) && isset($_POST['tel']) && isset($_POST['password']) && isset($_POST['prenom']) && isset($_POST['adresse'])) {
    
    // 2. On récupère toutes les informations tapées dans le formulaire
    $nom_saisi = $_POST['nom'];
    $prenom_saisi = $_POST['prenom'];
    $num_saisi = $_POST['tel']; // Dans le HTML c'est "tel", dans ton JSON c'est "num"
    $adresse_saisie = $_POST['adresse'];
    $infosupp_saisie = $_POST['complement'];
    $mdp_saisi = $_POST['password'];

    // 3. On prépare le profil du nouveau client (exactement comme la structure de ton JSON)
    $nouvel_utilisateur = [
        "nom" => $nom_saisi,
        "prenom" => $prenom_saisi,
        "num" => $num_saisi,
        "address" => $adresse_saisie,
        "infosupp" => $infosupp_saisie,
        "points" => 0,               // Un nouveau client commence avec 0 point
        "password" => $mdp_saisi,
        "type" => "client",          // Par défaut, quelqu'un qui s'inscrit est un client
        "commandes" => []            // Historique vide
    ];

    // 4. On ouvre le fichier JSON actuel
    $fichier = file_get_contents('json/users.json');
    $liste_utilisateurs = json_decode($fichier, true); 

    // Petite sécurité : on vérifie si le numéro de téléphone n'est pas déjà pris
    $numero_existe = false;
    foreach ($liste_utilisateurs as $user) {
        if ($user['num'] == $num_saisi) {
            $numero_existe = true;
            break;
        }
    }

    if ($numero_existe == true) {
        $erreur = "Ce numéro de téléphone possède déjà un compte.";
    } else {
        // 5. On AJOUTE le nouveau client à la liste existante
        $liste_utilisateurs[] = $nouvel_utilisateur;

        // 6. On re-transforme la liste en texte JSON et on SAUVEGARDE le fichier
        // JSON_PRETTY_PRINT permet de garder le fichier users.json lisible et bien aligné
        $nouveau_json = json_encode($liste_utilisateurs, JSON_PRETTY_PRINT);
        file_put_contents('json/users.json', $nouveau_json);

        // 7. C'est un succès ! On le renvoie vers la page de connexion
        header('Location: connexion.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - Bien Harr</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;600;800&display=swap" rel="stylesheet">
</head>
<body>

    <header>
        <nav>
            <input type="checkbox" id="menu-toggle">
            <label for="menu-toggle" class="menu-icon">
                <span></span><span></span><span></span>
            </label>
            <label for="menu-toggle" class="menu-overlay"></label>
            <div class="logo">BIEN <span>HARR</span></div>
            <ul class="menu-links">
                <li><div class="menu-header">BIEN HARR</div></li>
                <li><a href="index.php">Accueil</a></li>
                <li class="has-submenu">
                    <a href="carte.php">La Carte <span class="arrow">➤</span></a>
                    <ul class="submenu">
                        <li><a href="carte.php#entrees">Entrées</a></li>
                        <li><a href="carte.php#plats">Plats Traditionnels</a></li>
                        <li><a href="carte.php#boissons-chaudes">Boissons</a></li>
                        <li><a href="carte.php#desserts">Desserts</a></li>
                    </ul>
                </li>
                <li><a href="connexion.php">Connexion</a></li>
            </ul>
        </nav>
    </header>

    <section class="auth-container">
        <div class="auth-card">
            <h2>Rejoignez la famille Bien Harr</h2>
            <p>Inscrivez-vous pour commander plus vite et cumuler des points de fidélité !</p>

            <?php if ($erreur != ""): ?>
                <p style="color: white; background-color: red; padding: 10px; border-radius: 5px; text-align: center;">
                    <?php echo $erreur; ?>
                </p>
            <?php endif; ?>

            <form action="inscription.php" method="POST" class="auth-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" placeholder="Votre nom" required>
                    </div>
                    <div class="form-group">
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" placeholder="Votre prénom" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="tel">Numéro de téléphone</label>
                    <input type="tel" id="tel" name="tel" placeholder="Ex: 06 12 34 56 78" required>
                </div>

                <div class="form-group">
                    <label for="adresse">Adresse de livraison</label>
                    <input type="text" id="adresse" name="adresse" placeholder="N°, rue, ville et code postal" required>
                </div>

                <div class="form-group">
                    <label for="complement">Informations complémentaires</label>
                    <textarea id="complement" name="complement" rows="3" placeholder="Code interphone, étage, bâtiment..."></textarea>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" placeholder="********" required>
                </div>

                <button type="submit" class="btn-order">Créer mon compte</button>
            </form>

            <div class="auth-footer">
                <p>Déjà inscrit ? <a href="connexion.php">Connectez-vous ici</a></p>
            </div>
        </div>
    </section>

    <footer>
        <p>Bien Harr © 2026 - Projet Yumland</p>
    </footer>

</body>
</html>