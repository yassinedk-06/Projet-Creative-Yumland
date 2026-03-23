<?php
session_start(); 

// ====================================================================
// TRAITEMENT : SUPPRESSION D'UN ARTICLE DANS cart.json
// ====================================================================
if (isset($_GET['action']) && $_GET['action'] === 'supprimer' && isset($_GET['index'])) {
    $index_a_supprimer = (int)$_GET['index'];
    $chemin_cart = 'json/cart.json';

    if (file_exists($chemin_cart)) {
        $contenu = file_get_contents($chemin_cart);
        $cart_data = json_decode($contenu, true) ?? [];

        // Si l'article existe, on le supprime
        if (isset($cart_data[$index_a_supprimer])) {
            unset($cart_data[$index_a_supprimer]);
            // On réorganise les clés du tableau (0, 1, 2...)
            $cart_data = array_values($cart_data);
            
            // On sauvegarde le fichier
            file_put_contents($chemin_cart, json_encode($cart_data, JSON_PRETTY_PRINT));
        }
    }

    // On recharge la page pour mettre à jour l'affichage et retirer le paramètre GET
    header('Location: carte.php');
    exit();
}

// ====================================================================
// TRAITEMENT : AJOUT DANS cart.json
// ====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ajouter') {
    
    // 1. On prépare les données du plat cliqué
    $nouvel_article = [
        'id_plat' => $_POST['id_plat'],
        'nom' => $_POST['nom_plat'],
        'prix' => (float)$_POST['prix_plat'],
        
    ];

    $chemin_cart = 'json/cart.json';
    $cart_data = [];

    // 2. Si le fichier existe, on récupère ce qu'il y a déjà dedans
    if (file_exists($chemin_cart)) {
        $contenu = file_get_contents($chemin_cart);
        if (!empty($contenu)) {
            $cart_data = json_decode($contenu, true) ?? [];
        }
    }

    // 3. On ajoute le nouveau plat à la liste
    $cart_data[] = $nouvel_article;

    // 4. On sauvegarde le fichier
    file_put_contents($chemin_cart, json_encode($cart_data, JSON_PRETTY_PRINT));

    // 5. On recharge la page pour éviter que l'action se répète si on fait F5
    header('Location: carte.php');
    exit();
}
// ====================================================================

$json_data = file_get_contents('json/plats.json');
$menu = json_decode($json_data, true);

