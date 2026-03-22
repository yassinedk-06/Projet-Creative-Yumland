<?php 

session_start(); 
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bien Harr - Restaurant Tunisien</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

            <div class="header-actions">
                
                <?php if (isset($_SESSION['connecte']) && $_SESSION['type'] == 'admin'): ?>
                    <a href="admin.php" class="icon-btn" title="Espace Admin"> 
                        <i class="fas fa-user-shield"></i> <span class="desktop-only">Admin</span>
                    </a>
                    <a href="livraison.php" class="icon-btn" title="Espace Livreur">Livreur</a>
                <?php endif; ?>

                

                <?php if (isset($_SESSION['connecte']) && $_SESSION['type'] == 'livreur'): ?>
                    <a href="livraison.php" class="icon-btn" title="Espace Livreur">
                        <i class="fas fa-motorcycle"></i> <span class="desktop-only">Livreur</span>
                    </a>
                <?php endif; ?>

            </div>

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
                
                <?php if (isset($_SESSION['connecte'])): ?>
                    <li><a href="profil.php">Mon Compte</a></li>
                    <li><a href="deconnexion.php" style="color: var(--accent-red);">Déconnexion</a></li>
                <?php else: ?>
                    <li><a href="connexion.php">Connexion</a></li>
                    <li><a href="inscription.php">Inscription</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <section class="hero">
        <div class="hero-content">
            <?php if (isset($_SESSION['connecte'])): ?>
                <h1>Bonjour <?php echo htmlspecialchars($_SESSION['prenom']); ?> !</h1>
                <p>Prêt pour une bonne commande ? L'art du piment, l'âme de Tunis.</p>
            <?php else: ?>
                <h1>L'art du piment, l'âme de Tunis.</h1>
                <p>Une cuisine authentique dans un écrin de modernité.</p>
            <?php endif; ?>
            
            <div class="search-bar">
                <input type="text" placeholder="Envie d'un Couscous, d'une Brick ?">
                <button>Chercher</button>
            </div>
        </div>
    </section>

    <section class="plat-du-jour">
        <h2>Nos Suggestions du Moment</h2>
        
        <div class="cards-grid">

            <div class="card">
                <img src="src/couscous.jpg" alt="Couscous Royal">
                <div class="card-info">
                    <h3>Couscous Royal</h3>
                    <p>Semoule fine, légumes du soleil et notre harissa maison.</p>
                    <span class="price">18,50 €</span>
                    <button class="btn-order">Commander</button>
                </div>
            </div>

            <div class="card">
                <img src="src/ojja.webp" alt="Ojja Merguez">
                <div class="card-info">
                    <h3>Ojja Merguez</h3>
                    <p>Oeufs brouillés dans une sauce tomate pimentée aux merguez.</p>
                    <span class="price">14,90 €</span>
                    <button class="btn-order">Commander</button>
                </div>
            </div>

            <div class="card">
                <img src="src/kafteji.jpg" alt="Kafteji">
                <div class="card-info">
                    <h3>Kafteji Traditionnel</h3>
                    <p>Mélange de légumes frits, oeufs, persil et épices.</p>
                    <span class="price">13,50 €</span>
                    <button class="btn-order">Commander</button>
                </div>
            </div>

        </div>
    </section>

    <footer>
        <p>Bien Harr © 2026 - Projet Yumland</p>
    </footer>

</body>
</html>