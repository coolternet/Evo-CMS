<?php

// Inclure les fonctions du module
require_once __DIR__ . '/core/functions.php';

function eshop_ajax_return_success($data) {
	die(json_encode(['success' => true] + (array)$data));
}

function eshop_ajax_return_error($error) {
	die(json_encode(['success' => false, 'error' => $error]));
}

// Create a new ticket
if (($_POST['action'] ?? '') === 'create_new_ticket_btn') {
	$subject = $_POST['subject'] ?? '';
	$content = $_POST['content'] ?? '';
	
	$user = APP::getCurrentUser();
	$tableExists = \DB::TableExists('tss_ticket');
	
	// Validation côté serveur
	if (empty($subject) || strlen(trim($subject)) < 3) {
		eshop_ajax_return_error("Le sujet doit contenir au moins 3 caractères");
	}
	if (empty($content) || strlen(trim($content)) < 10) {
		eshop_ajax_return_error("La description doit contenir au moins 10 caractères");
	}
	
	// Vérifier que l'utilisateur est connecté
	if (!$user || !$user->id) {
		eshop_ajax_return_error("Vous devez être connecté pour créer un ticket");
	}
	
	// Vérifier que la table existe
	if (!$tableExists) {
		eshop_ajax_return_error("Le module de tickets n'est pas activé. Contactez l'administrateur.");
	}
	
	$action = create_new_ticket($subject, $content);
	
	// Si on a un ID (même 0), on considère que c'est un succès
	if ($action !== false) {
		eshop_ajax_return_success(['id' => $action]);
	} else {
		eshop_ajax_return_error("Erreur lors de la création du ticket.");
	}
}

// Send answer to a ticket
if (($_POST['action'] ?? '') === 'send_answer_btn') {
	$action = send_answer($_POST['ticket_id'], $_POST['comment']);
	if ($action) {
		eshop_ajax_return_success($action);
	} else {
		eshop_ajax_return_error("Not found");
	}
}

// Close a ticket
if (($_POST['action'] ?? '') === 'close_ticket_btn') {
	$action = close_ticket($_POST['ticket_id']);
	if ($action) {
		eshop_ajax_return_success($action);
	} else {
		eshop_ajax_return_error("Not found");
	}
}

// Delete a ticket
if (($_POST['action'] ?? '') === 'delete_ticket_btn') {
	$action = delete_ticket($_POST['ticket_id']);
	if ($action) {
		eshop_ajax_return_success($action);
	} else {
		eshop_ajax_return_error("Not found");
	}
}

// Send answer assigned (admin action)
if (($_POST['action'] ?? '') === 'send_answer_assigned') {
	$action = send_answer_assigned_btn($_POST['tid'], $_POST['msg']);
	if ($action) {
		eshop_ajax_return_success($action);
	} else {
		eshop_ajax_return_error("Not found");
	}
}

// Mark as solved
if (($_POST['action'] ?? '') === 'mark_solved') {
	$action = mark_solved($_POST['tid']);
	if ($action) {
		eshop_ajax_return_success($action);
	} else {
		eshop_ajax_return_error("Not found");
	}
}

// Mark as unsolved
if (($_POST['action'] ?? '') === 'mark_unsolved') {
	$action = mark_unsolved($_POST['tid']);
	if ($action) {
		eshop_ajax_return_success($action);
	} else {
		eshop_ajax_return_error("Not found");
	}
}

// Mark as critical
if (($_POST['action'] ?? '') === 'mark_critical') {
	$action = mark_critical($_POST['tid']);
	if ($action) {
		eshop_ajax_return_success($action);
	} else {
		eshop_ajax_return_error("Not found");
	}
}

// Mark as normal
if (($_POST['action'] ?? '') === 'mark_normal') {
	$action = mark_normal($_POST['tid']);
	if ($action) {
		eshop_ajax_return_success($action);
	} else {
		eshop_ajax_return_error("Not found");
	}
}

