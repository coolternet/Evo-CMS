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

if ($action === 'optimize_assets') {
    require_once __DIR__ . '/assets/optimize.php';
    $results = optimize_all_assets($assets_config);
    
    if (count(array_filter($results, function($r) { return strpos($r, 'Erreur') === false; })) > 0) {
        $message = 'Assets optimisés avec succès';
        $message_type = 'success';
    } else {
        $message = 'Erreur lors de l\'optimisation des assets';
        $message_type = 'error';
    }
}

if ($action === 'clear_all_cache') {
    evo_tsm_cache_clear();
    $message = 'Tout le cache a été vidé avec succès';
    $message_type = 'success';
}

// Obtenir les statistiques de performance
$performance_stats = [
    'cache_hits' => 0,
    'cache_misses' => 0,
    'db_queries' => 0,
    'execution_time' => 0,
    'memory_usage' => 0,
    'optimization_score' => 0
];

// Mesurer les performances
$start_time = microtime(true);
$start_memory = memory_get_usage();

// Test des fonctions optimisées
$ticket_counts = get_ticket_counts_optimized();
$contact_count = get_contact_message_count();

$end_time = microtime(true);
$end_memory = memory_get_usage();

$performance_stats['execution_time'] = round(($end_time - $start_time) * 1000, 2);
$performance_stats['memory_usage'] = round(($end_memory - $start_memory) / 1024, 2);

// Calculer le score d'optimisation
$execution_score = max(0, 100 - ($performance_stats['execution_time'] / 10));
$memory_score = max(0, 100 - ($performance_stats['memory_usage'] / 100));
$performance_stats['optimization_score'] = round(($execution_score + $memory_score) / 2, 1);

// Inclure le template
include __DIR__ . '/templates/header_empty.php';
include __DIR__ . '/pages/performance.php';
