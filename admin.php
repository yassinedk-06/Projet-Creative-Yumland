<?php
session_start();

// 1. SÉCURITÉ : On vérifie si l'utilisateur est connecté et si c'est bien un admin
if (!isset($_SESSION['connecte']) || $_SESSION['type'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$chemin_users = 'json/users.json';

// ====================================================================
// NOUVEAU : INTERCEPTEUR AJAX POUR BLOQUER / DÉBLOQUER UN CLIENT
// ====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_block') {
    header('Content-Type: application/json'); // Réponse en JSON
    
    $user_id_to_toggle = $_POST['user_id'];
    $success = false;
    $new_status = false; // false = non bloqué, true = bloqué

    if (file_exists($chemin_users)) {
        $utilisateurs = json_decode(file_get_contents($chemin_users), true);
        if ($utilisateurs) {
            foreach ($utilisateurs as &$user) {
                // On s'assure qu'on modifie un client et pas un autre admin
                if (isset($user['id']) && $user['id'] === $user_id_to_toggle && $user['type'] === 'client') {
                    
                    // Si la case "bloque" n'existe pas ou est sur false, on le bloque
                    if (!isset($user['bloque']) || $user['bloque'] === false) {
                        $user['bloque'] = true;
                        $new_status = true;
                    } else {
                        // Sinon on le débloque
                        $user['bloque'] = false;
                        $new_status = false;
                    }
                    $success = true;
                    break;
                }
            }
            if ($success) {
                file_put_contents($chemin_users, json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }
    }
    
    // On renvoie le résultat au JavaScript et on arrête l'exécution
    echo json_encode(['success' => $success, 'is_blocked' => $new_status]);
    exit();
}
// ====================================================================

// ====================================================================
// TRAITEMENT DE LA MISE À JOUR DU STATUT (Basic, VIP, etc.)
// ====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_statut') {
    $user_id_to_update = $_POST['user_id'];
    $new_statut = $_POST['new_statut'];
    
    $statuts_autorises = ['basic', 'silver', 'gold', 'VIP'];
    
    if (in_array($new_statut, $statuts_autorises) && file_exists($chemin_users)) {
        $users_data = file_get_contents($chemin_users);
        $utilisateurs = json_decode($users_data, true);
        
        if ($utilisateurs) {
            foreach ($utilisateurs as &$user) {
                if (isset($user['id']) && $user['id'] === $user_id_to_update) {
                    $user['statut'] = $new_statut; 
                    break;
                }
            }
            file_put_contents($chemin_users, json_encode($utilisateurs, JSON_PRETTY_PRINT));
            
            header('Location: admin.php');
            exit();
        }
    }
}
// ====================================================================

// 2. RÉCUPÉRATION DES UTILISATEURS POUR L'AFFICHAGE
$utilisateurs = [];
if (file_exists($chemin_users)) {
    $json_data = file_get_contents($chemin_users);
    $utilisateurs = json_decode($json_data, true) ?? [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - Bien Harr</title>
   <?php
    $themes_autorises = ['style.css', 'style-dark.css'];
    $theme_actuel = 'style.css'; 
    if (isset($_COOKIE['theme']) && in_array($_COOKIE['theme'], $themes_autorises)) {
        $theme_actuel = $_COOKIE['theme'];
    }
    ?>
    <link id="theme-style" rel="stylesheet" href="<?= htmlspecialchars($theme_actuel) ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;600;800&display=swap" rel="stylesheet">
</head>
<body class="admin-page admin-body">

    <header>
        <nav>
            <input type="checkbox" id="menu-toggle">
            <label for="menu-toggle" class="menu-icon">
                <span></span><span></span><span></span>
            </label>
            <label for="menu-toggle" class="menu-overlay"></label>
            <div class="logo">BIEN <span>HARR</span> <span style="font-size: 0.8rem; color: var(--primary-blue);">ADMIN</span></div>
            <ul class="menu-links">
                <li><div class="menu-header">ADMINISTRATION</div></li>
                <li><a href="index.php">Retour au Site</a></li>
                <li><a href="admin.php" class="active">Gestion Clients</a></li>
                <li><a href="ajout_plat.php">Ajouter un Plat</a></li> 
                <li><a href="commandes.php">Gestion Commandes</a></li>
                <li><a href="deconnexion.php" style="color: var(--accent-red);">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <main class="admin-container dashboard-container">
        
        <div class="admin-header">
            <h1>Gestion des Utilisateurs</h1>
            <div class="admin-search">
                <input type="text" placeholder="Rechercher (Nom, ID, Tel)...">
                <button><i class="fas fa-search"></i></button>
            </div>
        </div>

        <div class="admin-tabs">
            <button class="tab-btn active">Tous les utilisateurs (<?php echo count($utilisateurs); ?>)</button>
        </div>

        <section class="admin-table-wrapper dashboard-card">
            <table class="admin-table history-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Coordonnées</th>
                        <th>Historique</th>
                        <th>Niveau (Statut)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($utilisateurs)): ?>
                        <tr><td colspan="6" style="text-align: center;">Aucun utilisateur trouvé.</td></tr>
                    <?php else: ?>
                        <?php foreach ($utilisateurs as $user): ?>
                            <?php 
                                $id = isset($user['id']) ? htmlspecialchars($user['id']) : 'N/A';
                                $prenom = htmlspecialchars($user['prenom'] ?? '');
                                $nom = htmlspecialchars($user['nom'] ?? '');
                                $initiales = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
                                $num = htmlspecialchars($user['num'] ?? '');
                                $type = htmlspecialchars($user['type'] ?? 'client');
                                $nb_commandes = isset($user['commandes']) ? count($user['commandes']) : 0;
                                $statut = htmlspecialchars($user['statut'] ?? 'basic');
                                
                                // On vérifie si l'utilisateur est bloqué
                                $est_bloque = isset($user['bloque']) && $user['bloque'] === true;
                            ?>
                            <tr>
                                <td><strong>#<?php echo $id; ?></strong></td>
                                
                                <td>
                                    <div class="user-info">
                                        <div class="avatar" style="<?php echo $est_bloque ? 'background-color: #666;' : ''; ?>">
                                            <?php echo $initiales; ?>
                                        </div>
                                        <span style="<?php echo $est_bloque ? 'text-decoration: line-through; color: #888;' : ''; ?>">
                                            <?php echo $prenom . ' ' . $nom; ?>
                                        </span>
                                    </div>
                                </td>
                                
                                <td>
                                    <?php echo $num; ?><br>
                                    <small class="email-text" style="text-transform: capitalize;">Rôle : <?php echo $type; ?></small>
                                </td>
                                
                                <td>
                                    <?php if ($nb_commandes > 0): ?>
                                        <span class="badge-orders"><?php echo $nb_commandes; ?> commande(s)</span>
                                    <?php else: ?>
                                        <span class="badge-no-orders">Aucune commande</span>
                                    <?php endif; ?>
                                </td>
                                
                                <td>
                                    <form action="admin.php" method="POST" style="margin: 0;">
                                        <input type="hidden" name="action" value="update_statut">
                                        <input type="hidden" name="user_id" value="<?php echo $id; ?>">
                                        <select name="new_statut" class="select-statut <?php echo $statut; ?>" onchange="this.form.submit()">
                                            <option value="basic" <?php if($statut == 'basic') echo 'selected'; ?>>Basic</option>
                                            <option value="silver" <?php if($statut == 'silver') echo 'selected'; ?>>Silver</option>
                                            <option value="gold" <?php if($statut == 'gold') echo 'selected'; ?>>Gold</option>
                                            <option value="VIP" <?php if($statut == 'VIP') echo 'selected'; ?>>VIP</option>
                                        </select>
                                    </form>
                                </td>
                                
                                <td>
                                    <?php if ($type !== 'client'): ?>
                                        <button class="action-btn" title="Membre de l'équipe" disabled style="opacity: 0.3; cursor: not-allowed; background-color: #666; border: none; padding: 8px 12px; color: white; border-radius: 5px;">
                                            <i class="fas fa-shield-alt"></i>
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="action-btn btn-toggle-block" 
                                                data-id="<?php echo $id; ?>" 
                                                data-blocked="<?php echo $est_bloque ? 'true' : 'false'; ?>"
                                                title="<?php echo $est_bloque ? 'Débloquer ce client' : 'Bloquer ce client'; ?>"
                                                style="background-color: <?php echo $est_bloque ? '#27ae60' : 'var(--accent-red)'; ?>; border: none; padding: 8px 12px; color: white; border-radius: 5px; cursor: pointer; transition: 0.3s;">
                                            <i class="fas <?php echo $est_bloque ? 'fa-unlock' : 'fa-lock'; ?>"></i>
                                        </button>
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
        <p>Bien Harr © 2026 - Interface Administrateur</p>
    </footer>

    <script>
        // ====================================================================
        // SCRIPT AJAX POUR BLOQUER/DÉBLOQUER SANS RECHARGER LA PAGE
        // ====================================================================
        document.querySelectorAll('.btn-toggle-block').forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                const isBlocked = this.getAttribute('data-blocked') === 'true';
                
                // Message de confirmation dynamique
                const confirmMessage = isBlocked 
                    ? "Voulez-vous débloquer ce client ?" 
                    : "Voulez-vous bloquer ce client ? Sa session en cours sera fermée immédiatement.";
                
                if (!confirm(confirmMessage)) return; // Si on annule, on arrête tout

                // Préparation des données à envoyer
                const formData = new FormData();
                formData.append('action', 'toggle_block');
                formData.append('user_id', userId);

                // Appel AJAX vers admin.php
                fetch('admin.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Mise à jour visuelle du bouton sans recharger la page !
                        if (data.is_blocked) {
                            this.setAttribute('data-blocked', 'true');
                            this.style.backgroundColor = '#27ae60'; // Devient vert (Débloquer)
                            this.title = 'Débloquer ce client';
                            this.innerHTML = '<i class="fas fa-unlock"></i>';
                            
                            // Petit effet barré sur le nom du client (optionnel mais très pro)
                            const nomSpan = this.closest('tr').querySelector('.user-info span');
                            const avatarSpan = this.closest('tr').querySelector('.avatar');
                            nomSpan.style.textDecoration = 'line-through';
                            nomSpan.style.color = '#888';
                            avatarSpan.style.backgroundColor = '#666';
                        } else {
                            this.setAttribute('data-blocked', 'false');
                            this.style.backgroundColor = 'var(--accent-red)'; // Devient rouge (Bloquer)
                            this.title = 'Bloquer ce client';
                            this.innerHTML = '<i class="fas fa-lock"></i>';
                            
                            // On enlève l'effet barré
                            const nomSpan = this.closest('tr').querySelector('.user-info span');
                            const avatarSpan = this.closest('tr').querySelector('.avatar');
                            nomSpan.style.textDecoration = 'none';
                            nomSpan.style.color = '';
                            avatarSpan.style.backgroundColor = ''; // Revient à la couleur par défaut
                        }
                    } else {
                        alert("Erreur lors de l'opération.");
                    }
                })
                .catch(error => {
                    console.error("Erreur Fetch:", error);
                    alert("Problème de connexion avec le serveur.");
                });
            });
        });
    </script>
</body>
</html>