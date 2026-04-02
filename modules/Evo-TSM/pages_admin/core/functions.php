<?php

ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('xdebug.var_display_max_depth', '10');
ini_set('xdebug.var_display_max_children', '256');
ini_set('xdebug.var_display_max_data', '1024');
error_reporting(E_ALL);

/*
 *  Function for listing every tickets
 */

global $user_session;

if (!defined('TICKET_ALL')) {
    define('TICKET_ALL', 1);
    define('TICKET_OPEN', 2);
    define('TICKET_CLOSE', 3);
    define('TICKET_CRITICAL', 4);
    define('TICKET_UNASSIGNED', 5);
    define('TICKET_CLOSE_USER', 6);
    define('TICKET_CLOSE_MOD', 7);
    define('TICKET_CLOSE_ADM', 8);
    define('TICKET_ANSWERED', 9);
}

if (!function_exists('get_tickets')) {
    function get_tickets(int $type = TICKET_ALL, int $start = 0, int $count = 100): array
    {
        $conditions = [
            TICKET_CLOSE => 'close_date IS NOT NULL',
            TICKET_OPEN => 'close_date IS NULL',
            TICKET_CRITICAL => '`level` > 0',
            TICKET_UNASSIGNED => '`assignation` = 0',
        ];
        
        $where = $conditions[$type] ?? '1';

        return \DB::QueryAll("SELECT
                                {tss_ticket}.*,
                                account.username AS account,
                                assignation.username AS moderator,
                                {tss_rates}.score
                            FROM {tss_ticket}
                            LEFT JOIN {users} AS account ON {tss_ticket}.sid = account.id
                            LEFT JOIN {users} AS assignation ON {tss_ticket}.assignation = assignation.id
                            LEFT JOIN {tss_rates} ON {tss_ticket}.id = {tss_rates}.score
                            WHERE $where ORDER BY id ASC LIMIT $start, $count"
        );
    }
}

function get_tickets_admin(int $type = TICKET_ALL, int $start = 0, int $count = 100): array
{
    $conditions = [
        TICKET_CLOSE => 'close_date IS NOT NULL',
        TICKET_OPEN => 'close_date IS NULL',
        TICKET_CRITICAL => '`level` > 0',
        TICKET_UNASSIGNED => '(`assignation` IS NULL OR `assignation` = 0)',
    ];
    
    $where = $conditions[$type] ?? '1';

    return \DB::QueryAll("SELECT
                            {tss_ticket}.*,
                            account.username AS account,
                            assignation.username AS assignation_name,
                            {tss_ticket}.assignation AS assignation_id,
                            (SELECT score FROM tss_rates WHERE tid = tss_ticket.id ORDER BY send_date DESC LIMIT 1) AS score
                        FROM {tss_ticket}
                        LEFT JOIN {users} as account ON {tss_ticket}.sid = account.id
                        LEFT JOIN {users} AS assignation ON {tss_ticket}.assignation = assignation.id
                        WHERE $where ORDER BY id ASC LIMIT $start, $count"
    );
}

/*
 * Retreive Ticket's information
 */

