<?php 

// Charger les fonctions du module
require_once __DIR__ . '/core/functions.php';

if ($view = App::GET('id')) {
    $info = ticket_get_information($view);
    $content = ticket_get_content($view);
}

if (!empty($info)) {
    $title = $info['subject'];
    include  __DIR__.'/templates/view.php';
    include 'pages/view.php';
} else {
    header("location: /admin/?p=Evo-TSM/home");
}