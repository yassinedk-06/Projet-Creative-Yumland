<?php 
session_start(); 

// 1. SÉCURITÉ
if (!isset($_SESSION['connecte'])) {
    header('Location: connexion.php');
    exit();
}

// 2. RÉCUPÉRATION DES DONNÉES DE L'UTILISATEUR
$adresse = "Non renseignée";
$infosupp = "Aucun complément";
$points = 0;
$mes_commandes_ids = []; // Pour stocker les identifiants (ex: "cmd001", "cmd003")

if (file_exists('json/users.json')) {
    $json_data = file_get_contents('json/users.json');
    $utilisateurs = json_decode($json_data, true);
    
    if ($utilisateurs) {
        foreach ($utilisateurs as $user) {
            if (isset($user['id']) && $user['id'] === $_SESSION['id']) {
                $adresse = $user['address'] ?? "Non renseignée";
                $infosupp = empty($user['infosupp']) ? "Aucun complément" : $user['infosupp'];
                $points = $user['points'] ?? 0;
                $mes_commandes_ids = $user['commandes'] ?? []; // On récupère sa liste de commandes
                break;
            }
        }
    }
}

// 3. RÉCUPÉRATION DU DÉTAIL DES COMMANDES
$historique_commandes = [];
if (!empty($mes_commandes_ids) && file_exists('json/commandes.json')) {
    $cmd_data = file_get_contents('json/commandes.json');
    $toutes_les_commandes = json_decode($cmd_data, true);
    
    if ($toutes_les_commandes) {
        foreach ($toutes_les_commandes as $cmd) {
            // Si l'ID de la commande est dans la liste de l'utilisateur, on la garde
            if (in_array($cmd['id'], $mes_commandes_ids)) {
                $historique_commandes[] = $cmd;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Profil - Bien Harr</title>
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
<body class="user-connected"> 
    <header>
        <nav>
            <input type="checkbox" id="menu-toggle">
            <label for="menu-toggle" class="menu-icon">
                <span></span><span></span><span></span>
            </label>
            <label for="menu-toggle" class="menu-overlay"></label>
            <div class="logo">BIEN <span>HARR</span></div>

            <div class="header-actions">
                <?php if (isset($_SESSION['type']) && $_SESSION['type'] == 'admin'): ?>
                    <a href="admin.php" class="icon-btn" title="Espace Admin"><i class="fas fa-user-shield"></i> <span class="desktop-only">Admin</span></a>
                    
                <?php endif; ?>

                <?php if (isset($_SESSION['type']) && $_SESSION['type'] == 'livreur'): ?>
                    <a href="livraison.php" class="icon-btn" title="Espace Livreur"><i class="fas fa-motorcycle"></i> <span class="desktop-only">Livreur</span></a>
                <?php endif; ?>

                <button id="btn-theme" class="icon-btn" title="Changer le thème">
                    <i class="fas fa-moon"></i>
                </button>
            </div>

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
                <li><a href="deconnexion.php" style="color: var(--accent-red);">Déconnexion</a></li>
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
                        <span><?php echo htmlspecialchars($_SESSION['nom'] ?? '') . ' ' . htmlspecialchars($_SESSION['prenom'] ?? ''); ?></span>
                        
                    </div>
                </div>
                <div class="info-item">
                    <label>Téléphone</label>
                    <div class="info-value">
                        <span><?php echo htmlspecialchars($_SESSION['num'] ?? ''); ?></span>
                        
                    </div>
                </div>
                <div class="info-item">
                    <label>Adresse</label>
                    <div class="info-value">
                        <span><?php echo htmlspecialchars($adresse); ?></span>
                        <i class="fas fa-pencil-alt edit-icon"></i>
                    </div>
                </div>
                <div class="info-item">
                    <label>Complément d'adresse</label>
                    <div class="info-value">
                        <span><?php echo htmlspecialchars($infosupp); ?></span>
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
                    <p class="points"><span><?php echo $points; ?></span> Points</p>
                    <p class="reward">Chaque commande compte !</p>
                </div>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: 50%;"></div>
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
                    <?php if (empty($historique_commandes)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">Aucune commande pour le moment.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($historique_commandes as $cmd): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($cmd['date'])); ?></td>
                                
                                <td><?php echo htmlspecialchars(implode(" + ", $cmd['selection'])); ?></td>
                                
                                <td><?php echo number_format($cmd['prix'], 2, ',', ' '); ?> €</td>
                                
                                <td><span class="status delivered"><?php echo htmlspecialchars($cmd['etat']); ?></span></td>
                                
                                <td>
                                    <?php 
                                    // La note[0] représente la note de la cuisine. Si elle est > 0, c'est noté.
                                    if (isset($cmd['note']) && $cmd['note'][0] > 0): 
                                        // Affiche autant d'étoiles que la note (ex: 4 = ⭐⭐⭐⭐)
                                        $etoiles = str_repeat('⭐', $cmd['note'][0]);
                                    ?>
                                        <span title="Cuisine: <?php echo $cmd['note'][0]; ?>/5 - Commentaire: <?php echo htmlspecialchars($cmd['note'][2]); ?>">
                                            <?php echo $etoiles; ?>
                                        </span>
                                    <?php else: ?>
                                        <a href="notation.php?id=<?php echo htmlspecialchars($cmd['id']); ?>" class="rate-link">Noter</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

    </main>

    <footer>
        <p>Bien Harr © 2026 - Projet Yumland</p>
    </footer>

<script>
    document.getElementById('btn-theme').addEventListener('click', function() {
        // 1. On récupère la balise <link> du CSS
        const themeLink = document.getElementById('theme-style');
        const themeIcon = this.querySelector('i');
        
        // 2. On regarde quel est le thème actuel
        let currentTheme = themeLink.getAttribute('href');
        let newTheme = 'style.css'; // Par défaut, on remet le clair
        
        // 3. Logique de bascule (Toggle)
        if (currentTheme === 'style.css') {
            newTheme = 'style-dark.css';
            themeIcon.classList.replace('fa-moon', 'fa-sun'); // Change l'icône
        } else {
            newTheme = 'style.css';
            themeIcon.classList.replace('fa-sun', 'fa-moon'); // Change l'icône
        }
        
        // 4. On change le CSS EN DIRECT (sans recharger la page !)
        themeLink.setAttribute('href', newTheme);
        
        // 5. On sauvegarde le choix dans un COOKIE (valable 30 jours)
        let dateExpiration = new Date();
        dateExpiration.setTime(dateExpiration.getTime() + (30 * 24 * 60 * 60 * 1000));
        document.cookie = "theme=" + newTheme + "; expires=" + dateExpiration.toUTCString() + "; path=/";
    });

    // Petit bonus : Mettre la bonne icône au chargement de la page selon le cookie
    window.addEventListener('DOMContentLoaded', (event) => {
        const themeLink = document.getElementById('theme-style');
        const themeIcon = document.querySelector('#btn-theme i');
        if (themeLink && themeIcon && themeLink.getAttribute('href') === 'style-dark.css') {
            themeIcon.classList.replace('fa-moon', 'fa-sun');
        }
    });
    </script>
</body>
</html>