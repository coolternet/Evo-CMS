<?php
/**
 * Evo-TSM Admin AJAX Handler
 * Point d'entrée pour les actions AJAX dans l'interface d'administration
 */

// Inclure les fonctions du module
require_once __DIR__ . '/core/functions.php';

function admin_ajax_return_success($data) {
    die(json_encode(['success' => true] + (array)$data));
}

function admin_ajax_return_error($error) {
    die(json_encode(['success' => false, 'error' => $error]));
}

// Récupérer les données des tickets pour le tableau
if (($_POST['action'] ?? '') === 'get_tickets_data') {
    $state = $_POST['state'] ?? 'open';
    
    try {
        // Récupérer les tickets selon l'état
        $tickets = get_tickets_by_state($state);
        $count = count($tickets);
        
        admin_ajax_return_success([
            'tickets' => $tickets,
            'count' => $count
        ]);
    } catch (Exception $e) {
        admin_ajax_return_error("Erreur lors de la récupération des tickets: " . $e->getMessage());
    }
}

// Récupérer les données pour le graphique
if (($_POST['action'] ?? '') === 'get_chart_data') {
    try {
        $chartData = get_ticket_chart_data();
        admin_ajax_return_success($chartData);
    } catch (Exception $e) {
        admin_ajax_return_error("Erreur lors de la récupération des données du graphique: " . $e->getMessage());
    }
}

// Fermer un ticket
if (($_POST['action'] ?? '') === 'close_ticket_btn') {
    $ticket_id = $_POST['ticket_id'] ?? 0;
    
    if (!$ticket_id || !is_numeric($ticket_id)) {
        admin_ajax_return_error("ID de ticket invalide");
    }
    
    $result = close_ticket($ticket_id);
    if ($result === 'success') {
        admin_ajax_return_success(['message' => 'Ticket fermé avec succès']);
    } else {
        admin_ajax_return_error("Erreur lors de la fermeture du ticket");
    }
}

// Supprimer un ticket
if (($_POST['action'] ?? '') === 'delete_ticket_btn') {
    $ticket_id = $_POST['ticket_id'] ?? 0;
    
    if (!$ticket_id || !is_numeric($ticket_id)) {
        admin_ajax_return_error("ID de ticket invalide");
    }
    
    $result = delete_ticket($ticket_id);
    if ($result === 'success') {
        admin_ajax_return_success(['message' => 'Ticket supprimé avec succès']);
    } else {
        admin_ajax_return_error("Erreur lors de la suppression du ticket");
    }
}

// Marquer comme résolu
if (($_POST['action'] ?? '') === 'mark_solved_btn') {
    $ticket_id = $_POST['ticket_id'] ?? 0;
    
    if (!$ticket_id || !is_numeric($ticket_id)) {
        admin_ajax_return_error("ID de ticket invalide");
    }
    
    $result = mark_solved($ticket_id);
    if ($result === 'success') {
        admin_ajax_return_success(['message' => 'Ticket marqué comme résolu']);
    } else {
        admin_ajax_return_error("Erreur lors du marquage du ticket");
    }
}

// Marquer comme non résolu
if (($_POST['action'] ?? '') === 'mark_unsolved_btn') {
    $ticket_id = $_POST['ticket_id'] ?? 0;
    
    if (!$ticket_id || !is_numeric($ticket_id)) {
        admin_ajax_return_error("ID de ticket invalide");
    }
    
    $result = mark_unsolved($ticket_id);
    if ($result === 'success') {
        admin_ajax_return_success(['message' => 'Ticket marqué comme non résolu']);
    } else {
        admin_ajax_return_error("Erreur lors du marquage du ticket");
    }
}

// Marquer comme critique
if (($_POST['action'] ?? '') === 'mark_critical_btn') {
    $ticket_id = $_POST['ticket_id'] ?? 0;
    
    if (!$ticket_id || !is_numeric($ticket_id)) {
        admin_ajax_return_error("ID de ticket invalide");
    }
    
    $result = mark_critical($ticket_id);
    if ($result === 'success') {
        admin_ajax_return_success(['message' => 'Ticket marqué comme critique']);
    } else {
        admin_ajax_return_error("Erreur lors du marquage du ticket");
    }
}

// Marquer comme normal
if (($_POST['action'] ?? '') === 'mark_normal_btn') {
    $ticket_id = $_POST['ticket_id'] ?? 0;
    
    if (!$ticket_id || !is_numeric($ticket_id)) {
        admin_ajax_return_error("ID de ticket invalide");
    }
    
    $result = mark_normal($ticket_id);
    if ($result === 'success') {
        admin_ajax_return_success(['message' => 'Ticket marqué comme normal']);
    } else {
        admin_ajax_return_error("Erreur lors du marquage du ticket");
    }
}

// Rouvrir un ticket
if (($_POST['action'] ?? '') === 'reopen_ticket_btn') {
    $ticket_id = $_POST['ticket_id'] ?? 0;
    
    if (!$ticket_id || !is_numeric($ticket_id)) {
        admin_ajax_return_error("ID de ticket invalide");
    }
    
    $result = reopen_ticket($ticket_id);
    if ($result === 'success') {
        admin_ajax_return_success(['message' => 'Ticket rouvert avec succès']);
    } else {
        admin_ajax_return_error("Erreur lors de la réouverture du ticket");
    }
}

// Action non reconnue
$action = $_POST['action'] ?? 'non définie';
admin_ajax_return_error("Action non reconnue: " . $action);