// Delete ticket (admin action)
if (($_POST['action'] ?? '') === 'delete_ticket') {
	$action = delete_ticket($_POST['tid']);
	if ($action) {
		eshop_ajax_return_success($action);
	} else {
		eshop_ajax_return_error("Not found");
	}
}

// Solved ticket
if (($_POST['action'] ?? '') === 'solved_ticket') {
	$action = mark_solved($_POST['tid']);
	if ($action) {
		eshop_ajax_return_success($action);
	} else {
		eshop_ajax_return_error("Not found");
	}
}

// Admin create ticket
if (($_POST['action'] ?? '') === 'admin_create_ticket') {
	$action = admin_create_ticket($_POST['uid'], $_POST['mid'], $_POST['lid'], $_POST['sujet'], $_POST['desc']);
	if ($action) {
		eshop_ajax_return_success($action);
	} else {
		eshop_ajax_return_error("Not found");
	}
}

// Admin change assignation
if (($_POST['action'] ?? '') === 'admin_change_assignation') {
	$action = admin_change_assignation($_POST['tid'], $_POST['mid']);
	if ($action) {
		eshop_ajax_return_success($action);
	} else {
		eshop_ajax_return_error("Not found");
	}
}

// Reopen ticket (admin action) - Rediriger vers l'endpoint admin
if (($_POST['action'] ?? '') === 'reopen_ticket_btn') {
	eshop_ajax_return_error("Cette action doit être effectuée depuis l'interface d'administration");
}

// Mark solved (admin action) - Rediriger vers l'endpoint admin
if (($_POST['action'] ?? '') === 'mark_solved_btn') {
	eshop_ajax_return_error("Cette action doit être effectuée depuis l'interface d'administration");
}

// Mark unsolved (admin action) - Rediriger vers l'endpoint admin
if (($_POST['action'] ?? '') === 'mark_unsolved_btn') {
	eshop_ajax_return_error("Cette action doit être effectuée depuis l'interface d'administration");
}

// Mark critical (admin action) - Rediriger vers l'endpoint admin
if (($_POST['action'] ?? '') === 'mark_critical_btn') {
	eshop_ajax_return_error("Cette action doit être effectuée depuis l'interface d'administration");
}

// Mark normal (admin action) - Rediriger vers l'endpoint admin
if (($_POST['action'] ?? '') === 'mark_normal_btn') {
	eshop_ajax_return_error("Cette action doit être effectuée depuis l'interface d'administration");
}

// Actions supplémentaires pour compatibilité
if (($_POST['action'] ?? '') === 'ticket_answer_btn') {
	eshop_ajax_return_error("Cette action doit être effectuée depuis l'interface d'administration");
}

if (($_POST['action'] ?? '') === 'ticket_close_btn') {
	eshop_ajax_return_error("Cette action doit être effectuée depuis l'interface d'administration");
}

if (($_POST['action'] ?? '') === 'ticket_delete_btn') {
	eshop_ajax_return_error("Cette action doit être effectuée depuis l'interface d'administration");
}

if (($_POST['action'] ?? '') === 'ticket_solve_btn') {
	eshop_ajax_return_error("Cette action doit être effectuée depuis l'interface d'administration");
}

if (($_POST['action'] ?? '') === 'ticket_unsolve_btn') {
	eshop_ajax_return_error("Cette action doit être effectuée depuis l'interface d'administration");
}

if (($_POST['action'] ?? '') === 'ticket_critical_btn') {
	eshop_ajax_return_error("Cette action doit être effectuée depuis l'interface d'administration");
}

if (($_POST['action'] ?? '') === 'ticket_normal_btn') {
	eshop_ajax_return_error("Cette action doit être effectuée depuis l'interface d'administration");
}

