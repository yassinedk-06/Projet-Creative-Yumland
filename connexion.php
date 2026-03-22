<?php
// 1. On allume la mémoire du serveur pour cet utilisateur
session_start();

$erreur = ""; // On prépare une boîte vide pour un éventuel message d'erreur

// 2. On vérifie si l'utilisateur a cliqué sur "Se connecter" (le formulaire a envoyé les données)
if (isset($_POST['num']) && isset($_POST['password'])) {
    
    // On récupère exactement ce que l'utilisateur a tapé dans les cases
    $num_saisi = $_POST['num'];
    $mdp_saisi = $_POST['password'];

    // 3. On lit notre fichier JSON (notre base de données)
    $fichier = file_get_contents('json/users.json');
    $liste_utilisateurs = json_decode($fichier, true); 

    $connexion_ok = false; // Par défaut, on part du principe qu'il n'est pas connecté

    // 4. On fouille dans la liste des utilisateurs un par un
    foreach ($liste_utilisateurs as $user) {
        
        // Est-ce que le numéro ET le mot de passe correspondent ?
        if ($user['num'] == $num_saisi && $user['password'] == $mdp_saisi) {
            
            // BINGO ! On le mémorise dans la session
            $_SESSION['connecte'] = true;
            $_SESSION['id'] = $user['id'];
            $_SESSION['type'] = $user['type']; // On retient s'il est admin, client, etc.
            $_SESSION['prenom'] = $user['prenom'];
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['num'] = $user['num'];
            $_SESSION['statut'] = $user['statut'];
            
            $connexion_ok = true; // La connexion est validée
            break; // On a trouvé, on arrête de chercher dans la liste
        }
    }

    // 5. On agit en fonction du résultat
    if ($connexion_ok == true) {
        // Il a le bon mot de passe -> On le redirige vers l'accueil
        header('Location: index.php');
        exit();
    } else {
        // Il s'est trompé -> On remplit la boîte d'erreur
        $erreur = "Numéro ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Bien Harr</title>
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
            <h2>Ravi de vous revoir !</h2>
            <p>Connectez-vous pour accéder à vos commandes et vos points fidélité.</p>

            <?php if ($erreur != ""): ?>
                <p style="color: white; background-color: red; padding: 10px; border-radius: 5px; text-align: center;">
                    <?php echo $erreur; ?>
                </p>
            <?php endif; ?>

            <form action="connexion.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="num">Numéro de téléphone</label>
                    <input type="text" id="num" name="num" placeholder="Ex: 0606060606" required>
                </div>

                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <label for="password">Mot de passe</label>
                        
                    </div>
                    <input type="password" id="password" name="password" placeholder="********" required>
                </div>

                

                <button type="submit" class="btn-order">Se connecter</button>
            </form>

            <div class="auth-footer">
                <p>Nouveau chez Bien Harr ? <a href="inscription.php">Créez un compte ici</a></p>
            </div>
        </div>
    </section>

    <footer>
        <p>Bien Harr © 2026 - Projet Yumland</p>
    </footer>

</body>
</html>