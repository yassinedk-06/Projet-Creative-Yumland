<?php
session_start();
require_once 'fonctions.php';

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

                $action_texte = $new_status ? "BLOCAGE_CLIENT" : "DEBLOCAGE_CLIENT";
                $details_texte = $new_status ? "L'admin a bloqué le client ID : " . $user_id_to_toggle : "L'admin a débloqué le client ID : " . $user_id_to_toggle;
                ajouterLog($_SESSION['id'], $_SESSION['type'], $action_texte, $details_texte, "WARNING");
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
            
            ajouterLog(
                $_SESSION['id'], 
                $_SESSION['type'], 
                "MODIFICATION_STATUT", 
                "L'admin a passé le client ID : " . $user_id_to_update . " au statut " . strtoupper($new_statut)
            );
            
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

        // 1. SÉLECTION ET ÉCOUTE DES BOUTONS
        // On cherche sur toute la page tous les éléments HTML qui ont la classe '.btn-toggle-block'.
        // Ensuite, avec '.forEach()', on passe sur chaque bouton trouvé pour lui attacher une action.
        document.querySelectorAll('.btn-toggle-block').forEach(button => {
            
            // On ajoute un "écouteur d'événement" : à chaque fois qu'on clique sur ce bouton, la fonction s'exécute.
            button.addEventListener('click', function() {
                
                // 2. RÉCUPÉRATION DES DONNÉES DU BOUTON
                // 'this' représente le bouton sur lequel on vient de cliquer.
                // On récupère la valeur de son attribut caché 'data-id' (l'ID du client).
                const userId = this.getAttribute('data-id');
                
                // On récupère l'attribut 'data-blocked'. S'il vaut exactement le texte 'true', 
                // la variable 'isBlocked' sera vraie (true), sinon elle sera fausse (false).
                const isBlocked = this.getAttribute('data-blocked') === 'true';
                
                // 3. DEMANDE DE CONFIRMATION
                // On prépare une phrase différente selon si le client est déjà bloqué ou non.
                const confirmMessage = isBlocked 
                    ? "Voulez-vous débloquer ce client ?" 
                    : "Voulez-vous bloquer ce client ? Sa session en cours sera fermée immédiatement.";
                
                // La fonction 'confirm()' affiche une petite fenêtre (pop-up) avec "OK" ou "Annuler".
                // Le '!' veut dire "Si l'admin ne confirme PAS (s'il clique sur Annuler)".
                // Dans ce cas, on fait un 'return' : cela stoppe immédiatement la fonction, rien ne se passe.
                if (!confirm(confirmMessage)) return; 

                // 4. PRÉPARATION DU COLIS (LES DONNÉES À ENVOYER)
                // FormData est un outil JavaScript qui crée un formulaire virtuel (invisible).
                const formData = new FormData();
                // On y ajoute une étiquette 'action' avec la valeur 'toggle_block' (pour que le PHP comprenne quoi faire).
                formData.append('action', 'toggle_block');
                // On y ajoute l'ID du client qu'on veut modifier.
                formData.append('user_id', userId);

                // 5. ENVOI AU SERVEUR (LA REQUÊTE AJAX / FETCH)
                // La fonction 'fetch' part contacter le fichier 'admin.php' en arrière-plan.
                fetch('admin.php', {
                    method: 'POST', // On utilise la méthode POST (comme un vrai formulaire)
                    body: formData  // On glisse notre colis (le FormData) dans le corps du message
                })
                
                // 6. RÉCEPTION DE LA RÉPONSE DU SERVEUR
                // Quand le serveur PHP a fini, il renvoie une réponse. On transforme cette réponse brute en format JSON utilisable.
                .then(response => response.json())
                
                // Maintenant, 'data' contient le tableau JSON envoyé par PHP (ex: {success: true, is_blocked: true})
                .then(data => {
                    
                    // Si le serveur confirme que l'action a réussi dans la base de données...
                    if (data.success) {
                        
                        // 7. MISE À JOUR VISUELLE (SANS RECHARGER LA PAGE)
                        
                        // CAS A : Le serveur nous dit que le client est maintenant BLOQUÉ
                        if (data.is_blocked) {
                            // On met à jour l'attribut caché du bouton pour le prochain clic
                            this.setAttribute('data-blocked', 'true');
                            // On change la couleur du bouton en vert (car la prochaine action possible sera de le débloquer)
                            this.style.backgroundColor = '#27ae60'; 
                            // On change l'infobulle quand on passe la souris dessus
                            this.title = 'Débloquer ce client';
                            // On remplace l'icône de cadenas fermé par un cadenas ouvert
                            this.innerHTML = '<i class="fas fa-unlock"></i>';
                            
                            // -- Effet sur la ligne du tableau --
                            // On remonte au parent (la ligne <tr> entière), on cherche le span contenant le nom du client
                            const nomSpan = this.closest('tr').querySelector('.user-info span');
                            // On cherche le rond contenant les initiales
                            const avatarSpan = this.closest('tr').querySelector('.avatar');
                            
                            // On barre le nom et on le grise pour montrer qu'il est inactif
                            nomSpan.style.textDecoration = 'line-through';
                            nomSpan.style.color = '#888';
                            // On grise l'avatar
                            avatarSpan.style.backgroundColor = '#666';
                        
                        // CAS B : Le serveur nous dit que le client est maintenant DÉBLOQUÉ
                        } else {
                            // On remet l'attribut caché à false
                            this.setAttribute('data-blocked', 'false');
                            // On remet le bouton en rouge
                            this.style.backgroundColor = 'var(--accent-red)'; 
                            this.title = 'Bloquer ce client';
                            // On remet l'icône du cadenas fermé
                            this.innerHTML = '<i class="fas fa-lock"></i>';
                            
                            // On retire le style barré sur le nom du client
                            const nomSpan = this.closest('tr').querySelector('.user-info span');
                            const avatarSpan = this.closest('tr').querySelector('.avatar');
                            nomSpan.style.textDecoration = 'none';
                            nomSpan.style.color = '';
                            // L'avatar reprend sa couleur de base
                            avatarSpan.style.backgroundColor = ''; 
                        }
                    
                    // Si le serveur a rencontré un problème (ex: client introuvable)
                    } else {
                        alert("Erreur lors de l'opération.");
                    }
                })
                
                // 8. GESTION DES ERREURS RÉSEAU
                // Si le serveur est tombé en panne ou que la connexion internet a sauté pendant l'envoi
                .catch(error => {
                    console.error("Erreur Fetch:", error); // Affiche l'erreur technique dans la console F12
                    alert("Problème de connexion avec le serveur.");
                });
            });
        });
    </script>
</body>
</html>