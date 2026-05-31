<?php
session_start();
require_once 'fonctions.php';

// 1. VÉRIFICATION DE SÉCURITÉ
// Si on accède à cette page sans envoyer le formulaire ou sans être connecté
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['connecte'])) {
    header('Location: connexion.php');
    exit();
}

// 2. LECTURE DU PANIER DEPUIS LA SESSION
$cart_data = $_SESSION['panier'] ?? [];

if (empty($cart_data)) {
    header('Location: carte.php'); // Panier vide, on le renvoie au menu
    exit();
}

// 3. CALCUL DU PRIX ET RÉCUPÉRATION DES NOMS DES PLATS
$prix_total = 0;
$selection_plats = [];

foreach ($cart_data as $item) {
    $prix_total += $item['prix'];
    $selection_plats[] = $item['nom'];
}

// On vérifie les options de livraison transmises depuis la page de paiement
$livraison = false;
if (isset($_POST['mode_retrait']) && $_POST['mode_retrait'] === 'livraison') {
    $livraison = true;
    $prix_total += 2.50; 
}

$nv_prix = $prix_total; 

// Application des réductions selon le statut
if (isset($_SESSION['connecte']) && isset($_SESSION['statut'])) {
    $statut_client = $_SESSION['statut'];
    if ($statut_client === 'VIP') {
        $nv_prix *= 0.6; // 40% de réduction
    } elseif ($statut_client === 'gold') {
        $nv_prix *= 0.8; // 20% de réduction
    } elseif ($statut_client === 'silver') {
        $nv_prix *= 0.9; // 10% de réduction
    }
}

// ====================================================================
// NOUVEAU : RÉCUPÉRATION DES INFOS DE PAIEMENT ET LIVRAISON
// ====================================================================
$mode_paiement = $_POST['mode_paiement'] ?? 'non_defini';
$adresse_livraison = $_POST['adresse'] ?? '';
$heure_retrait = $_POST['heure_retrait'] ?? 'ASAP';
$commentaire = $_POST['commentaire'] ?? '';


// ====================================================================
// 4. MISE À JOUR DE commandes.json
// ====================================================================
$chemin_commandes = 'json/commandes.json';
$commandes = file_exists($chemin_commandes) ? json_decode(file_get_contents($chemin_commandes), true) : [];

// Génération du nouvel ID
$dernier_id_num = 0;
foreach ($commandes as $cmd) {
    if (isset($cmd['id']) && strpos($cmd['id'], 'cmd') === 0) {
        $num = (int)substr($cmd['id'], 3);
        if ($num > $dernier_id_num) {
            $dernier_id_num = $num;
        }
    }
}

$nouveau_num = $dernier_id_num + 1;
$nouvel_id_cmd = 'cmd' . str_pad($nouveau_num, 3, '0', STR_PAD_LEFT);

// Création de la nouvelle commande
$nouvelle_commande = [
    "id" => $nouvel_id_cmd,
    "date" => date("Y-m-d H:i:s"),
    "prix" => round($nv_prix, 2), 
    "selection" => $selection_plats,
    "etat" => "cuisine", 
    "note" => [0, 0, ""],
    "livraison" => $livraison,
    "adresse" => $adresse_livraison,
    "heure" => $heure_retrait,
    "commentaire" => $commentaire,
    "paiement" => $mode_paiement // On ajoute le mode de paiement !
];

$commandes[] = $nouvelle_commande;
file_put_contents($chemin_commandes, json_encode($commandes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));


// ====================================================================
// 5. MISE À JOUR DE users.json (Points et assignation)
// ====================================================================
$user_id_connecte = $_SESSION['id'] ?? null; 

if ($user_id_connecte) {
    $chemin_users = 'json/users.json';
    $users = file_exists($chemin_users) ? json_decode(file_get_contents($chemin_users), true) : [];

    foreach ($users as &$user) { 
        if ($user['id'] === $user_id_connecte) {
            
            if (!isset($user['commandes'])) $user['commandes'] = [];
            if (!isset($user['points'])) $user['points'] = 0;

            $user['commandes'][] = $nouvel_id_cmd;
            $user['points'] += round($prix_total, 2) * 2; // Points basés sur le prix SANS remise
            
            // Mise à jour du statut en direct
            if ($user['type'] === 'client' || $user['type'] === 'livreur') {
                if($user['points'] >= 300 ) {
                    $user['statut'] = 'VIP';
                } elseif($user['points'] >= 200 ) {
                    $user['statut'] = 'gold';
                } elseif($user['points'] >= 100 ) {
                    $user['statut'] = 'silver';
                }
                $_SESSION['statut'] = $user['statut'];
            }
            break; 
        }
    }
    file_put_contents($chemin_users, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}


// ====================================================================
// 6. NETTOYAGE, LOGS ET REDIRECTION
// ====================================================================

// On enregistre la commande dans les logs
$detail_log = "Création de la commande " . $nouvel_id_cmd . " pour un montant de " . number_format($nv_prix, 2, ',', ' ') . " € (Paiement: " . strtoupper($mode_paiement) . ")";
ajouterLog($_SESSION['id'], $_SESSION['type'], "NOUVELLE_COMMANDE", $detail_log);

// On vide le panier en réinitialisant la session
$_SESSION['panier'] = [];

// On redirige vers l'accueil
header('Location: index.php');
exit();
?>