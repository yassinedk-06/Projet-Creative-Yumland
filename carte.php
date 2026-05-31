<?php
session_start(); 

// 1. INITIALISATION DU PANIER EN SESSION
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// ====================================================================
// INTERCEPTEUR AJAX POUR LES FILTRES (CONNEXION ASYNCHRONE)
// ====================================================================
if (isset($_GET['ajax']) && $_GET['ajax'] === 'filter') {
    header('Content-Type: application/json'); 
    
    $json_data = file_get_contents('json/plats.json');
    $menu = json_decode($json_data, true);

    $selectedTags = !empty($_GET['tags']) ? explode(',', strtolower($_GET['tags'])) : [];
    $searchQuery = strtolower(trim($_GET['search'] ?? ''));

    $filteredMenu = [];

    foreach ($menu as $catName => $plats) {
        $filteredPlats = [];
        foreach ($plats as $plat) {
            $platName = strtolower($plat[1]);
            $platDesc = strtolower($plat[4]);
            $platTags = isset($plat[5]) ? array_map('strtolower', $plat[5]) : [];

            if ($searchQuery !== '' && strpos($platName, $searchQuery) === false && strpos($platDesc, $searchQuery) === false) continue; 

            $matchTags = true;
            foreach ($selectedTags as $tag) {
                if (!in_array($tag, $platTags)) { $matchTags = false; break; }
            }

            if ($matchTags) $filteredPlats[] = $plat;
        }
        if (!empty($filteredPlats)) $filteredMenu[$catName] = $filteredPlats;
    }

    echo json_encode($filteredMenu);
    exit();
}

// ====================================================================
// TRAITEMENT : SUPPRESSION D'UN ARTICLE DU PANIER
// ====================================================================
if (isset($_GET['action']) && $_GET['action'] === 'supprimer' && isset($_GET['index'])) {
    $index = (int)$_GET['index'];
    if (isset($_SESSION['panier'][$index])) {
        unset($_SESSION['panier'][$index]);
        $_SESSION['panier'] = array_values($_SESSION['panier']); 
    }
    header('Location: carte.php');
    exit();
}

// ====================================================================
// TRAITEMENT : AJOUT D'UN ARTICLE AU PANIER (MISE À JOUR AJAX)
// ====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ajouter') {
    $_SESSION['panier'][] = [
        'id_plat' => $_POST['id_plat'],
        'nom' => $_POST['nom_plat'],
        'prix' => (float)$_POST['prix_plat']
    ];

    // Si on détecte que c'est une requête AJAX, on renvoie du JSON au lieu de recharger
    if (isset($_POST['ajax']) && $_POST['ajax'] == '1') {
        header('Content-Type: application/json');
        
        $total = 0;
        foreach ($_SESSION['panier'] as $item) {
            $total += $item['prix'];
        }
        
        echo json_encode([
            'success' => true,
            'cart_data' => $_SESSION['panier'],
            'total' => $total
        ]);
        exit();
    }

    // Sinon (si le JS est désactivé), on recharge la page normalement
    header('Location: carte.php');
    exit();
}

// LECTURE DU MENU DEPUIS LE JSON (Chargement initial)
$json_data = file_get_contents('json/plats.json');
$menu = json_decode($json_data, true);