// NOUVEAU : On lit le fichier cart.json à chaque chargement de la page pour l'affichage
$chemin_cart = 'json/cart.json';
$cart_data = [];
$total_panier = 0; // On en profite pour préparer le calcul du total
if (file_exists($chemin_cart)) {
    $contenu_cart = file_get_contents($chemin_cart);
    if (!empty($contenu_cart)) {
        $cart_data = json_decode($contenu_cart, true) ?? [];
        // Calcul du total
        foreach ($cart_data as $item) {
            $total_panier += $item['prix'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>La Carte - Bien Harr</title>
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
                
                <?php if (isset($_SESSION['connecte'])): ?>
                    <li><a href="profil.php">Mon Compte</a></li>
                    <li><a href="deconnexion.php" style="color: var(--accent-red);">Déconnexion</a></li>
                <?php else: ?>
                    <li><a href="connexion.php">Connexion</a></li>
                    <li><a href="inscription.php">Inscription</a></li>
                <?php endif; ?>
            </ul>
    </nav>

    
    <div class="header-actions">
                
        <?php if (isset($_SESSION['connecte']) && $_SESSION['type'] == 'admin'): ?>
            <a href="admin.php" class="icon-btn" title="Espace Admin"> 
                <i class="fas fa-user-shield"></i> <span class="desktop-only">Admin</span>
            </a>
            <a href="livraison.php" class="icon-btn" title="Espace Livreur">Livreur</a>
            
            <label for="toggle-panier" class="icon-btn btn-ouvrir">
                🛒 Panier
            </label>

        <?php elseif (isset($_SESSION['connecte']) && $_SESSION['type'] == 'livreur'): ?>
            <a href="livraison.php" class="icon-btn" title="Espace Livreur">
                <i class="fas fa-motorcycle"></i> <span class="desktop-only">Livreur</span>
            </a>

        <?php else: ?>
            <label for="toggle-panier" class="icon-btn btn-ouvrir">
                🛒 Panier
            </label>
            
        <?php endif; ?>

    </div>
</header>

    <input type="checkbox" id="toggle-panier" class="case-cachee">

    <div class="fenetre-laterale">
        <label for="toggle-panier" class="btn-fermer">&times;</label>
        
        <h2>Mon Panier</h2>  
        
        <?php if(!empty($cart_data)): ?>
            <ul style="list-style: none; padding: 0;">
                <?php foreach ($cart_data as $index => $item): ?>
                    <li style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; align-items: center;">
                        <span>
                            <a href="carte.php?action=supprimer&index=<?= $index ?>" style="color: var(--accent-red); font-weight: bold; text-decoration: none; margin-right: 10px; font-size: 1.2rem;" title="Retirer du panier">&times;</a>
                            <?= htmlspecialchars($item['nom']) ?>
                        </span>
                        <strong><?= number_format($item['prix'], 2, ',', ' ') ?> €</strong>
                    </li>
                <?php endforeach; ?>
            </ul>
            
            <div style="border-top: 2px solid var(--primary-blue); margin-top: 15px; padding-top: 15px; text-align: right;">
                <h3 style="margin: 0; color: var(--primary-blue);">Total : <?= number_format($total_panier, 2, ',', ' ') ?> €</h3>
            </div>
            
            <div style="text-align: center; margin-top: 20px;">
                 <a href="#" style="display: inline-block; background-color: var(--primary-blue); color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; width: 100%; box-sizing: border-box;">Commander</a>
            </div>

        <?php else: ?>
            <p style="text-align: center; color: #888; margin-top: 30px;">Votre panier est vide.</p>
        <?php endif; ?>

    </div>

    <section class="carte-hero">
        <h1>Notre Carte Gourmande</h1>
        
        <div class="search-bar">
            <input type="text" placeholder="Rechercher un plat (ex: Mloukhia)...">
            <button>Chercher</button>
        </div>

        <div class="filters-menu">
            <a href="#entrees" class="filter-btn">Entrées</a>
            <a href="#plats" class="filter-btn">Plats Traditionnels</a>
            <a href="#boissons-chaudes" class="filter-btn">Boissons Chaudes</a>
            <a href="#boissons-froides" class="filter-btn">Boissons Froides</a>
            <a href="#desserts" class="filter-btn">Desserts</a>
        </div>
    </section>

    <section id="entrees" class="menu-section">
        <h2 class="section-title">Nos Entrées</h2>
        <div class="cards-grid">
            <?php if(isset($menu['entrées'])): ?>
                <?php foreach ($menu['entrées'] as $plat): ?>
                    <div class="card">
                        <img src="<?= htmlspecialchars($plat[3]) ?>" alt="<?= htmlspecialchars($plat[1]) ?>">
                        <div class="card-info">
                            <h3><?= htmlspecialchars($plat[1]) ?></h3>
                            <p><?= htmlspecialchars($plat[4]) ?></p>
                            <span class="price"><?= number_format($plat[2], 2, ',', ' ') ?> €</span>
                            
                            <form action="carte.php" method="POST" style="margin: 0;">
                                <input type="hidden" name="action" value="ajouter">
                                <input type="hidden" name="id_plat" value="<?= htmlspecialchars($plat[0]) ?>">
                                <input type="hidden" name="nom_plat" value="<?= htmlspecialchars($plat[1]) ?>">
                                <input type="hidden" name="prix_plat" value="<?= $plat[2] ?>">
                                <button type="submit" class="btn-order">Ajouter au panier</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <section id="plats" class="menu-section">
        <h2 class="section-title">Plats Traditionnels</h2>
        <div class="cards-grid">
            <?php foreach ($menu['plats'] as $plat): ?>
            <div class="card">
                <img src="<?= htmlspecialchars($plat[3]) ?>" alt="<?= htmlspecialchars($plat[1]) ?>">
                <div class="card-info">
                    <h3><?= htmlspecialchars($plat[1]) ?></h3>
                    <p><?= htmlspecialchars($plat[4]) ?></p>
                    <span class="price"><?= number_format($plat[2], 2, ',', ' ') ?> €</span>
                    
                    <form action="carte.php" method="POST" style="margin: 0;">
                        <input type="hidden" name="action" value="ajouter">
                        <input type="hidden" name="id_plat" value="<?= htmlspecialchars($plat[0]) ?>">
                        <input type="hidden" name="nom_plat" value="<?= htmlspecialchars($plat[1]) ?>">
                        <input type="hidden" name="prix_plat" value="<?= $plat[2] ?>">
                        <button type="submit" class="btn-order">Ajouter au panier</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section id="boissons-chaudes" class="menu-section">
        <h2 class="section-title">Boissons Chaudes</h2>
        <div class="cards-grid">
            <?php foreach ($menu['boissons_chaudes'] as $plat): ?>
            <div class="card">
                <img src="<?= htmlspecialchars($plat[3]) ?>" alt="<?= htmlspecialchars($plat[1]) ?>">
                <div class="card-info">
                    <h3><?= htmlspecialchars($plat[1]) ?></h3>
                    <p><?= htmlspecialchars($plat[4]) ?></p>
                    <span class="price"><?= number_format($plat[2], 2, ',', ' ') ?> €</span>
                    
                    <form action="carte.php" method="POST" style="margin: 0;">
                        <input type="hidden" name="action" value="ajouter">
                        <input type="hidden" name="id_plat" value="<?= htmlspecialchars($plat[0]) ?>">
                        <input type="hidden" name="nom_plat" value="<?= htmlspecialchars($plat[1]) ?>">
                        <input type="hidden" name="prix_plat" value="<?= $plat[2] ?>">
                        <button type="submit" class="btn-order">Ajouter au panier</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section id="boissons-froides" class="menu-section">
        <h2 class="section-title">Boissons Froides</h2>
        <div class="cards-grid">
            <?php foreach ($menu['boissons_froides'] as $plat): ?>
            <div class="card">
                <img src="<?= htmlspecialchars($plat[3]) ?>" alt="<?= htmlspecialchars($plat[1]) ?>">
                <div class="card-info">
                    <h3><?= htmlspecialchars($plat[1]) ?></h3>
                    <p><?= htmlspecialchars($plat[4]) ?></p>
                    <span class="price"><?= number_format($plat[2], 2, ',', ' ') ?> €</span>
                    
                    <form action="carte.php" method="POST" style="margin: 0;">
                        <input type="hidden" name="action" value="ajouter">
                        <input type="hidden" name="id_plat" value="<?= htmlspecialchars($plat[0]) ?>">
                        <input type="hidden" name="nom_plat" value="<?= htmlspecialchars($plat[1]) ?>">
                        <input type="hidden" name="prix_plat" value="<?= $plat[2] ?>">
                        <button type="submit" class="btn-order">Ajouter au panier</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section id="desserts" class="menu-section">
        <h2 class="section-title">Douceurs Sucrées</h2>
        <div class="cards-grid">
            <?php foreach ($menu['desserts'] as $plat): ?>
            <div class="card">
                <img src="<?= htmlspecialchars($plat[3]) ?>" alt="<?= htmlspecialchars($plat[1]) ?>">
                <div class="card-info">
                    <h3><?= htmlspecialchars($plat[1]) ?></h3>
                    <p><?= htmlspecialchars($plat[4]) ?></p>
                    <span class="price"><?= number_format($plat[2], 2, ',', ' ') ?> €</span>
                    
                    <form action="carte.php" method="POST" style="margin: 0;">
                        <input type="hidden" name="action" value="ajouter">
                        <input type="hidden" name="id_plat" value="<?= htmlspecialchars($plat[0]) ?>">
                        <input type="hidden" name="nom_plat" value="<?= htmlspecialchars($plat[1]) ?>">
                        <input type="hidden" name="prix_plat" value="<?= $plat[2] ?>">
                        <button type="submit" class="btn-order">Ajouter au panier</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <footer>
        <p>Bien Harr © 2026 - Projet Yumland</p>
    </footer>

    <script>
    // On récupère nos deux cases à cocher invisibles
    const menuGauche = document.getElementById('menu-toggle');
    const menuPanier = document.getElementById('toggle-panier');

    // Quand on clique sur le menu de gauche (le Burger)
    menuGauche.addEventListener('change', function() {
        if(this.checked) {
            menuPanier.checked = false; // On force le panier à se fermer
        }
    });

    // Quand on clique sur le bouton de droite (le Panier)
    if(menuPanier) {
        menuPanier.addEventListener('change', function() {
            if(this.checked) {
                menuGauche.checked = false; // On force le menu de gauche à se fermer
            }
        });
    }
</script>

</body>
</html>