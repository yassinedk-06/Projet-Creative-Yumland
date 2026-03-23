<?php
session_start();

// 1. SÉCURITÉ : On autorise l'accès à l'Admin ET au Restaurateur
if (!isset($_SESSION['connecte']) || ($_SESSION['type'] !== 'admin' && $_SESSION['type'] !== 'restaurateur')) {
    header('Location: index.php');
    exit();
}

$chemin_commandes = 'json/commandes.json';

// ====================================================================
// TRAITEMENT DE LA MISE À JOUR DE L'ÉTAT DE LA COMMANDE
// ====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_etat') {
    $cmd_id_to_update = $_POST['cmd_id'];
    $new_etat = $_POST['new_etat'];
    
    // On met à jour la liste des états pour qu'ils correspondent exactement à ce qu'on utilise partout
    $etats_autorises = ['en attente', 'cuisine', 'en livraison', 'livrée', 'annulée'];
    
    // On nettoie la chaîne reçue pour éviter les bugs de majuscules
    $new_etat_propre = mb_strtolower($new_etat, 'UTF-8');
    
    if (in_array($new_etat_propre, $etats_autorises) && file_exists($chemin_commandes)) {
        $cmd_data = file_get_contents($chemin_commandes);
        $commandes = json_decode($cmd_data, true);
        
        if ($commandes) {
            foreach ($commandes as &$cmd) {
                if (isset($cmd['id']) && $cmd['id'] === $cmd_id_to_update) {
                    $cmd['etat'] = $new_etat_propre; // On sauvegarde l'état propre en minuscules
                    break;
                }
            }
            // On sauvegarde le fichier
            file_put_contents($chemin_commandes, json_encode($commandes, JSON_PRETTY_PRINT));
            
            // On recharge la page pour valider
            header('Location: commandes.php');
            exit();
        }
    }
}
// ====================================================================


// 2. RÉCUPÉRATION DES COMMANDES POUR L'AFFICHAGE
$toutes_les_commandes = [];
if (file_exists($chemin_commandes)) {
    $json_data = file_get_contents($chemin_commandes);
    $toutes_les_commandes = json_decode($json_data, true) ?? [];
    
    // Bonus : Trier les commandes de la plus récente à la plus ancienne
    usort($toutes_les_commandes, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Commandes - Bien Harr</title>
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
            <div class="logo">BIEN <span>HARR</span> <span style="font-size: 0.8rem; color: var(--primary-blue);">GESTION</span></div>
            <ul class="menu-links">
                <li><div class="menu-header">MENU</div></li>
                <li><a href="index.php">Retour au Site</a></li>
                <?php if ($_SESSION['type'] === 'admin'): ?>
                    <li><a href="admin.php">Gestion Clients</a></li>
                <?php endif; ?>
                <li><a href="commandes.php" class="active">Gestion Commandes</a></li>
                <li><a href="deconnexion.php" style="color: var(--accent-red);">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <main class="admin-container dashboard-container">
        
        <div class="admin-header">
            <h1>Suivi des Commandes</h1>
            <div class="admin-search">
                <input type="text" placeholder="N° Commande...">
                <button><i class="fas fa-search"></i></button>
            </div>
        </div>

        <section class="admin-table-wrapper dashboard-card">
            <table class="admin-table history-table">
                <thead>
                    <tr>
                        <th>N° Commande</th>
                        <th>Date</th>
                        <th>Plats à préparer</th>
                        <th>Prix</th>
                        <th>État de la commande</th>
                        <th>Détails</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($toutes_les_commandes)): ?>
                        <tr><td colspan="6" style="text-align: center;">Aucune commande dans le système.</td></tr>
                    <?php else: ?>
                        <?php foreach ($toutes_les_commandes as $cmd): ?>
                            <?php 
                                $id = htmlspecialchars($cmd['id']);
                                $date = date('d/m/Y', strtotime($cmd['date']));
                                $prix = number_format($cmd['prix'], 2, ',', ' ');
                                $plats = htmlspecialchars(implode(" + ", $cmd['selection']));
                                
                                // On récupère l'état et on le force en minuscules pour éviter les erreurs !
                                $etat_brut = $cmd['etat'] ?? 'en attente';
                                $etat_propre = mb_strtolower($etat_brut, 'UTF-8');

                                // Attribution de la bonne classe CSS
                                $class_etat = 'attente';
                                if ($etat_propre === 'cuisine') $class_etat = 'cuisine';
                                if ($etat_propre === 'en livraison') $class_etat = 'livraison';
                                if ($etat_propre === 'livrée') $class_etat = 'livree';
                                if ($etat_propre === 'annulée' || $etat_propre === 'annulé') $class_etat = 'annulee';
                            ?>
                            <tr>
                                <td><strong>#<?php echo $id; ?></strong></td>
                                <td><?php echo $date; ?></td>
                                <td><?php echo $plats; ?></td>
                                <td><strong><?php echo $prix; ?> €</strong></td>
                                
                                <td>
                                    <form action="commandes.php" method="POST" style="margin: 0;">
                                        <input type="hidden" name="action" value="update_etat">
                                        <input type="hidden" name="cmd_id" value="<?php echo $id; ?>">
                                        
                                        <select name="new_etat" class="select-etat <?php echo $class_etat; ?>" onchange="this.form.submit()">
                                            <option value="en attente" <?php if($etat_propre === 'en attente') echo 'selected'; ?>>⏳ En attente</option>
                                            <option value="cuisine" <?php if($etat_propre === 'cuisine') echo 'selected'; ?>>🍳 En cuisine</option>
                                            <option value="en livraison" <?php if($etat_propre === 'en livraison') echo 'selected'; ?>>🛵 En livraison</option>
                                            <option value="livrée" <?php if($etat_propre === 'livrée') echo 'selected'; ?>>✅ Livrée</option>
                                            <option value="annulée" <?php if($etat_propre === 'annulée' || $etat_propre === 'annulé') echo 'selected'; ?>>❌ Annulée</option>
                                        </select>
                                    </form>
                                </td>
                                
                                <td>
                                    <button class="action-btn view" title="Voir les détails de la commande">
                                        <i class="fas fa-eye"></i>
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
        <p>Bien Harr © 2026 - Interface de Gestion</p>
    </footer>

</body>
</html>