// PRÉPARATION DE L'AFFICHAGE DU PANIER
$cart_data = $_SESSION['panier'];
$total_panier = 0;
foreach ($cart_data as $item) {
    $total_panier += $item['prix'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>La Carte - Bien Harr</title>
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
        <label for="menu-toggle" class="menu-icon"><span></span><span></span><span></span></label>
        <label for="menu-toggle" class="menu-overlay"></label>
        
        <div class="logo">BIEN <span>HARR</span></div>
        
        <ul class="menu-links">
                <li><div class="menu-header">BIEN HARR</div></li>
                <li><a href="index.php">Accueil</a></li>
                <li class="has-submenu">
                    <a href="carte.php" class="active">La Carte <span class="arrow">➤</span></a>
                    <ul class="submenu">
                        <li><a href="carte.php#entrees">Entrées</a></li>
                        <li><a href="carte.php#plats">Plats Traditionnels</a></li>
                        <li><a href="carte.php#boissons_chaudes">Boissons</a></li>
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
            <a href="admin.php" class="icon-btn" title="Espace Admin"><i class="fas fa-user-shield"></i> <span class="desktop-only">Admin</span></a>
            <a href="livraison.php" class="icon-btn" title="Espace Livreur"><i class="fas fa-motorcycle"></i> <span class="desktop-only">Livreur</span></a>
        <?php elseif (isset($_SESSION['connecte']) && $_SESSION['type'] == 'livreur'): ?>
            <a href="livraison.php" class="icon-btn" title="Espace Livreur"><i class="fas fa-motorcycle"></i> <span class="desktop-only">Livreur</span></a>
        <?php endif; ?>

        <label for="toggle-panier" class="icon-btn btn-ouvrir" style="cursor:pointer;">
            <i class="fas fa-shopping-cart"></i> <span class="desktop-only" id="cart-counter">Panier (<?= count($cart_data) ?>)</span>
        </label>
    </div>
</header>

<input type="checkbox" id="toggle-panier" class="case-cachee">

<div class="fenetre-laterale" id="fenetre-panier">
    <label for="toggle-panier" class="btn-fermer">&times;</label>
    <h2>Mon Panier</h2>  
    <?php if(!empty($cart_data)): ?>
        <ul class="cart-items-list">
            <?php foreach ($cart_data as $index => $item): ?>
                <li class="cart-item">
                    <span>
                        <a href="carte.php?action=supprimer&index=<?= $index ?>" class="btn-remove-item">&times;</a>
                        <?= htmlspecialchars($item['nom']) ?>
                    </span>
                    <strong><?= number_format($item['prix'], 2, ',', ' ') ?> €</strong>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="cart-total-container">
            <h3 class="cart-total-text">Total : <?= number_format($total_panier, 2, ',', ' ') ?> €</h3>
        </div>
        <div class="cart-action-container">
             <a href="validation.php" class="btn-checkout">Commander</a>
        </div>
    <?php else: ?>
        <p class="empty-cart-msg">Votre panier est vide.</p>
    <?php endif; ?>
</div>

<section class="carte-hero">
    <h1>Notre Carte Gourmande</h1>
    
    <div class="search-and-filter-container">
        <div class="search-bar">
            <?php $recherche_initiale = $_GET['search'] ?? ''; ?>
            <input type="text" id="searchInput" value="<?= htmlspecialchars($recherche_initiale) ?>" placeholder="Rechercher un plat (ex: Mloukhia)...">
        </div>
        <button id="btnOpenModal" class="btn-open-filtres"><i class="fas fa-sliders-h"></i> Filtres</button>
    </div>

    <div class="filters-menu">
        <a href="#entrees" class="filter-btn">Entrées</a>
        <a href="#plats" class="filter-btn">Plats Traditionnels</a>
        <a href="#boissons-chaudes" class="filter-btn">Boissons Chaudes</a>
        <a href="#boissons-froides" class="filter-btn">Boissons Froides</a>
        <a href="#desserts" class="filter-btn">Desserts</a>
    </div>
</section>

<div id="filterModal" class="modal-overlay">
    <div class="modal-content">
        <span id="btnCloseModal" class="btn-close-modal">&times;</span>
        <h2><i class="fas fa-filter"></i> Affiner la carte</h2>
        
        <div class="filter-section">
            <h3>Régimes & Goûts</h3>
            <div class="filter-tags">
                <label><input type="checkbox" class="tag-checkbox" value="vege"> <span class="tag-label">Végétarien</span></label>
                <label><input type="checkbox" class="tag-checkbox" value="proteine"> <span class="tag-label">Viande & Poisson</span></label>
                <label><input type="checkbox" class="tag-checkbox" value="sans gluten"> <span class="tag-label">Sans Gluten</span></label>
                <label><input type="checkbox" class="tag-checkbox" value="épicé"> <span class="tag-label">Épicé 🌶️</span></label>
                <label><input type="checkbox" class="tag-checkbox" value="sucré"> <span class="tag-label">Sucré</span></label>
                <label><input type="checkbox" class="tag-checkbox" value="salé"> <span class="tag-label">Salé</span></label>
            </div>
        </div>

        <div class="filter-section">
            <h3>Trier par prix</h3>
            <select id="sortSelect" class="sort-select">
                <option value="none">Ordre de la carte (Par défaut)</option>
                <option value="asc">Prix Croissant (- cher au + cher)</option>
                <option value="desc">Prix Décroissant (+ cher au - cher)</option>
            </select>
        </div>

        <button id="btnApplyFilters" class="btn-validate-filters">Appliquer</button>
    </div>
</div>

<div id="menu-container">
    <?php
    $catTitles = [
        'entrées' => 'Nos Entrées',
        'plats' => 'Plats Traditionnels',
        'boissons_chaudes' => 'Boissons Chaudes',
        'boissons_froides' => 'Boissons Froides',
        'desserts' => 'Douceurs Sucrées'
    ];
    
    foreach ($menu as $catKey => $plats) {
        $idSection = str_replace('_', '-', $catKey); 
        echo "<section id=\"$idSection\" class=\"menu-section\">";
        $titre = $catTitles[$catKey] ?? $catKey;
        echo "<h2 class=\"section-title\">$titre</h2>";
        echo "<div class=\"cards-grid\">";
        
        foreach ($plats as $plat) {
            $tagsHtml = "";
            if(isset($plat[5])) {
                foreach($plat[5] as $tag) {
                    $tagsHtml .= "<span class=\"plat-badge\">" . htmlspecialchars($tag) . "</span>";
                }
            }
            $prix = number_format($plat[2], 2, ',', ' ');
            
            echo "
            <div class=\"card\">
                <img src=\"" . htmlspecialchars($plat[3]) . "\" alt=\"" . htmlspecialchars($plat[1]) . "\">
                <div class=\"card-info\">
                    <h3>" . htmlspecialchars($plat[1]) . "</h3>
                    <p>" . htmlspecialchars($plat[4]) . "</p>
                    <div class=\"plat-tags\">$tagsHtml</div>
                    <span class=\"price\">$prix €</span>
                    <form action=\"carte.php\" method=\"POST\" class=\"form-add-cart\">
                        <input type=\"hidden\" name=\"action\" value=\"ajouter\">
                        <input type=\"hidden\" name=\"id_plat\" value=\"" . htmlspecialchars($plat[0]) . "\">
                        <input type=\"hidden\" name=\"nom_plat\" value=\"" . htmlspecialchars($plat[1]) . "\">
                        <input type=\"hidden\" name=\"prix_plat\" value=\"" . $plat[2] . "\">
                        <button type=\"submit\" class=\"btn-order\">Ajouter au panier</button>
                    </form>
                </div>
            </div>";
        }
        echo "</div></section>";
    }
    ?>
</div>

<footer>
    <p>Bien Harr © 2026 - Projet Yumland</p>
</footer>

<script>
    // --- GESTION DU PANIER LATÉRAL ---
    const menuGauche = document.getElementById('menu-toggle');
    const menuPanier = document.getElementById('toggle-panier');
    menuGauche.addEventListener('change', function() { if(this.checked) menuPanier.checked = false; });
    if(menuPanier) { menuPanier.addEventListener('change', function() { if(this.checked) menuGauche.checked = false; }); }

    // =====================================================================================
    // NOUVEAU : AJOUT AU PANIER SANS RECHARGER (AJAX)
    // =====================================================================================
    // On écoute tout le document pour intercepter les soumissions de formulaires
    document.addEventListener('submit', function(e) {
        if (e.target && e.target.classList.contains('form-add-cart')) {
            e.preventDefault(); // Annule le rechargement de la page

            const form = e.target;
            const formData = new FormData(form);
            formData.append('ajax', '1'); // Dit à PHP qu'on utilise AJAX

            // Effet visuel sur le bouton
            const btn = form.querySelector('.btn-order');
            const originalText = btn.textContent;
            btn.textContent = 'Ajouté ! ✔';
            btn.style.backgroundColor = '#27ae60'; // Vert

            fetch('carte.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    // Remettre le bouton à la normale après 1 seconde
                    setTimeout(() => {
                        btn.textContent = originalText;
                        btn.style.backgroundColor = '';
                    }, 1000);

                    // Mettre à jour le compteur du panier en haut
                    const counter = document.getElementById('cart-counter');
                    if(counter) {
                        counter.textContent = 'Panier (' + data.cart_data.length + ')';
                    }

                    // Rafraîchir le contenu du menu latéral
                    updateCartSidebar(data.cart_data, data.total);
                }
            })
            .catch(error => console.error("Erreur d'ajout panier:", error));
        }
    });

    // Fonction pour reconstruire le menu latéral après un ajout
    function updateCartSidebar(cartData, total) {
        const fenetrePanier = document.getElementById('fenetre-panier');

        if (cartData.length === 0) {
            fenetrePanier.innerHTML = `
                <label for="toggle-panier" class="btn-fermer">&times;</label>
                <h2>Mon Panier</h2>
                <p class="empty-cart-msg">Votre panier est vide.</p>
            `;
            return;
        }

        let html = `
            <label for="toggle-panier" class="btn-fermer">&times;</label>
            <h2>Mon Panier</h2>
            <ul class="cart-items-list">
        `;

        cartData.forEach((item, index) => {
            const prixFormate = parseFloat(item.prix).toFixed(2).replace('.', ',') + ' €';
            html += `
                <li class="cart-item">
                    <span>
                        <a href="carte.php?action=supprimer&index=${index}" class="btn-remove-item">&times;</a>
                        ${item.nom}
                    </span>
                    <strong>${prixFormate}</strong>
                </li>
            `;
        });

        const totalFormate = parseFloat(total).toFixed(2).replace('.', ',') + ' €';
        html += `
            </ul>
            <div class="cart-total-container">
                <h3 class="cart-total-text">Total : ${totalFormate}</h3>
            </div>
            <div class="cart-action-container">
                 <a href="validation.php" class="btn-checkout">Commander</a>
            </div>
        `;

        fenetrePanier.innerHTML = html;
    }


    // =====================================================================================
    // GESTION DE LA FENÊTRE MODALE & FILTRES/TRIS
    // =====================================================================================
    const modal = document.getElementById('filterModal');
    const btnOpen = document.getElementById('btnOpenModal');
    const btnClose = document.getElementById('btnCloseModal');
    const btnApply = document.getElementById('btnApplyFilters');

    btnOpen.addEventListener('click', () => { modal.style.display = 'flex'; });
    btnClose.addEventListener('click', () => { modal.style.display = 'none'; });
    window.addEventListener('click', (e) => { if (e.target === modal) modal.style.display = 'none'; });

    let currentData = {}; 
    const catTitles = {
        'entrées': 'Nos Entrées',
        'plats': 'Plats Traditionnels',
        'boissons_chaudes': 'Boissons Chaudes',
        'boissons_froides': 'Boissons Froides',
        'desserts': 'Douceurs Sucrées'
    };

    function fetchFilteredData() {
        const searchVal = document.getElementById('searchInput').value;
        const checkedTags = Array.from(document.querySelectorAll('.tag-checkbox:checked')).map(cb => cb.value);
        const url = 'carte.php?ajax=filter&search=' + encodeURIComponent(searchVal) + '&tags=' + encodeURIComponent(checkedTags.join(','));
        
        fetch(url)
            .then(response => response.json())
            .then(data => { currentData = data; applySortingAndRender(); })
            .catch(error => console.error('Erreur Fetch:', error));
    }

    function applySortingAndRender() {
        const sortOrder = document.getElementById('sortSelect').value;
        let dataToSort = JSON.parse(JSON.stringify(currentData));

        if (sortOrder !== 'none') {
            for (let catKey in dataToSort) {
                dataToSort[catKey].sort((a, b) => {
                    const prixA = parseFloat(a[2]);
                    const prixB = parseFloat(b[2]);
                    return sortOrder === 'asc' ? prixA - prixB : prixB - prixA;
                });
            }
        }
        renderMenu(dataToSort);
    }

    function renderMenu(data) {
        const container = document.getElementById('menu-container');
        container.innerHTML = ''; 

        if(Object.keys(data).length === 0) {
            container.innerHTML = '<h3 style="text-align:center; margin: 50px 0; color: var(--accent-red);">Aucun plat ne correspond à vos critères 😔</h3>';
            return;
        }

        for (const [catKey, plats] of Object.entries(data)) {
            const section = document.createElement('section');
            section.id = catKey.replace('_', '-'); 
            section.className = 'menu-section';

            const title = document.createElement('h2');
            title.className = 'section-title';
            title.textContent = catTitles[catKey] || catKey;
            section.appendChild(title);

            const grid = document.createElement('div');
            grid.className = 'cards-grid';

            plats.forEach(plat => {
                const card = document.createElement('div');
                card.className = 'card';
                const prixStr = parseFloat(plat[2]).toFixed(2).replace('.', ',') + ' €';
                
                let tagsHtml = '';
                if(plat[5] && plat[5].length > 0) {
                    tagsHtml = plat[5].map(tag => `<span class="plat-badge">${tag}</span>`).join('');
                }

                card.innerHTML = `
                    <img src="${plat[3]}" alt="${plat[1]}">
                    <div class="card-info">
                        <h3>${plat[1]}</h3>
                        <p>${plat[4]}</p>
                        <div class="plat-tags">${tagsHtml}</div>
                        <span class="price">${prixStr}</span>
                        <form action="carte.php" method="POST" class="form-add-cart">
                            <input type="hidden" name="action" value="ajouter">
                            <input type="hidden" name="id_plat" value="${plat[0]}">
                            <input type="hidden" name="nom_plat" value="${plat[1]}">
                            <input type="hidden" name="prix_plat" value="${plat[2]}">
                            <button type="submit" class="btn-order">Ajouter au panier</button>
                        </form>
                    </div>
                `;
                grid.appendChild(card);
            });
            section.appendChild(grid);
            container.appendChild(section);
        }
    }

    document.getElementById('searchInput').addEventListener('input', fetchFilteredData);
    btnApply.addEventListener('click', () => { fetchFilteredData(); modal.style.display = 'none'; });

    // =====================================================================================
    // NOUVEAU : LANCEMENT DE LA RECHERCHE AU CHARGEMENT (Depuis l'accueil)
    // =====================================================================================
    window.addEventListener('DOMContentLoaded', () => {
        // Si la barre de recherche n'est pas vide au chargement de la page
        if (document.getElementById('searchInput').value.trim() !== '') {
            // On simule un clic sur le bouton "Appliquer" ou on lance la fonction
            fetchFilteredData();
        }
    });

</script>

</body>
</html>