if (!function_exists('ticket_get_information')) {
    function ticket_get_information($id){
        $get = \DB::Get("SELECT
                            {tss_ticket}.*,
                            account.username AS account,
                            account.email,
                            account.country,
                            account.registered,
                            assignation.username AS moderator
                    FROM {tss_ticket}
                        LEFT JOIN {users} AS account ON {tss_ticket}.sid = account.id
                        LEFT JOIN {users} AS assignation ON {tss_ticket}.assignation = assignation.id
                    WHERE {tss_ticket}.id = :tid",[':tid' => $id]);
        return $get;
    }
}

if (!function_exists('ticket_get_content')) {
    function ticket_get_content($id){
        $get = \DB::QueryAll("SELECT
                                {tss_content}.*
                            FROM {tss_content}
                            WHERE tid = :tid ORDER BY send_date DESC",[':tid' => $id]);
        return $get;
    }
}

/*
 * Retreive Ticket's Counts
 */

/**
 * Système de cache utilisant les fonctions natives d'EvoCMS
 * Utilise la table {cache} d'EvoCMS si disponible, sinon cache statique
 */
function evo_tsm_cache_get($key, $default = null)
{
    // Essayer d'abord le cache EvoCMS natif
    if (\DB::TableExists('tss_cache')) {
        try {
            $result = \DB::Get('SELECT data, expires FROM {tss_cache} WHERE cache_key = ? AND expires > ?', 
                'evo_tsm_' . $key, time());
            if ($result) {
                return json_decode($result['data'], true);
            }
        } catch (Exception $e) {
            // Fallback vers cache statique
        }
    }
    
    // Cache statique de fallback
    static $static_cache = [];
    static $cache_times = [];
    $cache_duration = 300; // 5 minutes par défaut
    
    if (isset($static_cache[$key]) && isset($cache_times[$key]) && 
        (time() - $cache_times[$key]) < $cache_duration) {
        return $static_cache[$key];
    }
    
    return $default;
}

/**
 * Mettre des données en cache
 */
function evo_tsm_cache_set($key, $data, $ttl = 300)
{
    // Essayer d'abord le cache EvoCMS natif
    if (\DB::TableExists('tss_cache')) {
        try {
            \DB::Query('INSERT OR REPLACE INTO {tss_cache} (cache_key, data, expires) VALUES (?, ?, ?)', 
                'evo_tsm_' . $key, json_encode($data), time() + $ttl);
            return true;
        } catch (Exception $e) {
            // Fallback vers cache statique
        }
    }
    
    // Cache statique de fallback
    static $static_cache = [];
    static $cache_times = [];
    
    $static_cache[$key] = $data;
    $cache_times[$key] = time();
    
    return true;
}

/**
 * Invalider le cache Evo-TSM
 */
function evo_tsm_cache_clear($pattern = null)
{
    if (\DB::TableExists('tss_cache')) {
        try {
            if ($pattern) {
                \DB::Query('DELETE FROM {tss_cache} WHERE cache_key LIKE ?', 'evo_tsm_' . $pattern . '%');
            } else {
                \DB::Query('DELETE FROM {tss_cache} WHERE cache_key LIKE ?', 'evo_tsm_%');
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    // Nettoyer le cache statique
    static $static_cache = [];
    static $cache_times = [];
    
    if ($pattern) {
        foreach (array_keys($static_cache) as $key) {
            if (strpos($key, $pattern) === 0) {
                unset($static_cache[$key]);
                unset($cache_times[$key]);
            }
        }
    } else {
        $static_cache = [];
        $cache_times = [];
    }
    
    return true;
}

/**
 * Validation optimisée des entrées de ticket
 */
function validate_ticket_input($tid, $msg)
{
    // Validation de l'ID du ticket
    if (!is_numeric($tid) || $tid <= 0) {
        log_ticket_error('warning', 'ID de ticket invalide', ['tid' => $tid]);
        return false;
    }
    
    // Validation du message
    if (empty(trim($msg)) || strlen($msg) < 3) {
        log_ticket_error('warning', 'Message trop court ou vide', ['msg_length' => strlen($msg)]);
        return false;
    }
    
    // Protection XSS basique
    $msg = strip_tags($msg);
    if (strlen($msg) > 5000) {
        log_ticket_error('warning', 'Message trop long', ['msg_length' => strlen($msg)]);
        return false;
    }
    
    return true;
}

/**
 * Validation optimisée des données de contact
 */
function validate_contact_data($data)
{
    $errors = [];
    
    // Validation de l'email
    if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email invalide';
    }
    
    // Validation du sujet
    if (empty($data['subject']) || strlen(trim($data['subject'])) < 3) {
        $errors[] = 'Sujet requis (minimum 3 caractères)';
    }
    
    // Validation du message
    if (empty($data['message']) || strlen(trim($data['message'])) < 10) {
        $errors[] = 'Message requis (minimum 10 caractères)';
    }
    
    // Protection XSS
    $data['subject'] = strip_tags($data['subject']);
    $data['message'] = strip_tags($data['message']);
    
    if (strlen($data['subject']) > 200) {
        $errors[] = 'Sujet trop long (maximum 200 caractères)';
    }
    
    if (strlen($data['message']) > 5000) {
        $errors[] = 'Message trop long (maximum 5000 caractères)';
    }
    
    return empty($errors) ? true : $errors;
}

/**
 * Optimisation des requêtes de tickets avec pagination
 */
function get_tickets_optimized($type = TICKET_ALL, $start = 0, $count = 100, $order_by = 'id DESC')
{
    $cache_key = "tickets_{$type}_{$start}_{$count}_{$order_by}";
    $cached_data = evo_tsm_cache_get($cache_key);
    
    if ($cached_data !== null) {
        return $cached_data;
    }
    
    $conditions = [
        TICKET_CLOSE => 'close_date IS NOT NULL',
        TICKET_OPEN => 'close_date IS NULL',
        TICKET_CRITICAL => 'level > 0',
        TICKET_UNASSIGNED => 'assignation = 0',
    ];
    
    $where = $conditions[$type] ?? '1';
    
    try {
        $tickets = \DB::QueryAll("SELECT
            t.*,
            u.username AS account,
            u2.username AS moderator,
            r.score
        FROM {tss_ticket} t
        LEFT JOIN {users} u ON t.sid = u.id
        LEFT JOIN {users} u2 ON t.assignation = u2.id
        LEFT JOIN {tss_rates} r ON t.id = r.tid
        WHERE $where 
        ORDER BY $order_by 
        LIMIT $start, $count");
        
        // Mettre en cache le résultat
        $ttl = evo_tsm_get_config('cache.ticket_lists_ttl', 120);
        evo_tsm_cache_set($cache_key, $tickets, $ttl);
        
        return $tickets;
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Récupérer les données utilisateur avec cache
 */
function get_user_data_cached($user_id)
{
    static $user_cache = [];
    
    if (!isset($user_cache[$user_id])) {
        $user = \DB::Get("SELECT id, username, email, group_id FROM {users} WHERE id = ?", [$user_id]);
        $user_cache[$user_id] = $user;
    }
    
    return $user_cache[$user_id];
}

/**
 * Récupérer le nom d'utilisateur avec cache
 */
function get_user_name_cached($user_id)
{
    if (!$user_id || $user_id == 0) {
        return 'Non assigné';
    }
    
    $user = get_user_data_cached($user_id);
    return $user['username'] ?? 'Utilisateur inconnu';
}

/**
 * Charger la configuration des optimisations
 */
function evo_tsm_get_config($key = null, $default = null)
{
    static $config = null;
    
    if ($config === null) {
        $config_file = __DIR__ . '/../config/optimizations.php';
        if (file_exists($config_file)) {
            $config = include $config_file;
        } else {
            $config = [];
        }
    }
    
    if ($key === null) {
        return $config;
    }
    
    $keys = explode('.', $key);
    $value = $config;
    
    foreach ($keys as $k) {
        if (isset($value[$k])) {
            $value = $value[$k];
        } else {
            return $default;
        }
    }
    
    return $value;
}

/**
 * Récupère tous les compteurs de tickets en une seule requête (optimisé)
 * @return array Tableau avec tous les compteurs
 */
function get_ticket_counts_optimized()
{
    $cache_key = 'ticket_counts';
    $cached_data = evo_tsm_cache_get($cache_key);
    
    if ($cached_data !== null) {
        return $cached_data;
    }
    
    $counts = \DB::Get("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN assignation = 0 THEN 1 ELSE 0 END) as unassigned,
        SUM(CASE WHEN close_date IS NULL THEN 1 ELSE 0 END) as open,
        SUM(CASE WHEN level != 0 THEN 1 ELSE 0 END) as critical,
        SUM(CASE WHEN close_date IS NOT NULL THEN 1 ELSE 0 END) as closed
        FROM {tss_ticket}");
    
    $result = [
        'total' => $counts['total'] ?? 0,
        'unassigned' => $counts['unassigned'] ?? 0,
        'open' => $counts['open'] ?? 0,
        'critical' => $counts['critical'] ?? 0,
        'closed' => $counts['closed'] ?? 0
    ];
    
    // Mettre en cache le résultat avec TTL configuré
    $ttl = evo_tsm_get_config('cache.ticket_counts_ttl', 60);
    evo_tsm_cache_set($cache_key, $result, $ttl);
    
    return $result;
}

function ticket_count(){
    $counts = get_ticket_counts_optimized();
    return ['nbr' => $counts['total']];
}

function ticket_count_unassigned(){
    $counts = get_ticket_counts_optimized();
    return ['nbr' => $counts['unassigned']];
}

function ticket_count_open(){
    $counts = get_ticket_counts_optimized();
    return ['nbr' => $counts['open']];
}

function ticket_count_critical(){
    $counts = get_ticket_counts_optimized();
    return ['nbr' => $counts['critical']];
}

function ticket_count_close(){
    $counts = get_ticket_counts_optimized();
    return ['nbr' => $counts['closed']];
}

// Fonction supprimée - non utilisée

function ticket_get_scored(){
    $get = \DB::QueryAll("SELECT DISTINCT
                            assignation.id AS mid,
                            assignation.username AS modo,
                            groups.name AS rank
                        FROM {tss_ticket}
                        LEFT JOIN {users} AS assignation ON {tss_ticket}.assignation = assignation.id
                        LEFT JOIN {groups} AS groups ON assignation.group_id = groups.id
                        LEFT JOIN {tss_rates} AS rates ON {tss_ticket}.id = rates.tid
                        WHERE {tss_ticket}.close_date IS NOT NULL");
    return $get;
}

// Fonction supprimée - non utilisée

function global_score($mid){
    $cache_key = 'global_score_' . $mid;
    $cached_score = evo_tsm_cache_get($cache_key);
    
    if ($cached_score !== null) {
        return $cached_score;
    }
    
    // Optimisation : une seule requête au lieu d'une boucle
    $result = \DB::Get("SELECT 
        COUNT(DISTINCT t.id) as total_tickets,
        COUNT(r.id) as scored_tickets,
        COALESCE(SUM(r.score), 0) as total_score
        FROM {tss_ticket} t
        LEFT JOIN {tss_rates} r ON t.id = r.tid AND r.score > 0
        WHERE t.assignation = :mid AND t.close_date IS NOT NULL", 
        [':mid' => $mid]);
    
    $total_tickets = $result['total_tickets'] ?? 0;
    $scored_tickets = $result['scored_tickets'] ?? 0;
    $total_score = $result['total_score'] ?? 0;
    
    if ($scored_tickets > 0) {
        $final = round($total_score / $scored_tickets, 0);
    } else {
        $final = 0;
    }
    
    // Mettre en cache le résultat avec TTL configuré
    $ttl = evo_tsm_get_config('cache.global_score_ttl', 300);
    evo_tsm_cache_set($cache_key, $final, $ttl);
    
    return $final;
}

function send_answer_assigned_btn($tid, $msg){
    // Validation des entrées
    if (!validate_ticket_input($tid, $msg)) {
        return 'error';
    }
    
    $current_user = App::getCurrentUser();
    $current_user_id = $current_user->id;
    $current_group_id = $current_user->group_id;
    
    // Utiliser une transaction pour garantir la cohérence
    \DB::Query('START TRANSACTION');
    
    try {
        \DB::Insert('tss_content', [
            'tid' => $tid,
            'sid' => '0',
            'mid' => $current_user_id,
            'msg' => $msg,
            'send_date' => date("Y-m-d H:i:s"),
            'ip' => $_SERVER['REMOTE_ADDR']
        ]);

        // Vérifier si l'utilisateur est admin (group_id = 1) ou modo (group_id = 2)
        if ($current_group_id == 1 || $current_group_id == 2) {
            // Vérifier l'état actuel du billet
            $current_assignation = \DB::Get("SELECT assignation FROM tss_ticket WHERE id = ?", [$tid]);
            
            // Vérifier si le résultat est valide
            $is_orphan = true;
            if (is_array($current_assignation) && isset($current_assignation['assignation'])) {
                $assignation_value = $current_assignation['assignation'];
                $is_orphan = ($assignation_value == 0 || $assignation_value == null || $assignation_value === '');
            }
            
            if ($is_orphan) {
                // Billet orphelin : assigner à l'admin/modo qui répond
                $assignation_id = $current_user_id;
                
                // Mettre à jour l'assignation
                \DB::Update('tss_ticket', ['assignation' => $assignation_id], ['id' => $tid]);
                
                // Invalider le cache des compteurs
                evo_tsm_cache_clear('ticket_');
            }
        }
        
        \DB::Query('COMMIT');
        return 'success';
        
    } catch (Exception $e) {
        \DB::Query('ROLLBACK');
        log_ticket_error('error', 'Erreur lors de l\'envoi de la réponse', ['tid' => $tid, 'error' => $e->getMessage()]);
        return 'error';
    }
}

function mark_solved($tid)
{
    // Validation de l'ID du ticket
    if (!is_numeric($tid) || $tid <= 0) {
        log_ticket_error('warning', 'ID de ticket invalide pour marquer comme résolu', ['tid' => $tid]);
        return 'error';
    }
    
    $EndDate = date("Y-m-d H:i:s");
    $current_user_id = App::getCurrentUser()->id;
    
    // Utiliser une transaction pour garantir la cohérence
    \DB::Query('START TRANSACTION');
    
    try {
        \DB::Insert('tss_rates', [
            'tid' => $tid,
            'sid' => $current_user_id,
            'send_date' => $EndDate,
            'score' => '0',
            'comment' => 'Closed by a moderator'
        ]);

        \DB::Update('tss_ticket', ['close_date' => $EndDate], ['id' => $tid]);
        
        \DB::Query('COMMIT');
        
        // Invalider le cache des compteurs et scores
        evo_tsm_cache_clear('ticket_');
        evo_tsm_cache_clear('global_score_');
        
        log_ticket_error('info', 'Ticket marqué comme résolu', ['tid' => $tid, 'user_id' => $current_user_id]);
        return 'success';
        
    } catch (Exception $e) {
        \DB::Query('ROLLBACK');
        log_ticket_error('error', 'Erreur lors du marquage du ticket comme résolu', ['tid' => $tid, 'error' => $e->getMessage()]);
        return 'error';
    }
}

function mark_unsolved($tid)
{
    // Validation de l'ID du ticket
    if (!is_numeric($tid) || $tid <= 0) {
        log_ticket_error('warning', 'ID de ticket invalide pour marquer comme non résolu', ['tid' => $tid]);
        return 'error';
    }
    
    try {
        \DB::Update('tss_ticket', ['close_date' => NULL], ['id' => $tid]);
        
        // Invalider le cache des compteurs
        evo_tsm_cache_clear('ticket_');
        
        log_ticket_error('info', 'Ticket marqué comme non résolu', ['tid' => $tid]);
        return 'success';
        
    } catch (Exception $e) {
        log_ticket_error('error', 'Erreur lors du marquage du ticket comme non résolu', ['tid' => $tid, 'error' => $e->getMessage()]);
        return 'error';
    }
}

function mark_critical($tid)
{
    // Validation de l'ID du ticket
    if (!is_numeric($tid) || $tid <= 0) {
        log_ticket_error('warning', 'ID de ticket invalide pour marquer comme critique', ['tid' => $tid]);
        return 'error';
    }
    
    try {
        \DB::Update('tss_ticket', ['level' => 1], ['id' => $tid]);
        
        // Invalider le cache des compteurs
        evo_tsm_cache_clear('ticket_');
        
        log_ticket_error('info', 'Ticket marqué comme critique', ['tid' => $tid]);
        return 'success';
        
    } catch (Exception $e) {
        log_ticket_error('error', 'Erreur lors du marquage du ticket comme critique', ['tid' => $tid, 'error' => $e->getMessage()]);
        return 'error';
    }
}

function mark_normal($tid)
{
    // Validation de l'ID du ticket
    if (!is_numeric($tid) || $tid <= 0) {
        log_ticket_error('warning', 'ID de ticket invalide pour marquer comme normal', ['tid' => $tid]);
        return 'error';
    }
    
    try {
        \DB::Update('tss_ticket', ['level' => 0], ['id' => $tid]);
        
        // Invalider le cache des compteurs
        evo_tsm_cache_clear('ticket_');
        
        log_ticket_error('info', 'Ticket marqué comme normal', ['tid' => $tid]);
        return 'success';
        
    } catch (Exception $e) {
        log_ticket_error('error', 'Erreur lors du marquage du ticket comme normal', ['tid' => $tid, 'error' => $e->getMessage()]);
        return 'error';
    }
}

    function ticket_email_user($to, $subject, $message)
        {
            
            $subject = [
                TICKET_CLOSE_USER => 'Votre ticket a été fermé',
                TICKET_CLOSE_MOD => 'Votre ticket a été fermé',
                TICKET_OPEN => 'Votre billet a été ouvert',
                TICKET_CRITICAL => 'Le niveau de votre billet a été changé',
                TICKET_UNASSIGNED => 'Votre billet est en attente d\'assignement',
            ];

            $message = [
                TICKET_CLOSE_USER => 'Vous recevez ce message parce que vous avez fermé un billet. Cliquez içi pour visualiser le billet.',
                TICKET_CLOSE_MOD => 'Vous recevez ce message parce que votre billet a été fermé par le modérateur assigné. Cliquez içi pour visualiser le billet.',
                TICKET_OPEN => 'Vous recevez ce message parce que vous avez ouvert un billet. Cliquez içi pour visualiser le billet. ',
                TICKET_CRITICAL => 'Vous recevez ce message parce que le niveau de votre billet est reconnu comme critique. Cliquez içi pour visualiser le billet.',
                TICKET_UNASSIGNED => 'Votre billet est en attente d\'assignement. Cliquez içi pour visualiser le billet.',
            ];

            SendPrivateMessage($to, $subject, $message);

            return 'success';
        }

    function ticket_email_staff($to, $subject, $message)
        {
            
            $subject = [
                TICKET_CLOSE => "Un billet vient d'être fermé.",
                TICKET_OPEN => "Un nouveau billet vient d\'être créé.",
                TICKET_CRITICAL => "Le niveau de votre billet est maintenant : critique",
                TICKET_UNASSIGNED => "Un nouveau billet vient d\'être créé et est en attente d'assignement.",
            ];

            $message = [
                TICKET_CLOSE_MOD => 'Vous recevez ce message parce que votre billet a été fermé par le modérateur assigné. Cliquez içi pour visualiser le billet.',
                TICKET_CLOSE_ADM => 'Vous recevez ce message parce que votre billet a été fermé par l\administrateur. Cliquez içi pour visualiser le billet.',
                TICKET_OPEN => 'Vous recevez ce message parce qu\'un billet vient d\'être créé.  Cliquez içi pour visualiser le billet. ',
                TICKET_CRITICAL => 'Vous recevez ce message parce que le niveau de votre billet est reconnu comme critique. Cliquez içi pour visualiser le billet.',
                TICKET_UNASSIGNED => 'Vous recevez ce message parce qu\'un billet est en attente d\'assignement. Cliquez içi pour visualiser le billet.',
            ];


            SendPrivateMessage($to, $subject, $message);

            return 'success';
        }

    function ticket_email_notif_user($to, $subject, $message, $title)
        {

            $subject = [
                TICKET_ANSWERED => '[Ticket] Vous avez reçus une réponse !',
            ];

            $message = [
                TICKET_ANSWERED => 'Vous recevez ce message parce que vous avez reçus une répondre sur votre billet intitullé :'. $title .'. Cliquez içi pour visualiser le billet.',
            ];

            SendPrivateMessage($to, $subject, $message);

            return 'success';
        }


function admin_create_ticket($uid, $mid, $lid, $sujet, $desc){
    \DB::Insert('tss_ticket', [
        'sid' => $uid,
        'subject' => $sujet,
        'short_desc' => $desc,
        'assignation' => $mid,
        'level' => $lid,
        'create_date' => date("Y-m-d H:i:s")
    ]);
    return 'success';
}

function admin_change_assignation($tid, $mid){
    \DB::Update('tss_ticket', ['assignation' => $mid], ['id' => $tid]);
    return 'success';
}

/**
 * Fermer un ticket (côté admin)
 * @param int $tid ID du ticket
 * @return string
 */
function close_ticket($tid){
    if (!is_numeric($tid) || $tid <= 0) {
        return false;
    }
    
    try {
        \DB::Update('tss_ticket', ['close_date' => date("Y-m-d H:i:s")], ['id' => $tid]);
        evo_tsm_cache_clear(); // Invalider le cache
        return 'success';
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Supprimer un ticket (côté admin)
 * @param int $tid ID du ticket
 * @return string
 */
function delete_ticket($tid){
    if (!is_numeric($tid) || $tid <= 0) {
        return false;
    }
    
    try {
        // Vérifier que le ticket existe
        $check = \DB::Query("SELECT id FROM {tss_ticket} WHERE id = :tid", [':tid' => $tid]);
        
        if($check){
            // Supprimer le ticket et toutes ses données associées
            \DB::Delete('tss_ticket', ['id' => $tid]);
            \DB::Delete('tss_rates', ['tid' => $tid]);
            \DB::Delete('tss_content', ['tid' => $tid]);
            \DB::Delete('tss_admin_notes', ['tid' => $tid]);
            
            evo_tsm_cache_clear(); // Invalider le cache
            return 'success';
        } else {
            return false;
        }
    } catch (Exception $e) {
        return false;
    }
}

function get_ticket_rates_stats()
{
    return \DB::QueryAll("SELECT
                            rates.*,
                            ticket.subject,
                            staff.username,
                            client.username AS client_name
                        FROM {tss_rates} AS rates
                        LEFT JOIN {tss_ticket} AS ticket ON rates.tid = ticket.id
                        LEFT JOIN {users} AS staff ON ticket.assignation = staff.id
                        LEFT JOIN {users} AS client ON ticket.sid = client.id
                        WHERE rates.score > 0
                        ORDER BY rates.send_date DESC
                        LIMIT 10"
    );
}

function get_contact_messages($limit = 50)
{
    // Vérifier si la table existe
    $table_exists = \DB::Query("SELECT name FROM sqlite_master WHERE type='table' AND name='tss_contact_messages'");
    
    if (!$table_exists) {
        return [];
    }
    
    return \DB::QueryAll("SELECT * FROM tss_contact_messages ORDER BY created_date DESC LIMIT ?", [$limit]);
}

function get_contact_message_count()
{
    $cache_key = 'contact_message_count';
    $cached_count = evo_tsm_cache_get($cache_key);
    
    if ($cached_count !== null) {
        return $cached_count;
    }
    
    // Vérifier si la table existe
    if (!\DB::TableExists('tss_contact_messages')) {
        evo_tsm_cache_set($cache_key, 0, 300);
        return 0;
    }
    
    $count = \DB::Get("SELECT COUNT(*) AS nbr FROM {tss_contact_messages}");
    $result = $count['nbr'] ?? 0;
    
    // Mettre en cache le résultat avec TTL configuré
    $ttl = evo_tsm_get_config('cache.contact_messages_ttl', 300);
    evo_tsm_cache_set($cache_key, $result, $ttl);
    
    return $result;
}

/**
 * Extrait l'email de l'auteur d'un module
 * @param string $module_name Nom du module
 * @return string Email extrait ou chaîne vide
 */
function get_module_author_email($module_name)
{
    static $cache = [];
    
    // Utiliser le cache pour éviter les appels répétitifs
    if (isset($cache[$module_name])) {
        return $cache[$module_name];
    }
    
    $author_string = App::getModule($module_name)->author ?? '';
    
    // Gérer le cas où author est un tableau ou null
    if (is_array($author_string)) {
        $author_string = $author_string[0] ?? '';
    }
    
    $creator_email = '';
    
    // Extraire l'email du format "Nom (email@domain.com)" ou "Nom <email@domain.com>"
    if (preg_match('/\(([^)]+@[^)]+)\)/', $author_string, $matches)) {
        // Format: "Nom (email@domain.com)"
        $creator_email = $matches[1];
    } elseif (preg_match('/<([^>]+@[^>]+)>/', $author_string, $matches)) {
        // Format: "Nom <email@domain.com>"
        $creator_email = $matches[1];
    } elseif (preg_match('/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/', $author_string, $matches)) {
        // Format: juste l'email
        $creator_email = $matches[1];
    }
    
    // Mettre en cache le résultat
    $cache[$module_name] = $creator_email;
    
    return $creator_email;
}

/**
 * Gestion centralisée des erreurs et logs
 * @param string $level Niveau d'erreur (error, warning, info, debug)
 * @param string $message Message d'erreur
 * @param array $context Contexte supplémentaire
 */
function log_ticket_error($level, $message, $context = [])
{
    $log_levels = [
        'error' => E_ERROR,
        'warning' => E_WARNING,
        'info' => E_NOTICE,
        'debug' => E_NOTICE
    ];
    
    $current_level = $log_levels[$level] ?? E_ERROR;
    
    // Ne logger que si le niveau est approprié
    if (defined('EVO_DEBUG') && EVO_DEBUG) {
        $log_message = "[Evo-TSM] [$level] $message";
        
        if (!empty($context)) {
            $log_message .= " | Context: " . json_encode($context);
        }
        
    }
}

/**
 * Valide les données d'entrée du formulaire de contact
 * @param array $data Données du formulaire
 * @return array Tableau avec 'valid' (bool) et 'errors' (array)
 */
function validate_contact_form($data)
{
    $errors = [];
    
    // Validation du sujet
    if (empty($data['subject'])) {
        $errors[] = 'Le sujet est requis.';
    } elseif (strlen($data['subject']) > 255) {
        $errors[] = 'Le sujet ne peut pas dépasser 255 caractères.';
    }
    
    // Validation du message
    if (empty($data['message'])) {
        $errors[] = 'Le message est requis.';
    } elseif (strlen($data['message']) > 5000) {
        $errors[] = 'Le message ne peut pas dépasser 5000 caractères.';
    }
    
    // Validation XSS basique
    $subject_clean = strip_tags($data['subject']);
    $message_clean = strip_tags($data['message']);
    
    if ($subject_clean !== $data['subject'] || $message_clean !== $data['message']) {
        $errors[] = 'Le contenu contient des balises HTML non autorisées.';
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'cleaned_data' => [
            'subject' => $subject_clean,
            'message' => $message_clean,
            'followup' => isset($data['followup']) ? 1 : 0
        ]
    ];
}

/**
 * Rouvrir un ticket (côté admin)
 * @param int $tid ID du ticket
 * @return string
 */
function reopen_ticket($tid){
    if (!is_numeric($tid) || $tid <= 0) {
        return false;
    }
    
    try {
        \DB::Update('tss_ticket', ['close_date' => null], ['id' => $tid]);
        evo_tsm_cache_clear(); // Invalider le cache
        return 'success';
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Récupérer les tickets par état pour l'interface admin
 * @param string $state - État des tickets (open, closed, critical, unassigned)
 * @return array
 */
function get_tickets_by_state($state = 'open') {
    $conditions = [
        'open' => 'close_date IS NULL',
        'closed' => 'close_date IS NOT NULL',
        'critical' => '`level` > 0',
        'unassigned' => '`assignation` = 0',
        'opened' => 'close_date IS NULL' // Alias pour 'open'
    ];
    
    $where = $conditions[$state] ?? '1';
    
    try {
        $tickets = \DB::QueryAll("SELECT
                                    {tss_ticket}.*,
                                    account.username AS account,
                                    assignation.username AS moderator,
                                    {tss_rates}.score
                                FROM {tss_ticket}
                                LEFT JOIN {users} AS account ON {tss_ticket}.sid = account.id
                                LEFT JOIN {users} AS assignation ON {tss_ticket}.assignation = assignation.id
                                LEFT JOIN {tss_rates} ON {tss_ticket}.id = {tss_rates}.score
                                WHERE $where 
                                ORDER BY {tss_ticket}.id DESC"
        );
        
        // Formater les données pour l'affichage
        $formattedTickets = [];
        foreach ($tickets as $ticket) {
            $formattedTickets[] = [
                'id' => $ticket['id'],
                'subject' => htmlspecialchars($ticket['subject']),
                'content' => htmlspecialchars(substr($ticket['short_desc'] ?? '', 0, 100)) . '...',
                'account' => $ticket['account'] ?? 'N/A',
                'moderator' => $ticket['moderator'] ?? 'Non assigné',
                'level' => $ticket['level'],
                'status' => $ticket['close_date'] ? 'Fermé' : 'Ouvert',
                'created_date' => date('d/m/Y H:i', strtotime($ticket['create_date'])),
                'close_date' => $ticket['close_date'] ? date('d/m/Y H:i', strtotime($ticket['close_date'])) : null,
                'score' => $ticket['score'] ?? 0
            ];
        }
        
        return $formattedTickets;
    } catch (Exception $e) {
        error_log("Erreur get_tickets_by_state: " . $e->getMessage());
        return [];
    }
}

/**
 * Récupérer les données pour le graphique des tickets
 * @return array
 */
function get_ticket_chart_data() {
    try {
        // Compter les tickets ouverts
        $openResult = \DB::QueryAll("SELECT COUNT(*) as count FROM {tss_ticket} WHERE close_date IS NULL");
        $openCount = $openResult[0]['count'] ?? 0;
        
        // Compter les tickets fermés
        $closeResult = \DB::QueryAll("SELECT COUNT(*) as count FROM {tss_ticket} WHERE close_date IS NOT NULL");
        $closeCount = $closeResult[0]['count'] ?? 0;
        
        // Compter les tickets non assignés
        $unassignedResult = \DB::QueryAll("SELECT COUNT(*) as count FROM {tss_ticket} WHERE assignation = 0");
        $unassignedCount = $unassignedResult[0]['count'] ?? 0;
        
        return [
            'open' => (int)$openCount,
            'close' => (int)$closeCount,
            'unassigned' => (int)$unassignedCount
        ];
    } catch (Exception $e) {
        error_log("Erreur get_ticket_chart_data: " . $e->getMessage());
        return [
            'open' => 0,
            'close' => 0,
            'unassigned' => 0
        ];
    }
}