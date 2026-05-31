<?php 
session_start(); 
require_once 'fonctions.php';
// 1. SÉCURITÉ
if (!isset($_SESSION['connecte'])) {
    header('Location: connexion.php');
    exit();
}

// ====================================================================
// NOUVEAU : INTERCEPTEUR AJAX (Pour la sauvegarde asynchrone)
// ====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profil') {
    header('Content-Type: application/json'); // On précise qu'on répond en format JSON

    $new_adresse = $_POST['adresse'] ?? '';
    $new_infosupp = $_POST['infosupp'] ?? '';

    $chemin_users = 'json/users.json';
    $success = false;

    if (file_exists($chemin_users)) {
        $utilisateurs = json_decode(file_get_contents($chemin_users), true);
        if ($utilisateurs) {
            foreach ($utilisateurs as &$user) {
                // On trouve l'utilisateur connecté et on met à jour ses données
                if (isset($user['id']) && $user['id'] === $_SESSION['id']) {
                    $user['address'] = htmlspecialchars($new_adresse);
                    $user['infosupp'] = htmlspecialchars($new_infosupp);
                    $success = true;
                    break;
                }
            }
            // Si on a bien modifié, on sauvegarde le fichier
            if ($success) {
                file_put_contents($chemin_users, json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                ajouterLog(
                    $_SESSION['id'], 
                    $_SESSION['type'], 
                    "MODIFICATION_PROFIL", 
                    "Mise à jour de l'adresse de livraison."
                );
            }
        }
    }

    // On renvoie un signal de succès au JavaScript et on ARRÊTE le script PHP
    echo json_encode(['success' => $success]);
    exit(); 
}
// ====================================================================


// 2. RÉCUPÉRATION DES DONNÉES DE L'UTILISATEUR
$adresse = "Non renseignée";
$infosupp = "Aucun complément";
$points = 0;
$mes_commandes_ids = []; 

