<?php
// Vérifier si on est en mode admin
if (!defined('EVO_ADMIN')) {
    die('Accès non autorisé');
}

// Inclure les fonctions du module
require_once __DIR__ . '/core/functions.php';

// Traitement de l'installation
$action = $_GET['action'] ?? '';
$message = '';
$message_type = '';
$results = [];

if ($action === 'install') {
    try {
        // 1. Créer la table cache si elle n'existe pas
        if (!\DB::TableExists('tss_cache')) {
            \DB::CreateTable('tss_cache', [
                'cache_key' => ['varchar(255)', ''],
                'data' => ['text', ''],
                'expires' => ['integer', 0]
            ], false, true);
            $results[] = 'Table cache créée avec succès';
        } else {
            $results[] = 'Table cache existe déjà';
        }

        // 2. Créer les index pour optimiser les performances
        $indexes = [
            'CREATE UNIQUE INDEX IF NOT EXISTS cache_key_idx ON {cache} (cache_key)',
            'CREATE INDEX IF NOT EXISTS idx_cache_expires ON {cache} (expires)',
            'CREATE INDEX IF NOT EXISTS idx_tss_ticket_level ON {tss_ticket} (level)',
            'CREATE INDEX IF NOT EXISTS idx_tss_ticket_assignation ON {tss_ticket} (assignation)',
            'CREATE INDEX IF NOT EXISTS idx_tss_ticket_close_date ON {tss_ticket} (close_date)',
            'CREATE INDEX IF NOT EXISTS idx_tss_rates_tid ON {tss_rates} (tid)'
        ];

        $indexes_created = 0;
        foreach ($indexes as $index_sql) {
            try {
                \DB::Query($index_sql);
                $indexes_created++;
            } catch (Exception $e) {
                // Ignorer les erreurs d'index existants
            }
        }
        $results[] = "$indexes_created index créés/optimisés";

        // 3. Tester le système de cache
        $test_key = 'evo_tsm_test_' . time();
        $test_data = ['test' => 'data', 'timestamp' => time()];
        
        if (evo_tsm_cache_set($test_key, $test_data, 60)) {
            $cached_data = evo_tsm_cache_get($test_key);
            if ($cached_data && $cached_data['test'] === 'data') {
                $results[] = 'Système de cache fonctionnel';
                evo_tsm_cache_clear($test_key); // Nettoyer le test
            } else {
                $results[] = 'Cache en mode fallback (statique)';
            }
        } else {
            $results[] = 'Cache en mode fallback (statique)';
        }

        // 4. Test de performance
        $start_time = microtime(true);
        
        // Test des fonctions optimisées
        $ticket_counts = get_ticket_counts_optimized();
        $contact_count = get_contact_message_count();
        
        $end_time = microtime(true);
        $execution_time = round(($end_time - $start_time) * 1000, 2);
        
        $results[] = "Performance test: {$execution_time}ms";

        // 5. Créer le fichier de configuration
        $config_dir = __DIR__ . '/../config';
        if (!is_dir($config_dir)) {
            mkdir($config_dir, 0755, true);
        }

        $config_file = $config_dir . '/optimizations.php';
        if (!file_exists($config_file)) {
            $config_content = '<?php
return [
    "cache" => [
        "ticket_counts_ttl" => 60, // seconds
        "global_score_ttl" => 300, // seconds
        "contact_messages_ttl" => 300, // seconds
    ],
    "logging" => [
        "debug_enabled" => true,
        "error_level" => "warning",
    ],
    "performance" => [
        "enable_caching" => true,
        "cache_auto_cleanup" => true,
    ]
];';
            file_put_contents($config_file, $config_content);
            $results[] = 'Fichier de configuration créé';
        } else {
            $results[] = 'Fichier de configuration existe déjà';
        }

        $message = 'Installation du système de cache terminée avec succès !';
        $message_type = 'success';

    } catch (Exception $e) {
        $message = 'Erreur lors de l\'installation : ' . $e->getMessage();
        $message_type = 'error';
        $results[] = 'Erreur: ' . $e->getMessage();
    }
}

// Inclure le template
include __DIR__ . '/templates/header_empty.php';
include __DIR__ . '/pages/install.php';
