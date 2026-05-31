<?php
// 1. On allume la mémoire du serveur pour cet utilisateur
session_start();
require_once 'fonctions.php';
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
        ajouterLog($user['id'], $user['type'], "CONNEXION", "L'utilisateur s'est connecté.");
        header('Location: index.php');
        exit();
    } else {
        // Il s'est trompé -> On remplit la boîte d'erreur
        
        $erreur = "Numéro de téléphone ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Bien Harr</title>
    <?php
    // 1. On définit les thèmes autorisés (Sécurité pour éviter qu'on injecte n'importe quoi)
    $themes_autorises = ['style.css', 'style-dark.css'];
    $theme_actuel = 'style.css'; // Le thème par défaut

    // 2. On vérifie si le cookie existe ET si sa valeur est cohérente (autorisée)
    if (isset($_COOKIE['theme']) && in_array($_COOKIE['theme'], $themes_autorises)) {
        $theme_actuel = $_COOKIE['theme'];
    }
    ?>
    <link id="theme-style" rel="stylesheet" href="<?= htmlspecialchars($theme_actuel) ?>">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                <p style="color: white; background-color: #c0392b; padding: 10px; border-radius: 5px; text-align: center; font-weight: bold;">
                    <?php echo $erreur; ?>
                </p>
            <?php endif; ?>

            <form action="connexion.php" method="POST" class="auth-form" id="formConnexion">
                <div class="form-group">
                    <label for="num">Numéro de téléphone</label>
                    <input type="tel" id="num" name="num" placeholder="Ex: 0606060606">
                    <span class="error-msg" style="color: var(--accent-red); font-size: 0.8rem; margin-top: 5px;"></span>
                </div>

                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <label for="password">Mot de passe</label>
                    </div>
                    <div style="position: relative;">
                        <input type="password" id="password" name="password" placeholder="********" style="width: 100%; box-sizing: border-box;">
                        <i class="fas fa-eye" id="togglePassword" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #888;"></i>
                    </div>
                    <span class="error-msg" style="color: var(--accent-red); font-size: 0.8rem; margin-top: 5px;"></span>
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

    <script>
        // 1. GESTION DE L'AFFICHAGE DU MOT DE PASSE
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function (e) {
            // Bascule le type de l'input entre "password" et "text"
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            // Change l'icône (œil ouvert / œil barré)
            this.classList.toggle('fa-eye-slash');
        });

        // 2. VALIDATION DU FORMULAIRE SANS RECHARGER LA PAGE
        document.getElementById('formConnexion').addEventListener('submit', function(e) {
            let isValid = true;

            // Récupération des champs
            const num = document.getElementById('num');
            const pass = document.getElementById('password');

            // Regex (Numéro de téléphone français standard)
            const regexTel = /^0[1-9]([-. ]?[0-9]{2}){4}$/;

            // Fonction pour afficher l'erreur
            function showError(input, message) {
                let errorSpan = input.parentNode.querySelector('.error-msg');
                if(!errorSpan) {
                    errorSpan = input.parentNode.parentNode.querySelector('.error-msg'); // Cas du mot de passe avec le wrapper
                }
                if(errorSpan) errorSpan.textContent = message;
                input.style.borderColor = 'var(--accent-red)';
                isValid = false;
            }

            // Fonction pour effacer l'erreur
            function clearError(input) {
                let errorSpan = input.parentNode.querySelector('.error-msg');
                if(!errorSpan) {
                    errorSpan = input.parentNode.parentNode.querySelector('.error-msg');
                }
                if(errorSpan) errorSpan.textContent = '';
                input.style.borderColor = ''; // Remet la bordure par défaut
            }

            // On nettoie toutes les erreurs avant de vérifier
            clearError(num); 
            clearError(pass);

            // VÉRIFICATIONS :
            if (!regexTel.test(num.value.trim())) {
                showError(num, "Le numéro de téléphone est invalide (ex: 0612345678).");
            }

            if (pass.value.trim() === "") {
                showError(pass, "Veuillez saisir votre mot de passe.");
            }

            // Si isValid est passé à FALSE, on bloque l'envoi au serveur !
            if (!isValid) {
                e.preventDefault(); 
            }
        });
    </script>

</body>
</html>