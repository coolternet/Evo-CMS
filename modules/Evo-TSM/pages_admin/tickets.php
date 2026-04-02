<?php
// Interface complète pour la liste des tickets
$state = $_GET['state'] ?? 'opened';

// Charger les fonctions du module
require_once __DIR__ . '/core/functions.php';

if (!\DB::TableExists('tss_ticket')) {
    die("Erreur: Table tss_ticket non trouvée");
}

switch ($state) {
    case "opened":
        // Utiliser la fonction optimisée pour récupérer les tickets
        $get = get_tickets_optimized(TICKET_OPEN);
        $type = "open";
        
        // Utiliser le template complet main.php
        include __DIR__.'/templates/main.php';
        include 'pages/tickets.php';
        break;
        
    case "closed":
        $get = get_tickets_optimized(TICKET_CLOSE);
        $type = "close";
        include __DIR__.'/templates/main.php';
        include 'pages/tickets.php';
        break;
        
    case "critical":
        $get = get_tickets_optimized(TICKET_CRITICAL);
        $type = "critical";
        include __DIR__.'/templates/main.php';
        include 'pages/tickets.php';
        break;
        
    case "unassigned":
        $get = get_tickets_optimized(TICKET_UNASSIGNED);
        $type = "unassigned";
        include __DIR__.'/templates/main.php';
        include 'pages/tickets.php';
        break;
        
    default:
        echo "État de ticket non reconnu: " . htmlspecialchars($state);
        break;
}
?>