<?php
/**
 * Configuration des optimisations Evo-TSM
 * 
 * Ce fichier contient tous les paramètres configurables
 * pour le système de cache et les optimisations.
 */

return [
    // Configuration du cache
    'cache' => [
        'ticket_counts_ttl' => 60,        // Durée de vie du cache des compteurs de tickets (secondes)
        'global_score_ttl' => 300,        // Durée de vie du cache des scores globaux (secondes)
        'contact_messages_ttl' => 300,    // Durée de vie du cache des messages de contact (secondes)
        'module_data_ttl' => 600,         // Durée de vie du cache des données de modules (secondes)
        'ticket_lists_ttl' => 120,        // Durée de vie du cache des listes de tickets (secondes)
        'default_ttl' => 300,             // Durée de vie par défaut (secondes)
    ],
    
    // Configuration des logs
    'logging' => [
        'debug_enabled' => true,          // Activer/désactiver les logs de debug
        'error_level' => 'warning',       // Niveau minimum pour les logs (error, warning, info, debug)
        'log_file' => 'evo_tsm.log',      // Nom du fichier de log
    ],
    
    // Configuration des performances
    'performance' => [
        'enable_caching' => true,         // Activer le système de cache
        'cache_auto_cleanup' => true,     // Nettoyage automatique du cache expiré
        'cache_cleanup_interval' => 3600, // Intervalle de nettoyage (secondes)
        'max_cache_entries' => 1000,      // Nombre maximum d'entrées en cache
    ],
    
    // Configuration de la base de données
    'database' => [
        'enable_indexes' => true,         // Activer la création d'index
        'query_timeout' => 30,            // Timeout des requêtes (secondes)
        'connection_pooling' => true,     // Utiliser le pool de connexions
    ],
    
    // Configuration de l'interface
    'ui' => [
        'show_cache_stats' => true,       // Afficher les statistiques de cache
        'cache_management_enabled' => true, // Activer la gestion du cache
        'auto_refresh_interval' => 30,    // Intervalle de rafraîchissement auto (secondes)
    ],
    
    // Configuration des notifications
    'notifications' => [
        'cache_cleared' => true,          // Notifier lors du vidage du cache
        'performance_alerts' => true,     // Alertes de performance
        'error_notifications' => true,    // Notifications d'erreur
    ],
    
    // Configuration des assets
    'assets' => [
        'enable_minification' => true,    // Activer la minification des assets
        'enable_compression' => true,     // Activer la compression
        'cache_assets' => true,           // Mettre en cache les assets
        'version_assets' => true,         // Ajouter des versions aux assets
    ],
    
    // Configuration de la validation
    'validation' => [
        'enable_xss_protection' => true,  // Protection XSS
        'max_message_length' => 5000,     // Longueur maximale des messages
        'min_message_length' => 3,        // Longueur minimale des messages
        'max_subject_length' => 200,      // Longueur maximale des sujets
    ]
];