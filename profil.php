<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Profil - Bien Harr</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="user-connected"> <header>
        <nav>
            <input type="checkbox" id="menu-toggle">
            <label for="menu-toggle" class="menu-icon">
                <span></span><span></span><span></span>
            </label>
            <label for="menu-toggle" class="menu-overlay"></label>
            <div class="logo">BIEN <span>HARR</span></div>
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
                <li><a href="profil.php" class="active">Mon Profil</a></li>
                <li><a href="index.php" style="color: var(--accent-red);">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <main class="profile-container">
        
        <section class="profile-card">
            <div class="card-header">
                <h2>Mes Informations</h2>
                <p>Gérez vos coordonnées de livraison</p>
            </div>
            
            <div class="info-grid">
                <div class="info-item">
                    <label>Nom & Prénom</label>
                    <div class="info-value">
                        <span>Ahmed Ben Salah</span>
                        <i class="fas fa-pencil-alt edit-icon" title="Modifier"></i>
                    </div>
                </div>
                <div class="info-item">
                    <label>Téléphone</label>
                    <div class="info-value">
                        <span>06 12 34 56 78</span>
                        <i class="fas fa-pencil-alt edit-icon"></i>
                    </div>
                </div>
                <div class="info-item">
                    <label>Adresse</label>
                    <div class="info-value">
                        <span>12 Rue de la Kasbah, 75001 Paris</span>
                        <i class="fas fa-pencil-alt edit-icon"></i>
                    </div>
                </div>
                <div class="info-item">
                    <label>Complément d'adresse</label>
                    <div class="info-value">
                        <span>Code 1234, 2ème étage droite</span>
                        <i class="fas fa-pencil-alt edit-icon"></i>
                    </div>
                </div>
            </div>
        </section>

        <section class="loyalty-card">
            <div class="loyalty-content">
                <i class="fas fa-crown gold-crown"></i>
                <div class="loyalty-text">
                    <h3>Compte Fidélité</h3>
                    <p class="points"><span>450</span> Points</p>
                    <p class="reward">Plus que 50 points pour un dessert offert !</p>
                </div>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: 80%;"></div>
            </div>
        </section>

        <section class="history-card">
            <h3>Historique des commandes</h3>
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Commande</th>
                        <th>Prix</th>
                        <th>Statut</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>12/02/2026</td>
                        <td>Couscous Agneau + Boga</td>
                        <td>24,50 €</td>
                        <td><span class="status delivered">Livré</span></td>
                        <td><a href="notation.html" class="rate-link">Noter</a></td>
                    </tr>
                    <tr>
                        <td>05/02/2026</td>
                        <td>Ojja Merguez + Thé</td>
                        <td>18,00 €</td>
                        <td><span class="status delivered">Livré</span></td>
                        <td>⭐⭐⭐⭐⭐</td>
                    </tr>
                </tbody>
            </table>
        </section>

    </main>

    <footer>
        <p>Bien Harr © 2026 - Projet Yumland</p>
    </footer>

</body>
</html>