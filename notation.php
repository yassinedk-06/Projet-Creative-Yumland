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
            <ul class="menu-links">
                <li><div class="menu-header">Menu</div></li>
                <li><a href="index.html">Accueil</a></li>
                <li class="has-submenu">
                    <a href="carte.html">La Carte <span class="arrow">➤</span></a>
                    <ul class="submenu">
                        <li><a href="carte.html#entrees">Entrées</a></li>
                        <li><a href="carte.html#plats">Plats Traditionnels</a></li>
                        <li><a href="carte.html#boissons">Boissons</a></li>
                        <li><a href="carte.html#desserts">Desserts</a></li>
                    </ul>
                </li>
                <li><a href="profil.html">Mon Profil</a></li>
                <li><a href="index.html" style="color: var(--accent-red);">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <section class="auth-container">
        <div class="auth-card notation-card">
            <h2>Votre avis compte !</h2>
            <p>Commande #421 du 12/02/2026</p>
            <p class="subtitle">Aidez-nous à améliorer l'expérience Bien Harr.</p>

            <form action="profil.html" class="auth-form">
                
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
                <a href="profil.html" class="cancel-link">Annuler</a>
            </form>
        </div>
    </section>

    <footer>
        <p>Bien Harr © 2026 - Projet Yumland</p>
    </footer>

</body>
</html>