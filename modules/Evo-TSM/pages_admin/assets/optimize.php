<?php
/**
 * Système d'optimisation des assets Evo-TSM
 * 
 * Ce fichier gère la compression, la minification et la mise en cache
 * des fichiers CSS et JavaScript du module.
 */

// Vérifier si on est en mode admin
if (!defined('EVO_ADMIN')) {
    die('Accès non autorisé');
}

// Configuration des assets
$assets_config = [
    'css' => [
        'styles.css' => [
            'path' => __DIR__ . '/css/styles.css',
            'minified' => __DIR__ . '/css/styles.min.css',
            'cache_time' => 3600 // 1 heure
        ],
        'evo-tsm.css' => [
            'path' => __DIR__ . '/css/evo-tsm.css',
            'minified' => __DIR__ . '/css/evo-tsm.min.css',
            'cache_time' => 3600
        ]
    ],
    'js' => [
        'evo-tsm.js' => [
            'path' => __DIR__ . '/js/evo-tsm.js',
            'minified' => __DIR__ . '/js/evo-tsm.min.js',
            'cache_time' => 3600
        ],
        'ajax.js' => [
            'path' => __DIR__ . '/js/ajax.js',
            'minified' => __DIR__ . '/js/ajax.min.js',
            'cache_time' => 3600
        ]
    ]
];

/**
 * Minifier le CSS
 */
function minify_css($css) {
    // Supprimer les commentaires
    $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
    
    // Supprimer les espaces inutiles
    $css = str_replace(["\r\n", "\r", "\n", "\t", '  ', '    ', '    '], '', $css);
    
    // Supprimer les espaces autour des opérateurs
    $css = preg_replace('/\s*{\s*/', '{', $css);
    $css = preg_replace('/;\s*/', ';', $css);
    $css = preg_replace('/\s*}\s*/', '}', $css);
    $css = preg_replace('/,\s*/', ',', $css);
    
    return trim($css);
}

/**
 * Minifier le JavaScript
 */
function minify_js($js) {
    // Supprimer les commentaires de ligne
    $js = preg_replace('/\/\/.*$/m', '', $js);
    
    // Supprimer les commentaires de bloc
    $js = preg_replace('/\/\*.*?\*\//s', '', $js);
    
    // Supprimer les espaces inutiles
    $js = preg_replace('/\s+/', ' ', $js);
    
    // Supprimer les espaces autour des opérateurs
    $js = preg_replace('/\s*([{}();,=+\-*\/])\s*/', '$1', $js);
    
    return trim($js);
}

/**
 * Optimiser un fichier CSS
 */
function optimize_css_file($file_path, $minified_path) {
    if (!file_exists($file_path)) {
        return false;
    }
    
    $css_content = file_get_contents($file_path);
    $minified_content = minify_css($css_content);
    
    // Créer le répertoire si nécessaire
    $dir = dirname($minified_path);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    return file_put_contents($minified_path, $minified_content) !== false;
}

/**
 * Optimiser un fichier JavaScript
 */
function optimize_js_file($file_path, $minified_path) {
    if (!file_exists($file_path)) {
        return false;
    }
    
    $js_content = file_get_contents($file_path);
    $minified_content = minify_js($js_content);
    
    // Créer le répertoire si nécessaire
    $dir = dirname($minified_path);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    return file_put_contents($minified_path, $minified_content) !== false;
}

/**
 * Vérifier si un fichier minifié est à jour
 */
function is_minified_up_to_date($original_path, $minified_path, $cache_time) {
    if (!file_exists($minified_path) || !file_exists($original_path)) {
        return false;
    }
    
    $original_time = filemtime($original_path);
    $minified_time = filemtime($minified_path);
    
    return ($minified_time > $original_time) && ((time() - $minified_time) < $cache_time);
}

/**
 * Optimiser tous les assets
 */
function optimize_all_assets($assets_config) {
    $results = [];
    
    // Optimiser les fichiers CSS
    foreach ($assets_config['css'] as $name => $config) {
        if (is_minified_up_to_date($config['path'], $config['minified'], $config['cache_time'])) {
            $results[$name] = 'Déjà à jour';
            continue;
        }
        
        if (optimize_css_file($config['path'], $config['minified'])) {
            $results[$name] = 'Optimisé avec succès';
        } else {
            $results[$name] = 'Erreur lors de l\'optimisation';
        }
    }
    
    // Optimiser les fichiers JavaScript
    foreach ($assets_config['js'] as $name => $config) {
        if (is_minified_up_to_date($config['path'], $config['minified'], $config['cache_time'])) {
            $results[$name] = 'Déjà à jour';
            continue;
        }
        
        if (optimize_js_file($config['path'], $config['minified'])) {
            $results[$name] = 'Optimisé avec succès';
        } else {
            $results[$name] = 'Erreur lors de l\'optimisation';
        }
    }
    
    return $results;
}

/**
 * Générer le HTML optimisé pour les assets
 */
function generate_optimized_assets_html($assets_config, $use_minified = true) {
    $html = '';
    
    // CSS
    foreach ($assets_config['css'] as $name => $config) {
        $file_path = $use_minified && file_exists($config['minified']) ? 
            $config['minified'] : $config['path'];
        
        $html .= '<link rel="stylesheet" href="' . $file_path . '?v=' . filemtime($file_path) . '">' . "\n";
    }
    
    // JavaScript
    foreach ($assets_config['js'] as $name => $config) {
        $file_path = $use_minified && file_exists($config['minified']) ? 
            $config['minified'] : $config['path'];
        
        $html .= '<script src="' . $file_path . '?v=' . filemtime($file_path) . '"></script>' . "\n";
    }
    
    return $html;
}

// Si appelé directement, optimiser tous les assets
if (basename($_SERVER['PHP_SELF']) === 'optimize.php') {
    $results = optimize_all_assets($assets_config);
    
    header('Content-Type: application/json');
    echo json_encode($results);
    exit;
}
