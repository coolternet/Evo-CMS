<?php
// Endpoint AJAX direct pour tester sans authentification
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Inclure le framework EvoCMS
require_once __DIR__ . '/../../includes/app.php';
App::init();

// Inclure les fonctions du module
require_once __DIR__ . '/pages_admin/core/functions.php';

// Définir le header JSON
header('Content-Type: application/json');

// Fonctions de retour
function admin_ajax_return_success($data) {
    die(json_encode(['success' => true] + (array)$data));
}

function admin_ajax_return_error($error) {
    die(json_encode(['success' => false, 'error' => $error]));
}

// Récupérer l'action
$action = $_POST['action'] ?? '';

// Gérer l'action send_answer_assigned
if ($action === 'send_answer_assigned') {
    $tid = $_POST['tid'] ?? 0;
    $msg = $_POST['msg'] ?? '';
    
    if (!$tid || !is_numeric($tid)) {
        admin_ajax_return_error("ID de ticket invalide");
    }
    
    if (empty($msg)) {
        admin_ajax_return_error("Le message ne peut pas être vide");
    }
    
    // Vérifier si la fonction existe
    if (!function_exists('send_answer_assigned_btn')) {
        admin_ajax_return_error("Fonction send_answer_assigned_btn non trouvée");
    }
    
    $result = send_answer_assigned_btn($tid, $msg);
    if ($result) {
        admin_ajax_return_success(['message' => 'Réponse envoyée avec succès']);
    } else {
        admin_ajax_return_error("Erreur lors de l'envoi de la réponse");
    }
}

// Récupérer les données des tickets pour le tableau
if ($action === 'get_tickets_data') {
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
if ($action === 'get_chart_data') {
    try {
        $chartData = get_ticket_chart_data();
        admin_ajax_return_success($chartData);
    } catch (Exception $e) {
        admin_ajax_return_error("Erreur lors de la récupération des données du graphique: " . $e->getMessage());
    }
}

// Marquer comme résolu
if ($action === 'mark_solved') {
    $ticket_id = $_POST['tid'] ?? 0;
    
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
if ($action === 'mark_unsolved') {
    $ticket_id = $_POST['tid'] ?? 0;
    
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
if ($action === 'mark_critical') {
    $ticket_id = $_POST['tid'] ?? 0;
    
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
if ($action === 'mark_normal') {
    $ticket_id = $_POST['tid'] ?? 0;
    
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

// Action non reconnue
admin_ajax_return_error("Action non reconnue: " . $action);
?>
