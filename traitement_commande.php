<?php
session_start();

// 1. VÉRIFICATION DE SÉCURITÉ
// Si on accède à cette page sans envoyer le formulaire ou sans être connecté, on renvoie à l'accueil
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['connecte'])) {
    header('Location: connexion.php');
    exit();
}

// 2. LECTURE DU PANIER DEPUIS LA SESSION (Fini le cart.json !)
// On récupère le panier en mémoire. S'il n'y a rien, on met un tableau vide par sécurité.
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
    $selection_plats[] = $item['nom']; // On garde juste le nom pour le tableau "selection"
}

$livraison = false;
// On ajoute les frais de livraison si le client a choisi cette option
if (isset($_POST['mode_retrait']) && $_POST['mode_retrait'] === 'livraison') {
    $livraison = true;
    $prix_total += 2.50; 
}
$nv_prix = $prix_total; // On garde une copie du prix avant réduction pour l'affichage du récapitulatif


if (isset($_SESSION['connecte']) && isset($_SESSION['statut'])) {
    
    $statut_client = $_SESSION['statut']; // On récupère son statut

    if ($statut_client === 'VIP') {
        $nv_prix *= 0.6; // 40% de réduction pour les VIP
    }
    elseif ($statut_client === 'gold') {
        $nv_prix *= 0.8; // 20% de réduction pour les gold
    }
    elseif ($statut_client === 'silver') {
        $nv_prix *= 0.9; // 10% de réduction pour les silver
    }
    else {
    $nv_prix *= 1; // Aucune réduction pour les basic
    }
}


// ====================================================================
// 4. MISE À JOUR DE commandes.json
// ====================================================================
$chemin_commandes = 'json/commandes.json';
$commandes = file_exists($chemin_commandes) ? json_decode(file_get_contents($chemin_commandes), true) : [];

// A. Génération du nouvel ID (ex: cmd006)
$dernier_id_num = 0;
foreach ($commandes as $cmd) {
    if (isset($cmd['id']) && strpos($cmd['id'], 'cmd') === 0) {
        // On extrait le numéro (ex: "cmd005" -> 5)
        $num = (int)substr($cmd['id'], 3);
        if ($num > $dernier_id_num) {
            $dernier_id_num = $num;
        }
    }
}

// On fait +1 et on rajoute les zéros devant (str_pad)
$nouveau_num = $dernier_id_num + 1;
$nouvel_id_cmd = 'cmd' . str_pad($nouveau_num, 3, '0', STR_PAD_LEFT);

// B. Création de la nouvelle commande avec ton format exact
$nouvelle_commande = [
    "id" => $nouvel_id_cmd,
    "date" => date("Y-m-d"),
    "prix" => round($nv_prix, 2),  // ON AJOUTE LES PRIX AVEC LA RÉDUCTION POUR L'AFFICHAGE DU RÉCAPITULATIF
    "selection" => $selection_plats,
    "etat" => "cuisine", // Par défaut, la commande commence en cuisine
    "note" => [0, 0, ""],
    "livraison" => $livraison
];

// C. Ajout et sauvegarde dans commandes.json
$commandes[] = $nouvelle_commande;
file_put_contents($chemin_commandes, json_encode($commandes, JSON_PRETTY_PRINT));


// ====================================================================
// 5. MISE À JOUR DE users.json (Pour attribuer la commande au client)
// ====================================================================
$user_id_connecte = $_SESSION['id'] ?? null; 

if ($user_id_connecte) {
    $chemin_users = 'json/users.json';
    $users = file_exists($chemin_users) ? json_decode(file_get_contents($chemin_users), true) : [];

    foreach ($users as &$user) { 
        if ($user['id'] === $user_id_connecte) {
            
            if (!isset($user['commandes'])) {
                $user['commandes'] = [];
            }
            
            if (!isset($user['points'])) {
                $user['points'] = 0;
            }

            $user['commandes'][] = $nouvel_id_cmd;
            $user['points'] += round($prix_total, 2); // On ajoute les points  ( PRIX SANS REMISE !!!!! ) gagnés à l'utilisateur
            
        }
        if ($user['id'] === $user_id_connecte && ($user['type'] === 'client' || $user['type'] === 'livreur')) {
            if($user['points'] >= 100 ) {
                $user['statut'] = 'silver';
            } 
            if($user['points'] >= 200 ) {
                $user['statut'] = 'gold';
            }
            if($user['points'] >= 300 ) {
                $user['statut'] = 'VIP';
            }
            $_SESSION['statut'] = $user['statut'];

            break; 
        }
    }


    file_put_contents($chemin_users, json_encode($users, JSON_PRETTY_PRINT));
}

// ====================================================================
// 6. NETTOYAGE ET REDIRECTION
// ====================================================================

// LA MAGIE EST ICI : On vide le panier en réinitialisant la session
$_SESSION['panier'] = [];

// On redirige vers l'accueil (menu principal)
header('Location: index.php');
exit();
?>