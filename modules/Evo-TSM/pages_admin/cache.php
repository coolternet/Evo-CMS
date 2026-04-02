<?php
// Vérifier si on est en mode admin
if (!defined('EVO_ADMIN')) {
    die('Accès non autorisé');
}

// Inclure les fonctions du module
require_once __DIR__ . '/core/functions.php';

// Traitement des actions
$action = $_GET['action'] ?? '';
$message = '';
$message_type = '';

if ($action === 'clear_cache') {
    $cache_type = $_GET['type'] ?? 'all';
    
    switch ($cache_type) {
        case 'all':
            evo_tsm_cache_clear();
            $message = 'Tout le cache a été vidé avec succès.';
            $message_type = 'success';
            break;
        case 'tickets':
            evo_tsm_cache_clear('ticket_');
            $message = 'Le cache des tickets a été vidé avec succès.';
            $message_type = 'success';
            break;
        case 'scores':
            evo_tsm_cache_clear('score_');
            $message = 'Le cache des scores a été vidé avec succès.';
            $message_type = 'success';
            break;
        case 'contact':
            evo_tsm_cache_clear('contact_');
            $message = 'Le cache des messages de contact a été vidé avec succès.';
            $message_type = 'success';
            break;
        default:
            $message = 'Type de cache invalide.';
            $message_type = 'error';
    }
}

// Obtenir les statistiques du cache
$cache_stats = [
    'total_entries' => 0,
    'ticket_entries' => 0,
    'score_entries' => 0,
    'contact_entries' => 0,
    'cache_size' => '0 KB'
];

// Compter les entrées dans le cache
try {
    $cache_entries = \DB::QueryAll("SELECT cache_key, data FROM {tss_cache} WHERE cache_key LIKE 'evo_tsm_%'");
    $cache_stats['total_entries'] = count($cache_entries);
    
    foreach ($cache_entries as $entry) {
        if (strpos($entry['cache_key'], 'ticket_') !== false) {
            $cache_stats['ticket_entries']++;
        } elseif (strpos($entry['cache_key'], 'score_') !== false) {
            $cache_stats['score_entries']++;
        } elseif (strpos($entry['cache_key'], 'contact_') !== false) {
            $cache_stats['contact_entries']++;
        }
    }
    
    // Calculer la taille du cache
    $total_size = 0;
    foreach ($cache_entries as $entry) {
        $total_size += strlen($entry['data']);
    }
    $cache_stats['cache_size'] = format_bytes($total_size);
    
} catch (Exception $e) {
    $cache_stats['error'] = 'Erreur lors de la lecture du cache : ' . $e->getMessage();
}

// Fonction pour formater les tailles
function format_bytes($size, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    return round($size, $precision) . ' ' . $units[$i];
}

// Inclure le template
include __DIR__ . '/templates/header_empty.php';
include __DIR__ . '/pages/cache.php';