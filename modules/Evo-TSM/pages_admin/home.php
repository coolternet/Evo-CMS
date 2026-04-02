<?php
/**
 * Evo-TSM Dashboard - Page d'accueil
 * Point d'entrée principal pour l'interface d'administration
 */

// Charger les fonctions du module
require_once __DIR__ . '/core/functions.php';

// Récupération des données optimisées
$score = ticket_get_scored();
$ticket_counts = get_ticket_counts_optimized();
$get_open = get_tickets_optimized(TICKET_OPEN, 0, 5);

// Configuration des données du cache pour JavaScript
$cache_installed = \DB::TableExists('tss_cache');
$cache_entries = 0;

if ($cache_installed) {
    $cache_result = \DB::Get("SELECT COUNT(*) as count FROM {tss_cache} WHERE cache_key LIKE 'evo_tsm_%'");
    $cache_entries = $cache_result['count'] ?? 0;
}

$cache_data = [
    'status' => $cache_installed ? 'active' : 'inactive',
    'message' => $cache_installed ? 'Actif' : 'Non installé',
    'icon' => $cache_installed ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle',
    'color' => $cache_installed ? 'success' : 'warning',
    'entries' => $cache_entries
];

// Inclusion des templates
include __DIR__ . '/templates/header_empty.php';
include __DIR__ . '/pages/home.php';