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
    // 1. Afficher/Cacher la zone CB
    const radioCash = document.getElementById('radio-cash');
    const radioCarte = document.getElementById('radio-carte');
    const zoneCb = document.getElementById('zone-cb');

    radioCarte.addEventListener('change', () => zoneCb.style.display = 'block');
    radioCash.addEventListener('change', () => {
        zoneCb.style.display = 'none';
        document.getElementById('erreur-cb').textContent = ''; 
    });

    // 2. Formatage automatique du numéro de carte (ajoute des espaces)
    document.getElementById('cb_num').addEventListener('input', function (e) {
        let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
        let formattedValue = '';
        for (let i = 0; i < value.length; i++) {
            if (i > 0 && i % 4 === 0) formattedValue += ' ';
            formattedValue += value[i];
        }
        e.target.value = formattedValue;
    });

    // 3. Formatage de la date (ajoute le slash)
    document.getElementById('cb_date').addEventListener('input', function (e) {
        let value = e.target.value.replace(/\//g, '').replace(/[^0-9]/gi, '');
        if (value.length >= 2) value = value.substring(0, 2) + '/' + value.substring(2, 4);
        e.target.value = value;
    });

    // 4. Vérification avant l'envoi
    document.getElementById('formPaiement').addEventListener('submit', function(e) {
        if (radioCarte.checked) {
            const num = document.getElementById('cb_num').value.replace(/\s/g, '');
            const date = document.getElementById('cb_date').value;
            const cvv = document.getElementById('cb_cvv').value;
            let erreur = "";

            if (num.length !== 16) {
                erreur = "Le numéro de carte doit contenir 16 chiffres.";
            } else if (!date.match(/^(0[1-9]|1[0-2])\/\d{2}$/)) {
                erreur = "La date d'expiration est invalide (format MM/YY attendu).";
            } else if (cvv.length !== 3 || isNaN(cvv)) {
                erreur = "Le cryptogramme (CVV) doit contenir 3 chiffres.";
            }

            if (erreur !== "") {
                e.preventDefault(); 
                document.getElementById('erreur-cb').textContent = erreur;
            }
        }
    });
</script>
</body>
</html>