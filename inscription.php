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
                <li><a href="profil.php">Mon Compte</a></li>
            </ul>
        </nav>
    </header>

    <section class="auth-container">
        <div class="auth-card">
            <h2>Rejoignez la famille Bien Harr</h2>
            <p>Inscrivez-vous pour commander plus vite et cumuler des points de fidélité !</p>

            <form action="#" class="auth-form">
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
                <p>Déjà inscrit ? <a href="connexion.html">Connectez-vous ici</a></p>
            </div>
        </div>
    </section>

    <footer>
        <p>Bien Harr © 2026 - Projet Yumland</p>
    </footer>

</body>
</html>