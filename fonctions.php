<?php
function ajouterLog($user_id, $role, $action, $details, $niveau = "INFO") {
    $fichier_logs = 'json/logs.json';
    
    // 1. On récupère les logs existants
    $logs = [];
    if (file_exists($fichier_logs)) {
        $contenu = file_get_contents($fichier_logs);
        $logs = json_decode($contenu, true) ?? [];
    }

    // 2. On crée le nouveau log
    $nouveau_log = [
        "date" => date("Y-m-d H:i:s"),
        "user_id" => $user_id,
        "role" => $role,
        "action" => $action,
        "details" => $details,
        "niveau" => $niveau
    ];

    // 3. On l'ajoute au début du tableau (pour avoir les plus récents en premier)
    array_unshift($logs, $nouveau_log);

    // 4. On sauvegarde le fichier
    file_put_contents($fichier_logs, json_encode($logs, JSON_PRETTY_PRINT));
}
?>