// Actions admin - gérer directement ici
if (($_POST['action'] ?? '') === 'close_ticket_btn') {
	$ticket_id = $_POST['ticket_id'] ?? 0;
	
	if (!$ticket_id || !is_numeric($ticket_id)) {
		eshop_ajax_return_error("ID de ticket invalide");
	}
	
	$result = close_ticket($ticket_id);
	if ($result === 'success') {
		eshop_ajax_return_success(['message' => 'Ticket fermé avec succès']);
	} else {
		eshop_ajax_return_error("Erreur lors de la fermeture du ticket");
	}
}

if (($_POST['action'] ?? '') === 'delete_ticket_btn') {
	$ticket_id = $_POST['ticket_id'] ?? 0;
	
	if (!$ticket_id || !is_numeric($ticket_id)) {
		eshop_ajax_return_error("ID de ticket invalide");
	}
	
	$result = delete_ticket($ticket_id);
	if ($result === 'success') {
		eshop_ajax_return_success(['message' => 'Ticket supprimé avec succès']);
	} else {
		eshop_ajax_return_error("Erreur lors de la suppression du ticket");
	}
}

if (($_POST['action'] ?? '') === 'mark_solved_btn') {
	$ticket_id = $_POST['ticket_id'] ?? 0;
	
	if (!$ticket_id || !is_numeric($ticket_id)) {
		eshop_ajax_return_error("ID de ticket invalide");
	}
	
	$result = mark_solved($ticket_id);
	if ($result === 'success') {
		eshop_ajax_return_success(['message' => 'Ticket marqué comme résolu']);
	} else {
		eshop_ajax_return_error("Erreur lors du marquage du ticket");
	}
}

if (($_POST['action'] ?? '') === 'mark_unsolved_btn') {
	$ticket_id = $_POST['ticket_id'] ?? 0;
	
	if (!$ticket_id || !is_numeric($ticket_id)) {
		eshop_ajax_return_error("ID de ticket invalide");
	}
	
	$result = mark_unsolved($ticket_id);
	if ($result === 'success') {
		eshop_ajax_return_success(['message' => 'Ticket marqué comme non résolu']);
	} else {
		eshop_ajax_return_error("Erreur lors du marquage du ticket");
	}
}

if (($_POST['action'] ?? '') === 'mark_critical_btn') {
	$ticket_id = $_POST['ticket_id'] ?? 0;
	
	if (!$ticket_id || !is_numeric($ticket_id)) {
		eshop_ajax_return_error("ID de ticket invalide");
	}
	
	$result = mark_critical($ticket_id);
	if ($result === 'success') {
		eshop_ajax_return_success(['message' => 'Ticket marqué comme critique']);
	} else {
		eshop_ajax_return_error("Erreur lors du marquage du ticket");
	}
}

if (($_POST['action'] ?? '') === 'mark_normal_btn') {
	$ticket_id = $_POST['ticket_id'] ?? 0;
	
	if (!$ticket_id || !is_numeric($ticket_id)) {
		eshop_ajax_return_error("ID de ticket invalide");
	}
	
	$result = mark_normal($ticket_id);
	if ($result === 'success') {
		eshop_ajax_return_success(['message' => 'Ticket marqué comme normal']);
	} else {
		eshop_ajax_return_error("Erreur lors du marquage du ticket");
	}
}

if (($_POST['action'] ?? '') === 'reopen_ticket_btn') {
	$ticket_id = $_POST['ticket_id'] ?? 0;
	
	if (!$ticket_id || !is_numeric($ticket_id)) {
		eshop_ajax_return_error("ID de ticket invalide");
	}
	
	$result = reopen_ticket($ticket_id);
	if ($result === 'success') {
		eshop_ajax_return_success(['message' => 'Ticket rouvert avec succès']);
	} else {
		eshop_ajax_return_error("Erreur lors de la réouverture du ticket");
	}
}

// Action non reconnue
$action = $_POST['action'] ?? 'non définie';
eshop_ajax_return_error("Action non reconnue: " . $action);