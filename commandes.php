<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Cuisine & Commandes - Bien Harr</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;600;800&display=swap" rel="stylesheet">
</head>
<body class="admin-page">

    <header>
        <nav>
            <input type="checkbox" id="menu-toggle">
            <label for="menu-toggle" class="menu-icon">
                <span></span><span></span><span></span>
            </label>
            <label for="menu-toggle" class="menu-overlay"></label>
            <div class="logo">BIEN <span>HARR</span></div>
            <ul class="menu-links">
                <li><div class="menu-header">RESTAURATEUR</div></li>
                <li><a href="index.html">Retour au Site</a></li>
                <li><a href="admin.html">Gestion Clients</a></li>
                <li><a href="commandes.html" class="active">Cuisine & Livraisons</a></li>
                <li><a href="index.html" style="color: var(--accent-red);">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <main class="admin-container">
        
        <div class="admin-header">
            <h1>Suivi des Commandes</h1>
            <div class="kitchen-stats">
                <span class="stat-badge urgent"><i class="fas fa-fire"></i> 3 à préparer</span>
                <span class="stat-badge info"><i class="fas fa-motorcycle"></i> 2 en livraison</span>
            </div>
        </div>

        <div class="kitchen-board">
            
            <div class="column to-do">
                <div class="column-header">
                    <h2><i class="fas fa-utensils"></i> En Cuisine</h2>
                </div>
                
                <div class="order-card">
                    <div class="order-header">
                        <span class="order-id">#C044</span>
                        <span class="order-time">12:30</span>
                    </div>
                    <div class="order-client">
                        <i class="fas fa-user"></i> Sarah Connor
                    </div>
                    <ul class="order-items">
                        <li><strong>1x</strong> Couscous Royal</li>
                        <li><strong>1x</strong> Brik à l'oeuf</li>
                        <li><strong>2x</strong> Boga Cidre</li>
                    </ul>
                    <div class="order-actions">
                        <button class="btn-kitchen ready">
                            <i class="fas fa-check"></i> Prêt pour livraison
                        </button>
                    </div>
                </div>

                <div class="order-card">
                    <div class="order-header">
                        <span class="order-id">#C045</span>
                        <span class="order-time">12:35</span>
                    </div>
                    <div class="order-client">
                        <i class="fas fa-user"></i> Mohamed Dridi
                    </div>
                    <ul class="order-items">
                        <li><strong>2x</strong> Kafteji</li>
                        <li><strong>1x</strong> Pain Tabouna (Extra)</li>
                    </ul>
                    <div class="order-actions">
                        <button class="btn-kitchen ready">
                            <i class="fas fa-check"></i> Prêt pour livraison
                        </button>
                    </div>
                </div>
                
                <div class="order-card">
                    <div class="order-header">
                        <span class="order-id">#C046</span>
                        <span class="order-time warning">12:42</span>
                    </div>
                    <div class="order-client">
                        <i class="fas fa-user"></i> Inès Ben Ali
                    </div>
                    <ul class="order-items">
                        <li><strong>1x</strong> Ojja Merguez</li>
                    </ul>
                    <div class="order-actions">
                        <button class="btn-kitchen ready">
                            <i class="fas fa-check"></i> Prêt pour livraison
                        </button>
                    </div>
                </div>

            </div>

            <div class="column in-progress">
                <div class="column-header">
                    <h2><i class="fas fa-motorcycle"></i> En Cours de Livraison</h2>
                </div>

                <div class="order-card delivery-mode">
                    <div class="order-header">
                        <span class="order-id">#C042</span>
                        <span class="delivery-status">Parti à 12:15</span>
                    </div>
                    <div class="order-client">
                        <i class="fas fa-map-marker-alt"></i> Ahmed Ben Salah<br>
                        <small>12 Rue de la Kasbah</small>
                    </div>
                    <div class="order-actions">
                        <button class="btn-kitchen disabled">
                            <i class="fas fa-clock"></i> En route...
                        </button>
                    </div>
                </div>

                <div class="order-card delivery-mode">
                    <div class="order-header">
                        <span class="order-id">#C040</span>
                        <span class="delivery-status">Parti à 12:20</span>
                    </div>
                    <div class="order-client">
                        <i class="fas fa-map-marker-alt"></i> Julie Martin<br>
                        <small>45 Bd du Port</small>
                    </div>
                    <div class="order-actions">
                        <button class="btn-kitchen disabled">
                            <i class="fas fa-clock"></i> En route...
                        </button>
                    </div>
                </div>

            </div>

        </div>

    </main>

    <footer>
        <p>Bien Harr © 2026 - Interface Restaurateur</p>
    </footer>

</body>
</html>