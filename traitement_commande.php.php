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
    "prix" => round($prix_total, 2),
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
            
            if (!isset($user['point'])) {
                $user['point'] = 0;
            }

            $user['commandes'][] = $nouvel_id_cmd;
            $user['points'] += round($prix_total, 2); // On ajoute les points gagnés à l'utilisateur
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