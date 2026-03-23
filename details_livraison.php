<?php
session_start();

// 1. SÉCURITÉ
if (!isset($_SESSION['connecte']) || !in_array($_SESSION['type'], ['admin', 'livreur'])) {
    header('Location: index.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: livraison.php');
    exit();
}

$id_commande = $_GET['id'];
$chemin_commandes = 'json/commandes.json';
$chemin_users = 'json/users.json';

// 2. RÉCUPÉRER LA COMMANDE SPÉCIFIQUE
$commandes = file_exists($chemin_commandes) ? json_decode(file_get_contents($chemin_commandes), true) : [];
$commande_actuelle = null;
$commande_index = null;

foreach ($commandes as $index => $cmd) {
    if ($cmd['id'] === $id_commande) {
        $commande_actuelle = $cmd;
        $commande_index = $index;
        break;
    }
}

if (!$commande_actuelle) {
    header('Location: livraison.php');
    exit();
}

// ====================================================================
// 3. ACTIONS DU LIVREUR (Les 3 boutons)
// ====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // Selon le bouton cliqué, on change le statut avec le mot exact que tu as demandé
    if ($_POST['action'] === 'terminer') {
        $commandes[$commande_index]['etat'] = 'livrée';
    } 
    elseif ($_POST['action'] === 'annuler') {
        $commandes[$commande_index]['etat'] = 'annulé'; 
    } 
    elseif ($_POST['action'] === 'attente') {
        $commandes[$commande_index]['etat'] = 'en attente';
    }

    // On sauvegarde la modification
    file_put_contents($chemin_commandes, json_encode($commandes, JSON_PRETTY_PRINT));
    
    // Et hop, retour à la liste des courses !
    header('Location: livraison.php');
    exit();
}

// ====================================================================
// 4. CHANGER LE STATUT EN "EN LIVRAISON" (À l'ouverture de la page)
// ====================================================================
if ($commande_actuelle['etat'] === 'en attente') {
    $commandes[$commande_index]['etat'] = 'en livraison';
    $commande_actuelle['etat'] = 'en livraison';
    file_put_contents($chemin_commandes, json_encode($commandes, JSON_PRETTY_PRINT));
}

// 5. RÉCUPÉRER LES INFOS DU CLIENT
$users = file_exists($chemin_users) ? json_decode(file_get_contents($chemin_users), true) : [];
$client_info = ['nom' => 'Client Inconnu', 'address' => 'Adresse non précisée', 'num' => ''];

foreach ($users as $user) {
    if (isset($user['type']) && (  $user['type'] === 'client'  || $user['type'] === 'admin' ) && isset($user['commandes']) && in_array($id_commande, $user['commandes'])) {
        $client_info['nom'] = ($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? '');
        $client_info['address'] = $user['address'] ?? 'Adresse introuvable';
        $client_info['num'] = $user['num'] ?? '';
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Course #<?= strtoupper($id_commande) ?> - Bien Harr</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;800&display=swap" rel="stylesheet">
</head>
<body class="body-livreur">

    <header class="header-livreur">
        <form action="details_livraison.php?id=<?= $id_commande ?>" method="POST" style="margin: 0; display: inline;">
            <input type="hidden" name="action" value="attente">
            <button type="submit" style="background: none; border: none; color: white; font-size: 1.8rem; padding: 10px; cursor: pointer;">
                <i class="fas fa-arrow-left"></i>
            </button>
        </form>
        <h1>COURSE #<?= strtoupper($id_commande) ?></h1>
        <div style="width: 40px;"></div>
    </header>

    <div class="details-container">

        <div class="info-block">
            <h2>Informations Client</h2>
            <div class="client-name"><?= htmlspecialchars($client_info['nom']) ?></div>
            <div class="client-address">
                <i class="fas fa-map-marker-alt" style="color: var(--accent-red); margin-right: 10px;"></i> 
                <?= htmlspecialchars($client_info['address']) ?>
            </div>
            
            <div class="action-row">
                <a href="http://maps.google.com/?q=<?= urlencode($client_info['address']) ?>" target="_blank" class="btn-action btn-gps">
                    <i class="fas fa-location-arrow"></i> Y ALLER
                </a>
                
                <a href="tel:<?= htmlspecialchars($client_info['num']) ?>" class="btn-action btn-call">
                    <i class="fas fa-phone-alt"></i> APPELER
                </a>
            </div>
        </div>

        <div class="info-block">
            <h2>À Livrer (<?= number_format($commande_actuelle['prix'], 2, ',', ' ') ?> €)</h2>
            <ul class="order-items-livraison">
                <?php foreach ($commande_actuelle['selection'] as $plat): ?>
                    <li><i class="fas fa-utensils" style="margin-right: 10px; color: #555;"></i> <?= htmlspecialchars($plat) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div style="margin-top: 30px; display: flex; flex-direction: column; gap: 15px;">
            
            <form action="details_livraison.php?id=<?= $id_commande ?>" method="POST" style="margin: 0;">
                <input type="hidden" name="action" value="terminer">
                <button type="submit" class="btn-massive btn-terminer">
                    <i class="fas fa-check-circle" style="margin-right: 10px;"></i> TERMINER LA LIVRAISON
                </button>
            </form>

            <form action="details_livraison.php?id=<?= $id_commande ?>" method="POST" style="margin: 0;">
                <input type="hidden" name="action" value="annuler">
                <button type="submit" class="btn-massive btn-annuler">
                    <i class="fas fa-times-circle" style="margin-right: 10px;"></i> ANNULER LA LIVRAISON
                </button>
            </form>

            <form action="details_livraison.php?id=<?= $id_commande ?>" method="POST" style="margin: 0;">
                <input type="hidden" name="action" value="attente">
                <button type="submit" class="btn-massive btn-attente">
                    <i class="fas fa-undo" style="margin-right: 10px;"></i> PRENDRE UNE AUTRE COURSE
                </button>
            </form>

        </div>

    </div>

</body>
</html>