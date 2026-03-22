<?php
session_start();

// 1. SÉCURITÉ : On vérifie si l'utilisateur est connecté et si c'est bien un admin
if (!isset($_SESSION['connecte']) || $_SESSION['type'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$chemin_users = 'json/users.json';

// ====================================================================
// TRAITEMENT DE LA MISE À JOUR DU STATUT
// ====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_statut') {
    $user_id_to_update = $_POST['user_id'];
    $new_statut = $_POST['new_statut'];
    
    // On vérifie que le statut envoyé fait bien partie des choix autorisés
    $statuts_autorises = ['basic', 'silver', 'gold', 'VIP'];
    
    if (in_array($new_statut, $statuts_autorises) && file_exists($chemin_users)) {
        $users_data = file_get_contents($chemin_users);
        $utilisateurs = json_decode($users_data, true);
        
        if ($utilisateurs) {
            foreach ($utilisateurs as &$user) {
                if (isset($user['id']) && $user['id'] === $user_id_to_update) {
                    $user['statut'] = $new_statut; // On met à jour le statut
                    break;
                }
            }
            // On sauvegarde le fichier
            file_put_contents($chemin_users, json_encode($utilisateurs, JSON_PRETTY_PRINT));
            
            // On recharge la page pour éviter que le navigateur renvoie le formulaire si on fait F5
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
    <link rel="stylesheet" href="style.css">
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
            <button class="tab-btn">Clients avec commandes</button>
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
                                
                                // On récupère le statut (basic par défaut s'il n'existe pas)
                                $statut = htmlspecialchars($user['statut'] ?? 'basic');
                            ?>
                            <tr>
                                <td><strong>#<?php echo $id; ?></strong></td>
                                
                                <td>
                                    <div class="user-info">
                                        <div class="avatar"><?php echo $initiales; ?></div>
                                        <span><?php echo $prenom . ' ' . $nom; ?></span>
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
                                    <button class="action-btn delete" title="Bloquer / Supprimer">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
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

</body>
</html>