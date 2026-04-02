<?php
/**
 * Script de diagnostic de la base de données
 * À exécuter pour identifier les problèmes de structure
 */

// Inclure EvoCMS
require_once '../../includes/app.php';

echo "<h1>🔍 Diagnostic de la Base de Données</h1>";

try {
    // 1. Vérifier l'état des tables du module
    echo "<h2>1. Tables du Module de Statistiques</h2>";
    $module_tables = ['statistics', 'page_statistics', 'statistics_visits'];
    
    foreach ($module_tables as $table) {
        $exists = Db::TableExists($table);
        $status = $exists ? "✅ Existe" : "❌ Manquante";
        echo "<p><strong>{$table}:</strong> {$status}</p>";
        
        if ($exists) {
            try {
                $columns = Db::QueryAll("PRAGMA table_info({$table})");
                echo "<ul>";
                foreach ($columns as $column) {
                    echo "<li>{$column['name']} - {$column['type']}</li>";
                }
                echo "</ul>";
            } catch (Exception $e) {
                echo "<p style='color: red;'>Erreur lors de la lecture: " . $e->getMessage() . "</p>";
            }
        }
    }

    // 2. Vérifier la table pages d'EvoCMS
    echo "<h2>2. Table Pages d'EvoCMS</h2>";
    if (Db::TableExists('pages')) {
        echo "<p>✅ Table 'pages' existe</p>";
        
        try {
            $columns = Db::QueryAll("PRAGMA table_info(pages)");
            echo "<h3>Structure de la table 'pages':</h3>";
            echo "<ul>";
            foreach ($columns as $column) {
                echo "<li><strong>{$column['name']}</strong> - {$column['type']}</li>";
            }
            echo "</ul>";
            
            // Vérifier s'il y a des données
            $count = Db::Get('SELECT COUNT(*) as count FROM {pages}')['count'];
            echo "<p><strong>Nombre de pages:</strong> {$count}</p>";
            
            // Afficher quelques exemples
            if ($count > 0) {
                $sample_pages = Db::QueryAll('SELECT * FROM {pages} LIMIT 3');
                echo "<h3>Exemples de pages:</h3>";
                echo "<pre>" . print_r($sample_pages, true) . "</pre>";
            }
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>Erreur lors de la lecture de 'pages': " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>❌ Table 'pages' n'existe pas</p>";
    }

    // 3. Vérifier les autres tables importantes
    echo "<h2>3. Autres Tables Importantes</h2>";
    $important_tables = ['users', 'groups', 'comments', 'forums'];
    
    foreach ($important_tables as $table) {
        if (Db::TableExists($table)) {
            echo "<p>✅ Table '{$table}' existe</p>";
        } else {
            echo "<p>❌ Table '{$table}' manquante</p>";
        }
    }

    // 4. Test de la méthode getPageTitle
    echo "<h2>4. Test de getPageTitle</h2>";
    
    // Simuler l'appel de la méthode
    $test_urls = [
        '/admin/?page=modules',
        '/admin/?page=users',
        '/admin/?page=settings',
        '/forums',
        '/gallery'
    ];
    
    foreach ($test_urls as $url) {
        echo "<p><strong>URL:</strong> {$url}</p>";
        
        // Extraire le slug de la page
        if (strpos($url, '?p=') !== false) {
            $page_slug = str_replace('?p=', '', $url);
            echo "<p>Slug extrait: {$page_slug}</p>";
        } elseif (strpos($url, '?page=') !== false) {
            $page_slug = str_replace('?page=', '', $url);
            echo "<p>Page extraite: {$page_slug}</p>";
        } else {
            $page_slug = $url;
            echo "<p>URL directe: {$page_slug}</p>";
        }
        
        // Essayer de récupérer le titre
        if (Db::TableExists('pages')) {
            try {
                $columns = Db::QueryAll("PRAGMA table_info(pages)");
                $column_names = array_column($columns, 'name');
                
                if (in_array('title', $column_names)) {
                    $page = Db::Get('SELECT title FROM {pages} WHERE slug = ?', $page_slug);
                    if ($page) {
                        echo "<p>✅ Titre trouvé: {$page['title']}</p>";
                    } else {
                        echo "<p>⚠️ Aucune page trouvée avec ce slug</p>";
                    }
                } elseif (in_array('name', $column_names)) {
                    $page = Db::Get('SELECT name FROM {pages} WHERE slug = ?', $page_slug);
                    if ($page) {
                        echo "<p>✅ Nom trouvé: {$page['name']}</p>";
                    } else {
                        echo "<p>⚠️ Aucune page trouvée avec ce slug</p>";
                    }
                } else {
                    echo "<p>⚠️ Aucune colonne de titre trouvée</p>";
                }
            } catch (Exception $e) {
                echo "<p style='color: red;'>Erreur: " . $e->getMessage() . "</p>";
            }
        }
        
        echo "<hr>";
    }

    // 5. Recommandations
    echo "<h2>🎯 Recommandations</h2>";
    
    if (!Db::TableExists('statistics') || !Db::TableExists('page_statistics') || !Db::TableExists('statistics_visits')) {
        echo "<p style='color: orange;'>⚠️ Certaines tables du module sont manquantes.</p>";
        echo "<p>Activez le module de statistiques pour créer ces tables.</p>";
    } else {
        echo "<p style='color: green;'>✅ Toutes les tables du module existent.</p>";
    }
    
    if (!Db::TableExists('pages')) {
        echo "<p style='color: orange;'>⚠️ La table 'pages' n'existe pas.</p>";
        echo "<p>Le module utilisera les titres par défaut pour les pages.</p>";
    } else {
        echo "<p style='color: green;'>✅ La table 'pages' existe.</p>";
    }
    
    echo "<p><a href='../../admin/?page=modules'>← Retour aux modules</a></p>";

} catch (Exception $e) {
    echo "<h2>❌ Erreur lors du Diagnostic</h2>";
    echo "<p><strong>Erreur:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Fichier:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Ligne:</strong> " . htmlspecialchars($e->getLine()) . "</p>";
    echo "<p><a href='../../admin/?page=modules'>← Retour aux modules</a></p>";
}
?>
