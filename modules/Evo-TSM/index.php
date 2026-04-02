<?php
defined('EVO') or die;

return new class extends Evo\Module
{
	public function init(){
		// Ne pas charger les fonctions ici pour éviter les conflits avec l'admin
		
		parent::route('/support', function($params) {
			require_once __DIR__ .'/pages_user/core/functions.php';
			return __DIR__ . '/pages_user/main.php';
		});

		parent::route('/support/create', function($params) {
			require_once __DIR__ .'/pages_user/core/functions.php';
			return __DIR__ . '/pages_user/create.php';
		});

		parent::route('/support/view', function($params) {
			require_once __DIR__ .'/pages_user/core/functions.php';
			return __DIR__ . '/pages_user/view.php';
		});

		// AJAX endpoint for user
		parent::route('/user-ajax', function($params) {
			require_once __DIR__ .'/pages_user/core/functions.php';
			return __DIR__ . '/pages_user/ajax.php';
		});
		
		// AJAX endpoint for admin
		parent::route('/admin-ajax', function($params) {
			require_once __DIR__ .'/pages_admin/core/functions.php';
			
			// Gestion des actions AJAX directement ici
			header('Content-Type: application/json');
			
			function admin_ajax_return_success($data) {
				die(json_encode(['success' => true] + (array)$data));
			}

			function admin_ajax_return_error($error) {
				die(json_encode(['success' => false, 'error' => $error]));
			}

			// Fermer un ticket
			if (App::POST('action') === 'close_ticket_btn') {
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
			if (App::POST('action') === 'delete_ticket_btn') {
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
			if (App::POST('action') === 'mark_solved_btn') {
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
			if (App::POST('action') === 'mark_unsolved_btn') {
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
			if (App::POST('action') === 'mark_critical_btn') {
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
			if (App::POST('action') === 'mark_normal_btn') {
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

			// Envoyer une réponse assignée
			if (($_POST['action'] ?? '') === 'send_answer_assigned') {
				$tid = $_POST['tid'] ?? 0;
				$msg = $_POST['msg'] ?? '';
				
				if (!$tid || !is_numeric($tid)) {
					admin_ajax_return_error("ID de ticket invalide");
				}
				
				if (empty($msg)) {
					admin_ajax_return_error("Le message ne peut pas être vide");
				}
				
				$result = send_answer_assigned_btn($tid, $msg);
				if ($result) {
					admin_ajax_return_success(['message' => 'Réponse envoyée avec succès']);
				} else {
					admin_ajax_return_error("Erreur lors de l'envoi de la réponse");
				}
			}

			// Action non reconnue
			admin_ajax_return_error("Action non reconnue");
		});

		// AJAX endpoint for admin (alias)
		parent::route('/ajax', function($params) {
			require_once __DIR__ .'/pages_admin/core/functions.php';
			
			// Gestion des actions AJAX directement ici
			header('Content-Type: application/json');
			
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

			// Marquer comme résolu
			if (($_POST['action'] ?? '') === 'mark_solved') {
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
			if (($_POST['action'] ?? '') === 'mark_unsolved') {
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
			if (($_POST['action'] ?? '') === 'mark_critical') {
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
			if (($_POST['action'] ?? '') === 'mark_normal') {
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

			// Supprimer un ticket
			if (($_POST['action'] ?? '') === 'delete_ticket') {
				$ticket_id = $_POST['tid'] ?? 0;
				
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

			// Marquer comme résolu (alias pour solved_ticket)
			if (($_POST['action'] ?? '') === 'solved_ticket') {
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

			// Créer un ticket (admin)
			if (($_POST['action'] ?? '') === 'admin_create_ticket') {
				$uid = $_POST['uid'] ?? 0;
				$mid = $_POST['mid'] ?? 0;
				$lid = $_POST['lid'] ?? 0;
				$sujet = $_POST['sujet'] ?? '';
				$desc = $_POST['desc'] ?? '';
				
				if (!$uid || !is_numeric($uid)) {
					admin_ajax_return_error("ID utilisateur invalide");
				}
				
				if (empty($sujet)) {
					admin_ajax_return_error("Le sujet ne peut pas être vide");
				}
				
				if (empty($desc)) {
					admin_ajax_return_error("La description ne peut pas être vide");
				}
				
				$result = admin_create_ticket($uid, $mid, $lid, $sujet, $desc);
				if ($result) {
					admin_ajax_return_success(['message' => 'Ticket créé avec succès', 'id' => $result]);
				} else {
					admin_ajax_return_error("Erreur lors de la création du ticket");
				}
			}

			// Changer l'assignation d'un ticket (admin)
			if (($_POST['action'] ?? '') === 'admin_change_assignation') {
				$tid = $_POST['tid'] ?? 0;
				$mid = $_POST['mid'] ?? 0;
				
				if (!$tid || !is_numeric($tid)) {
					admin_ajax_return_error("ID de ticket invalide");
				}
				
				if (!$mid || !is_numeric($mid)) {
					admin_ajax_return_error("ID de modérateur invalide");
				}
				
				$result = admin_change_assignation($tid, $mid);
				if ($result) {
					admin_ajax_return_success(['message' => 'Assignation modifiée avec succès']);
				} else {
					admin_ajax_return_error("Erreur lors de la modification de l'assignation");
				}
			}

			// Envoyer une réponse assignée
			if (($_POST['action'] ?? '') === 'send_answer_assigned') {
				$tid = $_POST['tid'] ?? 0;
				$msg = $_POST['msg'] ?? '';
				
				if (!$tid || !is_numeric($tid)) {
					admin_ajax_return_error("ID de ticket invalide");
				}
				
				if (empty($msg)) {
					admin_ajax_return_error("Le message ne peut pas être vide");
				}
				
				$result = send_answer_assigned_btn($tid, $msg);
				if ($result) {
					admin_ajax_return_success(['message' => 'Réponse envoyée avec succès']);
				} else {
					admin_ajax_return_error("Erreur lors de l'envoi de la réponse");
				}
			}

			// Action non reconnue
			admin_ajax_return_error("Action non reconnue");
		});

		// Tickets list page for admin
		parent::route('/tickets', function($params) {
			#require_once __DIR__ .'/pages_admin/core/functions.php';
			return __DIR__ . '/pages_admin/tickets.php';
		});

		// Contact page for admin
		parent::route('/contact', function($params) {
			require_once __DIR__ .'/pages_admin/core/functions.php';
			return __DIR__ . '/pages_admin/contact.php';
		});

		// Messages page for admin
		parent::route('/messages', function($params) {
			require_once __DIR__ .'/pages_admin/core/functions.php';
			return __DIR__ . '/pages_admin/messages.php';
		});

		// Cache management page for admin
		parent::route('/cache', function($params) {
			require_once __DIR__ .'/pages_admin/core/functions.php';
			return __DIR__ . '/pages_admin/cache.php';
		});

		// Cache installation page for admin
		parent::route('/install', function($params) {
			require_once __DIR__ .'/pages_admin/core/functions.php';
			return __DIR__ . '/pages_admin/install.php';
		});

		// Performance monitoring page for admin
		parent::route('/performance', function($params) {
			require_once __DIR__ .'/pages_admin/core/functions.php';
			return __DIR__ . '/pages_admin/performance.php';
		});
	}

	public function activate()
	{
		\DB::CreateTable('tss_ticket', [
			'id'			=> 'increment', 
			'sid'			=> 'integer',
			'subject'		=> 'string',
			'short_desc'	=> 'string',
			'assignation'	=> array('integer', 0),
			'level'			=> array('integer', 0),
			'create_date'	=> 'dateTime',
			'close_date'	=> array('datetime', NULL),
			'availability'	=> array('integer', '1'),
		], false, true);

		\DB::CreateTable('tss_content', [
			'id'			=> 'increment', 
			'tid'			=> 'integer',
			'sid'			=> 'integer',
			'mid'			=> 'integer',
			'msg'			=> 'text',
			'send_date'		=> 'dateTime',
			'ip'			=> 'string'
		], false, true);

		\DB::CreateTable('tss_rates', [
			'id'			=> 'increment', 
			'tid'			=> 'integer',
			'sid'			=> 'integer',
			'score'			=> 'integer',
			'comment'		=> 'text',
			'send_date'		=> 'dateTime'
		], false, true);

		\DB::CreateTable('tss_admin_notes', [
			'id'			=> 'increment', 
			'tid'			=> 'integer', 
			'cid'			=> 'integer',
			'note'			=> 'text',
			'assignation'	=> 'integer',
			'send_date'		=> 'dateTime'
		], false, true);

		// Créer la table cache pour le système d'optimisation
		\DB::CreateTable('tss_cache', [
			'cache_key'		=> 'string',
			'data'			=> 'text',
			'expires'		=> 'integer'
		], false, true);

		// Créer les index pour la table cache
		\DB::Query("CREATE UNIQUE INDEX IF NOT EXISTS cache_key_idx ON {tss_cache} (cache_key)");
		\DB::Query("CREATE INDEX IF NOT EXISTS idx_cache_expires ON {tss_cache} (expires)");

		\DB::CreateTable('tss_contact_messages', [
			'id'			=> 'increment',
			'user_id'		=> 'integer',
			'username'		=> 'string',
			'user_email'	=> 'string',
			'subject'		=> 'string',
			'message'		=> 'text',
			'followup'		=> array('integer', 0),
			'user_agent'	=> 'text',
			'ip_address'	=> 'string',
			'created_date'	=> 'dateTime'
		], false, true);

		App::setNotice("Ticket system is enable");
	}

	public function deactivate()
	{
		\DB::DropTable('tss_ticket');
		\DB::DropTable('tss_content');
		\DB::DropTable('tss_rates');
		\DB::DropTable('tss_admin_notes');
		\DB::DropTable('tss_contact_messages');
		\DB::DropTable('tss_cache');
		App::setNotice("Ticket system is disable");
	}

	public function hook_user_menu(array &$items)
	{
		$items[] = ['Technical Support', 'fa-headset', APP::getURL('/support')];
	}
	
	public function hook_admin_menu(array &$items)
	{
		$items[] = ['Panel Ticket Support', 'fa-headset', '/admin/?p=Evo-TSM/home', null];
	}
};