<?php
session_start();
require_once 'fonctions.php';

// Si on accède à cette page sans passer par le panier, on redirige
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['panier'])) {
    header('Location: validation.php');
    exit();
}

// 1. ON RÉCUPÈRE LES INFOS DE LA PAGE PRÉCÉDENTE
$mode_retrait = $_POST['mode_retrait'] ?? 'sur_place';
$adresse = $_POST['adresse'] ?? '';
$heure_retrait = $_POST['heure_retrait'] ?? 'ASAP';
$commentaire = $_POST['commentaire'] ?? '';

// 2. ON RECALCULE LE TOTAL
$sous_total = 0;
foreach ($_SESSION['panier'] as $item) {
    $sous_total += $item['prix'];
}

// Application de la réduction selon le statut
if (isset($_SESSION['statut'])) {
    if ($_SESSION['statut'] === 'VIP') $sous_total *= 0.6;
    elseif ($_SESSION['statut'] === 'gold') $sous_total *= 0.8;
    elseif ($_SESSION['statut'] === 'silver') $sous_total *= 0.9;
}

$frais_livraison = ($mode_retrait === 'livraison') ? 2.50 : 0;
$total_a_payer = $sous_total + $frais_livraison;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paiement - Bien Harr</title>
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
<body>

<header>
    <nav style="display: flex; justify-content: center;">
        <div class="logo">BIEN <span>HARR</span></div>
    </nav>
</header>

<main class="payment-card">
    <h2 style="text-align: center; color: var(--primary-blue); margin-bottom: 20px;">💳 Paiement</h2>
    
    <div class="total-box">
        Total à régler : <strong><?= number_format($total_a_payer, 2, ',', ' ') ?> €</strong>
    </div>

    <form action="traitement_commande.php" method="POST" id="formPaiement">
        
        <input type="hidden" name="mode_retrait" value="<?= htmlspecialchars($mode_retrait) ?>">
        <input type="hidden" name="adresse" value="<?= htmlspecialchars($adresse) ?>">
        <input type="hidden" name="heure_retrait" value="<?= htmlspecialchars($heure_retrait) ?>">
        <input type="hidden" name="commentaire" value="<?= htmlspecialchars($commentaire) ?>">

        <div class="options-wrapper">
            <label class="radio-option">
                <input type="radio" name="mode_paiement" value="cash" id="radio-cash" checked> 
                <i class="fas fa-coins" style="color: #f1c40f;"></i> Payer en espèces (à la réception)
            </label>
            
            <label class="radio-option" style="margin-top: 10px;">
                <input type="radio" name="mode_paiement" value="carte" id="radio-carte"> 
                <i class="fas fa-credit-card" style="color: #3498db;"></i> Carte Bancaire
            </label>

            <div class="cb-fields" id="zone-cb">
                <div class="form-group">
                    <label class="titre-champ">Numéro de carte</label>
                    <input type="text" id="cb_num" class="form-control" placeholder="0000 0000 0000 0000" maxlength="19">
                </div>
                <div class="cb-grid">
                    <div class="form-group">
                        <label class="titre-champ">Expiration</label>
                        <input type="text" id="cb_date" class="form-control" placeholder="MM/YY" maxlength="5">
                    </div>
                    <div class="form-group">
                        <label class="titre-champ">CVV</label>
                        <input type="password" id="cb_cvv" class="form-control" placeholder="123" maxlength="3">
                    </div>
                </div>
                <span id="erreur-cb" class="error-msg"></span>
            </div>
        </div>

        <button type="submit" class="btn-order" style="width: 100%; margin-top: 20px; font-size: 1.1rem;">
            <i class="fas fa-lock"></i> Payer et Commander
        </button>
    </form>
</main>

