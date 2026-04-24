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

    // 3. On OUVRE le fichier JSON AVANT de créer le profil
    $chemin_users = 'json/users.json';
    $liste_utilisateurs = file_exists($chemin_users) ? json_decode(file_get_contents($chemin_users), true) : []; 
    if (!$liste_utilisateurs) {
        $liste_utilisateurs = [];
    }

    // 4. On cherche le plus grand ID existant et on vérifie le numéro de téléphone
    $numero_existe = false;
    $dernier_id_num = 0;

    foreach ($liste_utilisateurs as $user) {
        if (isset($user['num']) && $user['num'] == $num_saisi) {
            $numero_existe = true;
        }
        if (isset($user['id'])) {
            $id_actuel = (int)$user['id']; 
            if ($id_actuel > $dernier_id_num) {
                $dernier_id_num = $id_actuel;
            }
        }
    }

    if ($numero_existe == true) {
        $erreur = "Ce numéro de téléphone possède déjà un compte.";
    } else {
        // 5. On calcule le nouvel ID
        $nouveau_num = $dernier_id_num + 1;
        $nouvel_id = sprintf("%04d", $nouveau_num);

        // 6. On prépare le profil
        $nouvel_utilisateur = [
            "id" => $nouvel_id,
            "nom" => $nom_saisi,
            "prenom" => $prenom_saisi,
            "num" => $num_saisi,
            "address" => $adresse_saisie,
            "infosupp" => $infosupp_saisie,
            "points" => 0,               
            "statut" => "basic",
            "password" => $mdp_saisi,
            "type" => "client",          
            "commandes" => []            
        ];

        // 7. On AJOUTE et on SAUVEGARDE
        $liste_utilisateurs[] = $nouvel_utilisateur;
        $nouveau_json = json_encode($liste_utilisateurs, JSON_PRETTY_PRINT);
        file_put_contents($chemin_users, $nouveau_json);

        // 8. C'est un succès ! 
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
    <?php
    $themes_autorises = ['style.css', 'style-dark.css'];
    $theme_actuel = 'style.css';
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
            <h2>Rejoignez la famille Bien Harr</h2>
            <p>Inscrivez-vous pour commander plus vite et cumuler des points de fidélité !</p>

            <?php if ($erreur != ""): ?>
                <p style="color: white; background-color: #c0392b; padding: 10px; border-radius: 5px; text-align: center; font-weight: bold;">
                    <?php echo $erreur; ?>
                </p>
            <?php endif; ?>

            <form action="inscription.php" method="POST" class="auth-form" id="formInscription">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" placeholder="Votre nom" maxlength="30">
                        <div style="display: flex; justify-content: space-between; margin-top: 5px;">
                            <span class="error-msg" style="color: var(--accent-red); font-size: 0.8rem;"></span>
                            <span id="counter-nom" style="font-size: 0.8rem; color: #888;">0/30</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" placeholder="Votre prénom" maxlength="30">
                        <div style="display: flex; justify-content: space-between; margin-top: 5px;">
                            <span class="error-msg" style="color: var(--accent-red); font-size: 0.8rem;"></span>
                            <span id="counter-prenom" style="font-size: 0.8rem; color: #888;">0/30</span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="tel">Numéro de téléphone</label>
                    <input type="tel" id="tel" name="tel" placeholder="Ex: 0612345678" maxlength="10">
                    <div style="display: flex; justify-content: space-between; margin-top: 5px;">
                        <span class="error-msg" style="color: var(--accent-red); font-size: 0.8rem;"></span>
                        <span id="counter-tel" style="font-size: 0.8rem; color: #888;">0/10</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="adresse">Adresse de livraison</label>
                    <input type="text" id="adresse" name="adresse" placeholder="N°, rue, ville et code postal" maxlength="100">
                    <div style="display: flex; justify-content: space-between; margin-top: 5px;">
                        <span class="error-msg" style="color: var(--accent-red); font-size: 0.8rem;"></span>
                        <span id="counter-adresse" style="font-size: 0.8rem; color: #888;">0/100</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="complement">Informations complémentaires</label>
                    <textarea id="complement" name="complement" rows="3" placeholder="Code interphone, étage, bâtiment..." maxlength="150"></textarea>
                    <div style="display: flex; justify-content: space-between; margin-top: 5px;">
                        <span class="error-msg" style="color: var(--accent-red); font-size: 0.8rem;"></span>
                        <span id="counter-complement" style="font-size: 0.8rem; color: #888;">0/150</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <div style="position: relative;">
                        <input type="password" id="password" name="password" placeholder="********" style="width: 100%; box-sizing: border-box;" maxlength="50">
                        <i class="fas fa-eye" id="togglePassword" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #888;"></i>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-top: 5px;">
                        <span class="error-msg" style="color: var(--accent-red); font-size: 0.8rem;"></span>
                        <span id="counter-password" style="font-size: 0.8rem; color: #888;">0/50</span>
                    </div>
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

    <script>
        // 1. GESTION DE L'AFFICHAGE DU MOT DE PASSE (Œil)
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function (e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });

        // 2. COMPTEURS DE CARACTÈRES EN TEMPS RÉEL (Nouveau !)
        function setupCharCounter(inputId, maxLimit) {
            const input = document.getElementById(inputId);
            const counter = document.getElementById('counter-' + inputId);
            
            if (input && counter) {
                // 'input' se déclenche à chaque fois qu'on tape ou efface une touche
                input.addEventListener('input', function() {
                    const length = this.value.length;
                    counter.textContent = length + '/' + maxLimit;
                    
                    // Si on atteint la limite, on met le compteur en rouge
                    if (length >= maxLimit) {
                        counter.style.color = 'var(--accent-red)';
                        counter.style.fontWeight = 'bold';
                    } else {
                        counter.style.color = '#888';
                        counter.style.fontWeight = 'normal';
                    }
                });
            }
        }

        // On active la fonction pour chaque champ avec sa limite
        setupCharCounter('nom', 30);
        setupCharCounter('prenom', 30);
        setupCharCounter('tel', 10);
        setupCharCounter('adresse', 100);
        setupCharCounter('complement', 150);
        setupCharCounter('password', 50);

        // 3. VALIDATION DU FORMULAIRE SANS RECHARGER LA PAGE
        document.getElementById('formInscription').addEventListener('submit', function(e) {
            let isValid = true;

            const nom = document.getElementById('nom');
            const prenom = document.getElementById('prenom');
            const tel = document.getElementById('tel');
            const adresse = document.getElementById('adresse');
            const pass = document.getElementById('password');

            const regexNom = /^[a-zA-ZÀ-ÿ\s\-]{2,}$/; 
            const regexTel = /^0[1-9]([-. ]?[0-9]{2}){4}$/;
            const regexPass = /^(?=.*[A-Z])(?=.*\d).{8,}$/;

            function showError(input, message) {
                let errorSpan = input.parentNode.querySelector('.error-msg');
                if(!errorSpan) {
                    errorSpan = input.parentNode.parentNode.querySelector('.error-msg');
                }
                errorSpan.textContent = message;
                input.style.borderColor = 'var(--accent-red)';
                isValid = false;
            }

            function clearError(input) {
                let errorSpan = input.parentNode.querySelector('.error-msg');
                if(!errorSpan) {
                    errorSpan = input.parentNode.parentNode.querySelector('.error-msg');
                }
                if(errorSpan) errorSpan.textContent = '';
                input.style.borderColor = ''; 
            }

            clearError(nom); clearError(prenom); clearError(tel); clearError(adresse); clearError(pass);

            if (!regexNom.test(nom.value.trim())) {
                showError(nom, "Le nom est invalide (lettres uniquement, min. 2).");
            }
            if (!regexNom.test(prenom.value.trim())) {
                showError(prenom, "Le prénom est invalide (lettres uniquement, min. 2).");
            }
            if (!regexTel.test(tel.value.trim())) {
                showError(tel, "Le numéro est invalide (ex: 0612345678).");
            }
            if (adresse.value.trim().length < 5) {
                showError(adresse, "Veuillez saisir une adresse complète.");
            }
            if (!regexPass.test(pass.value)) {
                showError(pass, "Le mot de passe doit faire au moins 8 caractères, 1 majuscule et 1 chiffre.");
            }

            if (!isValid) {
                e.preventDefault(); 
            }
        });
    </script>

</body>
</html>