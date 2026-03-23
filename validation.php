<?php
session_start();

$chemin_cart = 'json/cart.json';

// ====================================================================
// TRAITEMENT : SUPPRESSION D'UN ARTICLE
// ====================================================================
if (isset($_GET['action']) && $_GET['action'] === 'supprimer' && isset($_GET['index'])) {
    $index_a_supprimer = (int)$_GET['index'];

    if (file_exists($chemin_cart)) {
        $cart_data = json_decode(file_get_contents($chemin_cart), true) ?? [];

        if (isset($cart_data[$index_a_supprimer])) {
            unset($cart_data[$index_a_supprimer]);
            $cart_data = array_values($cart_data); // On réorganise les numéros
            file_put_contents($chemin_cart, json_encode($cart_data, JSON_PRETTY_PRINT));
        }
    }
    // On recharge la page
    header('Location: validation.php');
    exit();
}

// ====================================================================
// LECTURE DU PANIER POUR L'AFFICHAGE
// ====================================================================
$cart_data = [];
$sous_total = 0;

if (file_exists($chemin_cart)) {
    $contenu_cart = file_get_contents($chemin_cart);
    if (!empty($contenu_cart)) {
        $cart_data = json_decode($contenu_cart, true) ?? [];
        foreach ($cart_data as $item) {
            $sous_total += $item['prix'];
        }
    }
}

// Frais de livraison (fixe)
$frais_livraison = 2.50;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Validation Commande - Bien Harr</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;600;800&display=swap" rel="stylesheet">
    <style>
        /* Styles de la page */
        .checkout-container {
            max-width: 900px; margin: 50px auto; padding: 0 20px;
            display: flex; gap: 30px; flex-wrap: wrap;
        }
        .checkout-section {
            background: white; padding: 30px; border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05); flex: 1; min-width: 300px;
            border: 1px solid #eee;
        }
        .checkout-section h2 {
            color: var(--primary-blue); border-bottom: 2px solid var(--bg-light);
            padding-bottom: 15px; margin-top: 0;
        }
        .recap-list { list-style: none; padding: 0; margin: 0; }
        .recap-list li { display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #eee; }
        .recap-total { margin-top: 20px; font-size: 1.3rem; text-align: right; color: var(--text-dark); }
        
        .form-group { margin-bottom: 20px; }
        .form-group label.titre-champ { display: block; font-weight: 600; margin-bottom: 10px; font-size: 0.95rem; }
        
        .form-control {
            width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px;
            font-family: inherit; font-size: 1rem; box-sizing: border-box;
        }

        /* L'ASTUCE CSS SANS JAVASCRIPT */
        .options-wrapper {
            background: #f9f9f9; padding: 15px; border-radius: 8px; border: 1px solid #eee;
        }
        .radio-option {
            margin-bottom: 10px; font-weight: 600; cursor: pointer; display: block;
        }
        
        /* 1. On cache le champ adresse par défaut */
        .champ-adresse {
            display: none; 
            margin-top: 15px; padding-top: 15px; border-top: 1px dashed #ccc;
        }
        
        /* 2. LA MAGIE : Si l'input avec l'id "radio-livraison" est coché, 
           alors son "frère" (~) qui a la classe "champ-adresse" devient visible ! */
        #radio-livraison:checked ~ .champ-adresse {
            display: block;
        }
    </style>
</head>
<body>

<header>
    <nav style="display: flex; justify-content: center; position: relative;">
        <a href="carte.php" class="icon-btn" style="position: absolute; left: 20px; top: 50%; transform: translateY(-50%);">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
        <div class="logo">BIEN <span>HARR</span></div>
    </nav>
</header>

<div class="checkout-container">

    <div class="checkout-section">
        <h2>🍽️ Votre Commande</h2>
        
        <?php if(!empty($cart_data)): ?>
            <ul class="recap-list">
                <?php foreach ($cart_data as $index => $item): ?>
                    <li>
                        <span>
                            <a href="validation.php?action=supprimer&index=<?= $index ?>" style="color: var(--accent-red); font-weight: bold; text-decoration: none; margin-right: 10px; font-size: 1.2rem;" title="Retirer">&times;</a>
                            <?= htmlspecialchars($item['nom']) ?>
                        </span>
                        <strong><?= number_format($item['prix'], 2, ',', ' ') ?> €</strong>
                    </li>
                <?php endforeach; ?>
            </ul>
            
            <div class="recap-total">
                <strong>Sous-total : <?= number_format($sous_total, 2, ',', ' ') ?> €</strong>
                <p style="font-size: 0.85rem; color: #888; font-weight: normal; margin-top: 5px;">(Hors frais de livraison éventuels)</p>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: #888; margin-top: 30px;">Votre panier est vide.</p>
            <div style="text-align: center; margin-top: 20px;">
                <a href="carte.php" class="btn-order" style="text-decoration: none;">Retour à la carte</a>
            </div>
        <?php endif; ?>
    </div>

    <?php if(!empty($cart_data)): ?>
    <div class="checkout-section">
        <h2>🛵 Mode de retrait</h2>
        
        <form action="traitement_commande.php" method="POST">
            
            <div class="form-group">
                <label class="titre-champ">Comment souhaitez-vous récupérer votre commande ?</label>
                
                <div class="options-wrapper">
                    
                    <label class="radio-option">
                        <input type="radio" name="mode_retrait" value="sur_place" checked>
                        Sur place (Retrait)
                    </label>
                    
                    <label class="radio-option">
                        <input type="radio" name="mode_retrait" value="livraison" id="radio-livraison">
                        Livraison à domicile (+<?= number_format($frais_livraison, 2, ',', ' ') ?> €)
                    </label>

                    <div class="champ-adresse">
                        <label for="adresse" class="titre-champ">Adresse de livraison complète :</label>
                        <textarea name="adresse" id="adresse" class="form-control" rows="2" placeholder="Ex: 12 Rue des Oliviers, Appt 3..."></textarea>
                    </div>

                </div>
            </div>

            <div class="form-group">
                <label for="heure_retrait" class="titre-champ">Pour quand la souhaitez-vous ?</label>
                <select name="heure_retrait" id="heure_retrait" class="form-control">
                    <option value="ASAP">Dès que possible</option>
                    <option value="12:00">Pour 12h00</option>
                    <option value="12:30">Pour 12h30</option>
                    <option value="13:00">Pour 13h00</option>
                    <option value="19:00">Pour 19h00</option>
                    <option value="19:30">Pour 19h30</option>
                    <option value="20:00">Pour 20h00</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="commentaire" class="titre-champ">Commentaire pour la cuisine (Optionnel)</label>
                <input type="text" name="commentaire" id="commentaire" class="form-control" placeholder="Sans harissa, pas trop de sel...">
            </div>

            <button type="submit" class="btn-order" style="width: 100%; margin-top: 15px; font-size: 1.1rem;">Confirmer ma commande</button>

        </form>
    </div>
    <?php endif; ?>

</div>

<footer>
    <p>Bien Harr © 2026 - Projet Yumland</p>
</footer>

</body>
</html>