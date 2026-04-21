<?php
session_start();

// 1. SÉCURITÉ : Accès réservé à l'Admin
if (!isset($_SESSION['connecte']) || $_SESSION['type'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$message = '';
$chemin_plats = 'json/plats.json';

// ====================================================================
// TRAITEMENT DU FORMULAIRE D'AJOUT
// ====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ajouter_plat') {
    
    $categorie = $_POST['categorie'];
    $nom = htmlspecialchars($_POST['nom']);
    $prix = (float)$_POST['prix'];
    $description = htmlspecialchars($_POST['description']);
    
    // Gestion de l'image (Upload)
    $chemin_photo = 'src/default.jpg'; // Image par défaut si oubli

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $nom_fichier_original = basename($_FILES['photo']['name']);
        
        // On nettoie le nom du fichier pour éviter les bugs (espaces, accents...)
        $nom_fichier_propre = preg_replace("/[^a-zA-Z0-9.]/", "_", $nom_fichier_original);
        
        // On rajoute un numéro unique devant pour éviter d'écraser une image existante
        $chemin_cible = 'src/' . time() . '_' . $nom_fichier_propre;
        
        // On déplace l'image du dossier temporaire vers notre dossier "src/"
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $chemin_cible)) {
            $chemin_photo = $chemin_cible;
        }
    }

    // Création du nouvel ID (ex: plat_64abc123)
    $nouvel_id = uniqid('plat_');

    // Création du tableau du plat au même format que ton plats.json
    // Format : [0: ID, 1: Nom, 2: Prix, 3: Image, 4: Description]
    $nouveau_plat = [
        $nouvel_id,
        $nom,
        $prix,
        $chemin_photo,
        $description
    ];

    // Lecture du fichier JSON
    $plats_data = file_exists($chemin_plats) ? json_decode(file_get_contents($chemin_plats), true) : [];

    // Sécurité : Si la catégorie n'existe pas encore, on la crée
    if (!isset($plats_data[$categorie])) {
        $plats_data[$categorie] = [];
    }

    // Ajout du plat dans la bonne catégorie
    $plats_data[$categorie][] = $nouveau_plat;

    // Sauvegarde dans le fichier JSON
    file_put_contents($chemin_plats, json_encode($plats_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    $message = "✅ Le plat '{$nom}' a été ajouté avec succès à la carte !";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un Plat - Bien Harr</title>
    <?php
    // 1. On définit les thèmes autorisés (Sécurité pour éviter qu'on injecte n'importe quoi)
    $themes_autorises = ['style.css', 'style-dark.css'];
    $theme_actuel = 'style.css'; // Le thème par défaut

    // 2. On vérifie si le cookie existe ET si sa valeur est cohérente (autorisée)
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
            <div class="logo">BIEN <span>HARR</span> <span style="font-size: 0.8rem; color: var(--primary-blue);">GESTION</span></div>
            <ul class="menu-links">
                <li><div class="menu-header">MENU ADMIN</div></li>
                <li><a href="index.php">Retour au Site</a></li>
                <li><a href="admin.php">Gestion Clients</a></li>
                <li><a href="commandes.php">Gestion Commandes</a></li>
                <li><a href="ajout_plat.php" class="active">Ajouter un Plat</a></li>
                <li><a href="deconnexion.php" style="color: var(--accent-red);">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <main class="admin-container dashboard-container">
        
        <div class="admin-header" style="justify-content: center; margin-bottom: 30px;">
            <h1>Ajouter un Nouveau Plat</h1>
        </div>

        <div class="form-container">
            <?php if (!empty($message)): ?>
                <div class="alert-success"><?= $message ?></div>
            <?php endif; ?>

            <form action="ajout_plat.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="ajouter_plat">

                <div class="form-group">
                    <label for="categorie"><i class="fas fa-tags"></i> Catégorie du plat</label>
                    <select name="categorie" id="categorie" class="form-control" required>
                        <option value="entrées">Entrées</option>
                        <option value="plats">Plats Traditionnels</option>
                        <option value="boissons_chaudes">Boissons Chaudes</option>
                        <option value="boissons_froides">Boissons Froides</option>
                        <option value="desserts">Desserts</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="nom"><i class="fas fa-utensils"></i> Nom du plat</label>
                    <input type="text" name="nom" id="nom" class="form-control" placeholder="Ex: Salade Tunisienne" required>
                </div>

                <div class="form-group">
                    <label for="prix"><i class="fas fa-euro-sign"></i> Prix (€)</label>
                    <input type="number" name="prix" id="prix" class="form-control" step="0.10" min="0" placeholder="Ex: 8.50" required>
                </div>

                <div class="form-group">
                    <label for="description"><i class="fas fa-align-left"></i> Description</label>
                    <textarea name="description" id="description" class="form-control" rows="3" placeholder="Ingrédients, préparation..." required></textarea>
                </div>

                <div class="form-group">
                    <label for="photo"><i class="fas fa-camera"></i> Photo du plat (JPG, PNG)</label>
                    <input type="file" name="photo" id="photo" class="form-control" accept="image/*" required>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-plus-circle"></i> Ajouter à la carte
                </button>
            </form>
        </div>

    </main>

    <footer>
        <p>Bien Harr © 2026 - Interface de Gestion</p>
    </footer>

</body>
</html>