<script>
    // ====================================================================
    // 1. AFFICHER / CACHER LA ZONE DE CARTE BANCAIRE
    // ====================================================================
    // On récupère les éléments HTML grâce à leur ID
    const radioCash = document.getElementById('radio-cash');
    const radioCarte = document.getElementById('radio-carte');
    const zoneCb = document.getElementById('zone-cb');

    // On écoute le changement d'état du bouton radio "Carte"
    // S'il est sélectionné, on modifie le CSS pour afficher la zone (display: 'block')
    radioCarte.addEventListener('change', () => zoneCb.style.display = 'block');
    
    // Si l'utilisateur change d'avis et sélectionne "Cash"
    radioCash.addEventListener('change', () => {
        // On recache la zone de la carte
        zoneCb.style.display = 'none';
        // On vide l'éventuel message d'erreur qui serait resté affiché
        document.getElementById('erreur-cb').textContent = ''; 
    });

    // ====================================================================
    // 2. FORMATAGE DYNAMIQUE DU NUMÉRO DE CARTE (AJOUT D'ESPACES)
    // ====================================================================
    // 'input' est déclenché à chaque fois qu'une touche est pressée ou effacée
    document.getElementById('cb_num').addEventListener('input', function (e) {
        
        // a) NETTOYAGE : On prend ce que l'utilisateur vient de taper
        // .replace(/\s+/g, '') supprime tous les espaces existants
        // .replace(/[^0-9]/gi, '') supprime tout ce qui n'est pas un chiffre (lettres, symboles)
        let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
        
        let formattedValue = '';
        
        // b) RECONSTRUCTION : On parcourt les chiffres nettoyés un par un
        for (let i = 0; i < value.length; i++) {
            // Si on n'est pas au premier chiffre (i > 0) ET qu'on est à un multiple de 4 (i % 4 === 0)
            // On ajoute un espace avant d'ajouter le chiffre
            if (i > 0 && i % 4 === 0) formattedValue += ' ';
            formattedValue += value[i];
        }
        
        // On remplace la valeur dans le champ de saisie par notre nouvelle valeur formatée
        e.target.value = formattedValue;
    });

    // ====================================================================
    // 3. FORMATAGE DYNAMIQUE DE LA DATE D'EXPIRATION (MM/YY)
    // ====================================================================
    document.getElementById('cb_date').addEventListener('input', function (e) {
        
        // Nettoyage : On supprime les slashs '/' existants et tout ce qui n'est pas un chiffre
        let value = e.target.value.replace(/\//g, '').replace(/[^0-9]/gi, '');
        
        // Si l'utilisateur a tapé au moins 2 chiffres (le mois est complet)
        if (value.length >= 2) {
            // On coupe la chaîne en deux et on insère un '/' au milieu
            // substring(0, 2) prend les 2 premiers chiffres (Mois)
            // substring(2, 4) prend les chiffres suivants (Année)
            value = value.substring(0, 2) + '/' + value.substring(2, 4);
        }
        
        // On met à jour l'affichage
        e.target.value = value;
    });

    // ====================================================================
    // 4. VÉRIFICATION DE SÉCURITÉ AVANT L'ENVOI DU FORMULAIRE
    // ====================================================================
    // On écoute l'événement 'submit' (quand on clique sur le bouton "Payer et Commander")
    document.getElementById('formPaiement').addEventListener('submit', function(e) {
        
        // On ne fait ces vérifications complexes QUE si le client a choisi de payer par carte
        if (radioCarte.checked) {
            
            // On récupère les valeurs actuelles des champs (en enlevant les espaces du numéro de carte)
            const num = document.getElementById('cb_num').value.replace(/\s/g, '');
            const date = document.getElementById('cb_date').value;
            const cvv = document.getElementById('cb_cvv').value;
            
            let erreur = ""; // Variable pour stocker le message d'erreur

            // TEST 1 : La longueur du numéro de carte
            if (num.length !== 16) {
                erreur = "Le numéro de carte doit contenir 16 chiffres.";
            } 
            // TEST 2 : Le format de la date (grâce à une expression régulière)
            // ^(0[1-9]|1[0-2]) vérifie que le mois va de 01 à 12
            // \/\d{2}$ vérifie qu'il y a bien un '/' suivi de 2 chiffres exacts
            else if (!date.match(/^(0[1-9]|1[0-2])\/\d{2}$/)) {
                erreur = "La date d'expiration est invalide (format MM/YY attendu).";
            } 
            // TEST 3 : Le CVV
            // On vérifie qu'il fait 3 caractères ET que ce n'est pas "Not a Number" (!isNaN)
            else if (cvv.length !== 3 || isNaN(cvv)) {
                erreur = "Le cryptogramme (CVV) doit contenir 3 chiffres.";
            }

            // CONCLUSION DE L'ANALYSE
            // Si la variable erreur n'est plus vide, c'est qu'un des 3 tests a échoué
            if (erreur !== "") {
                // e.preventDefault() est LA commande vitale ici : elle annule l'envoi du formulaire au serveur (PHP) !
                e.preventDefault(); 
                
                // On affiche le message d'erreur rouge à l'utilisateur pour qu'il corrige
                document.getElementById('erreur-cb').textContent = erreur;
            }
        }
        // Si aucune erreur, le code continue normalement et le formulaire part vers 'traitement_commande.php'
    });
</script>
</body>
</html>