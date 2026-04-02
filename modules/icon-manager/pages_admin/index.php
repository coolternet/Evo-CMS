<?php
defined('EVO') or die('Que fais-tu là?');

has_permission('manage_icons', true);

// Redirection vers la page de paramètres
header('Location: ?p=icon-manager/settings');
exit;