if (file_exists('json/users.json')) {
    $json_data = file_get_contents('json/users.json');
    $utilisateurs = json_decode($json_data, true);
    
    if ($utilisateurs) {
        foreach ($utilisateurs as $user) {
            if (isset($user['id']) && $user['id'] === $_SESSION['id']) {
                $adresse = $user['address'] ?? "Non renseignée";
                $infosupp = empty($user['infosupp']) ? "Aucun complément" : $user['infosupp'];
                $points = $user['points'] ?? 0;
                $mes_commandes_ids = $user['commandes'] ?? []; 
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
    $themes_autorises = ['style.css', 'style-dark.css'];
    $theme_actuel = 'style.css'; 
    if (isset($_COOKIE['theme']) && in_array($_COOKIE['theme'], $themes_autorises)) {
        $theme_actuel = $_COOKIE['theme'];
    }
    ?>
    <link id="theme-style" rel="stylesheet" href="<?= htmlspecialchars($theme_actuel) ?>">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Petits styles pour les champs éditables */
        .edit-input {
            width: 75%;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid var(--primary-blue);
            background: transparent;
            color: inherit;
            font-family: inherit;
            font-size: 0.95rem;
            outline: none;
        }
        .edit-input:focus { border-color: var(--accent-red); }
        .save-icon { transition: transform 0.2s; }
        .save-icon:hover { transform: scale(1.2); }
    </style>
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
                    <div class="info-value" id="box-adresse">
                        <span class="text-display"><?php echo htmlspecialchars($adresse); ?></span>
                        <input type="text" class="edit-input" id="input-adresse" value="<?php echo htmlspecialchars($adresse, ENT_QUOTES); ?>" style="display: none;">
                        
                        <div class="actions">
                            <i class="fas fa-pencil-alt edit-icon btn-edit" title="Modifier"></i>
                            <i class="fas fa-check save-icon btn-save" title="Sauvegarder" style="display: none; color: #2ecc71; cursor: pointer; font-size: 1.2rem;"></i>
                        </div>
                    </div>
                </div>
                
                <div class="info-item">
                    <label>Complément d'adresse</label>
                    <div class="info-value" id="box-infosupp">
                        <span class="text-display"><?php echo htmlspecialchars($infosupp); ?></span>
                        <input type="text" class="edit-input" id="input-infosupp" value="<?php echo htmlspecialchars($infosupp, ENT_QUOTES); ?>" style="display: none;">
                        
                        <div class="actions">
                            <i class="fas fa-pencil-alt edit-icon btn-edit" title="Modifier"></i>
                            <i class="fas fa-check save-icon btn-save" title="Sauvegarder" style="display: none; color: #2ecc71; cursor: pointer; font-size: 1.2rem;"></i>
                        </div>
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
                                    if (isset($cmd['note']) && $cmd['note'][0] > 0): 
                                        $etoiles = str_repeat('⭐', $cmd['note'][0]);
                                    ?>
                                        <span title="Cuisine: <?php echo $cmd['note'][0]; ?>/5 - Commentaire: <?php echo htmlspecialchars($cmd['note'][2]); ?>">
                                            <?php echo $etoiles; ?>
                                        </span>
                                    <?php else: 
                                        
                                        $etat_commande = $cmd['etat'];
                                        
                                        // On vérifie si c'est livré ou annulé (avec ou sans accent)
                                        if (in_array($etat_commande, ['livr\u00e9e', 'livree','livrée', 'annul\u00e9', 'annulé','annulée'])):
                                    ?>
                                        <a href="notation.php?id=<?php echo htmlspecialchars($cmd['id']); ?>" class="rate-link">Noter</a>
                                    <?php else: ?>
                                        <span style="color: #666; cursor: not-allowed; font-size: 0.9rem; font-style: italic;" title="Attendez la fin de la commande pour noter">En cours...</span>
                                    <?php endif; ?>
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
    // --- GESTION DU THÈME ---
    document.getElementById('btn-theme').addEventListener('click', function() {
        const themeLink = document.getElementById('theme-style');
        const themeIcon = this.querySelector('i');
        let currentTheme = themeLink.getAttribute('href');
        let newTheme = 'style.css'; 
        
        if (currentTheme === 'style.css') {
            newTheme = 'style-dark.css';
            themeIcon.classList.replace('fa-moon', 'fa-sun'); 
        } else {
            newTheme = 'style.css';
            themeIcon.classList.replace('fa-sun', 'fa-moon');
        }
        
        themeLink.setAttribute('href', newTheme);
        
        let dateExpiration = new Date();
        dateExpiration.setTime(dateExpiration.getTime() + (30 * 24 * 60 * 60 * 1000));
        document.cookie = "theme=" + newTheme + "; expires=" + dateExpiration.toUTCString() + "; path=/";
    });

    window.addEventListener('DOMContentLoaded', (event) => {
        const themeLink = document.getElementById('theme-style');
        const themeIcon = document.querySelector('#btn-theme i');
        if (themeLink && themeIcon && themeLink.getAttribute('href') === 'style-dark.css') {
            themeIcon.classList.replace('fa-moon', 'fa-sun');
        }
    });

    // ====================================================================
    // NOUVEAU : GESTION DE LA MODIFICATION ASYNCHRONE (AJAX / FETCH)
    // ====================================================================
    function setupInlineEdit(fieldId) {
        const box = document.getElementById('box-' + fieldId);
        if (!box) return;

        const textDisplay = box.querySelector('.text-display');
        const editInput = box.querySelector('.edit-input');
        const btnEdit = box.querySelector('.btn-edit');
        const btnSave = box.querySelector('.btn-save');

        // 1. Quand on clique sur le crayon
        btnEdit.addEventListener('click', () => {
            textDisplay.style.display = 'none';    // Cache le texte
            editInput.style.display = 'block';     // Montre le champ input
            btnEdit.style.display = 'none';        // Cache le crayon
            btnSave.style.display = 'block';       // Montre la coche (valider)
            editInput.focus();
        });

        // 2. Quand on clique sur la coche (Valider)
        btnSave.addEventListener('click', () => {
            // On récupère les deux valeurs pour être sûr de tout sauvegarder
            const adresseValue = document.getElementById('input-adresse').value.trim();
            const infosuppValue = document.getElementById('input-infosupp').value.trim();

            // On prépare les données à envoyer (comme un formulaire classique)
            const formData = new FormData();
            formData.append('action', 'update_profil');
            formData.append('adresse', adresseValue);
            formData.append('infosupp', infosuppValue);

            // Appel Asynchrone vers le serveur (même page)
            fetch('profil.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json()) // On s'attend à recevoir {success: true}
            .then(data => {
                if(data.success) {
                    // Si le serveur dit OK, on met à jour l'interface HTML
                    textDisplay.textContent = editInput.value;
                    textDisplay.style.display = 'block';
                    editInput.style.display = 'none';
                    btnSave.style.display = 'none';
                    btnEdit.style.display = 'block';

                    // Petit effet visuel vert pour confirmer à l'utilisateur
                    textDisplay.style.color = '#2ecc71';
                    setTimeout(() => { textDisplay.style.color = ''; }, 1500);
                } else {
                    alert("Erreur lors de la sauvegarde.");
                }
            })
            .catch(error => {
                console.error("Erreur Fetch:", error);
                alert("Erreur de connexion avec le serveur.");
            });
        });
    }

    // On active la fonction pour nos deux champs
    setupInlineEdit('adresse');
    setupInlineEdit('infosupp');
</script>
</body>
</html>