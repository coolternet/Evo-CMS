<?php
defined('EVO') or die;

return new class extends Evo\Module
{
    public function init()
    {
        // Route pour afficher les statistiques publiques
        $this->route('/statistics', function($params) {
            if (has_permission('modules.statistics.view')) {
                echo $this->displayPublicStats();
            } else {
                echo '<p class="alert alert-warning">Accès refusé aux statistiques.</p>';
            }
            return true;
        });

        // Ne pas enregistrer les visites lors de l'initialisation
        // Cela sera fait après l'activation via hook_app_init_done
    }

    public function activate()
    {
        try {
            // Créer les tables dans l'ordre correct avec vérification
            $this->createStatsTable();
            $this->createPageStatsTable();
            $this->createVisitsTable();
            
            // Insérer des données initiales
            $this->insertInitialData();
            
            // Vérifier que toutes les tables existent
            $this->verifyTablesExist();
            
            // Enregistrer le hook pour les visites APRÈS avoir créé les tables
            $this->hook_app_init_done();
            
            App::setNotice("Module de statistiques activé avec succès !");
        } catch (Exception $e) {
            // En cas d'erreur, essayer de nettoyer et recréer
            $this->emergencyRepair();
            throw $e;
        }
    }

    public function deactivate()
    {
        // Optionnel : supprimer les tables lors de la désactivation
        // $this->dropStatsTables();
        App::setNotice("Module de statistiques désactivé !");
    }

    public function hook_admin_menu(array &$items)
    {
        $items[] = ['Statistiques', 'fa-chart-bar', '?p=statistics/statistics', 'modules.statistics.admin'];
    }

    public function hook_user_menu(array &$items)
    {
        if (has_permission('modules.statistics.view')) {
            $items[] = ['Statistiques', 'fa-chart-line', 'statistics'];
        }
    }

    public function hook_ajax($action)
    {
        switch($action) {
            case 'get_stats':
                echo json_encode($this->getStatsData());
                break;
            case 'get_page_stats':
                echo json_encode($this->getPageStats());
                break;
            case 'get_visitor_stats':
                echo json_encode($this->getVisitorStats());
                break;
        }
    }

    public function hook_app_init_done()
    {
        // Enregistrer la visite actuelle
        $this->recordVisit();
    }

    /**
     * Créer la table des statistiques générales
     */
    private function createStatsTable()
    {
        if (!Db::TableExists('statistics')) {
            Db::CreateTable('statistics', [
                'id' => ['increment'],
                'date' => ['date', date('Y-m-d')],
                'visitors' => ['integer', 0],
                'page_views' => ['integer', 0],
                'unique_visitors' => ['integer', 0],
                'bounce_rate' => ['decimal', 0.0],
                'avg_time' => ['integer', 0],
                'created_at' => ['timestamp', date('Y-m-d H:i:s')]
            ]);
        }
    }

    /**
     * Créer la table des statistiques de pages
     */
    private function createPageStatsTable()
    {
        if (!Db::TableExists('page_statistics')) {
            Db::CreateTable('page_statistics', [
                'id' => ['increment'],
                'page_url' => ['varchar', ''],
                'page_title' => ['varchar', ''],
                'visits' => ['integer', 0],
                'unique_visits' => ['integer', 0],
                'avg_time' => ['integer', 0],
                'bounce_rate' => ['decimal', 0.0],
                'last_visit' => ['timestamp', date('Y-m-d H:i:s')],
                'created_at' => ['timestamp', date('Y-m-d H:i:s')]
            ]);
        }
    }

    /**
     * Créer la table des visites détaillées
     */
    private function createVisitsTable()
    {
        if (!Db::TableExists('statistics_visits')) {
            Db::CreateTable('statistics_visits', [
                'id' => ['increment'],
                'page_id' => ['integer', 0],
                'user_id' => ['integer', 0],
                'ip' => ['varchar', ''],
                'visit_date' => ['timestamp', date('Y-m-d H:i:s')],
                'user_agent' => ['text', '']
            ]);
        }
    }

    /**
     * Insérer des données initiales
     */
    private function insertInitialData()
    {
        $today = date('Y-m-d');
        
        // Vérifier si les stats du jour existent déjà
        $existing = Db::Get('SELECT * FROM {statistics} WHERE date = ?', $today);
        if (!$existing) {
            Db::Insert('statistics', [
                'date' => $today,
                'visitors' => 0,
                'page_views' => 0,
                'unique_visitors' => 0,
                'bounce_rate' => 0.0,
                'avg_time' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Vérifier que toutes les tables existent
     */
    private function verifyTablesExist()
    {
        $required_tables = ['statistics', 'page_statistics', 'statistics_visits'];
        
        foreach ($required_tables as $table) {
            if (!Db::TableExists($table)) {
                throw new Exception("La table '{$table}' n'a pas été créée correctement");
            }
        }
    }

    /**
     * Réparation d'urgence en cas d'échec d'activation
     */
    private function emergencyRepair()
    {
        // Supprimer les tables partiellement créées
        $tables = ['statistics', 'page_statistics', 'statistics_visits'];
        
        foreach ($tables as $table) {
            if (Db::TableExists($table)) {
                Db::DropTable($table);
            }
        }
        
        // Recréer toutes les tables
        $this->createStatsTable();
        $this->createPageStatsTable();
        $this->createVisitsTable();
        $this->insertInitialData();
    }

    /**
     * Enregistrer une visite
     */
    private function recordVisit()
    {
        // Vérifier que toutes les tables existent avant d'enregistrer
        if (!Db::TableExists('statistics') || !Db::TableExists('page_statistics') || !Db::TableExists('statistics_visits')) {
            // Si les tables n'existent pas, essayer de les créer
            try {
                $this->createStatsTable();
                $this->createPageStatsTable();
                $this->createVisitsTable();
                $this->insertInitialData();
            } catch (Exception $e) {
                // Si la création échoue, ne pas enregistrer la visite
                return;
            }
        }

        $current_user = App::getCurrentUser();
        $user_id = $current_user ? $current_user->id : 0;
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $page_url = $_SERVER['REQUEST_URI'] ?? '/';
        $referrer = $_SERVER['HTTP_REFERER'] ?? '';
        
        // Vérifier si c'est un robot
        if ($this->isBot($user_agent)) {
            return;
        }

        // Enregistrer la visite dans la table des pages
        $this->recordPageVisit($page_url, $user_id, $ip);

        // Mettre à jour les statistiques quotidiennes
        $this->updateDailyStats();
    }

    /**
     * Vérifier si c'est un bot
     */
    private function isBot($user_agent)
    {
        $bots = ['bot', 'crawler', 'spider', 'scraper', 'googlebot', 'bingbot'];
        $user_agent_lower = strtolower($user_agent);
        
        foreach ($bots as $bot) {
            if (strpos($user_agent_lower, $bot) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Enregistrer la visite d'une page
     */
    private function recordPageVisit($page_url, $user_id, $ip)
    {
        // Vérifier que la table existe avant de l'utiliser
        if (!Db::TableExists('page_statistics')) {
            // Si la table n'existe pas, essayer de la créer
            $this->createPageStatsTable();
            if (!Db::TableExists('page_statistics')) {
                return; // Éviter l'erreur si la création échoue
            }
        }

        $page_title = $this->getPageTitle($page_url);
        
        // Vérifier si la page existe déjà
        $existing = Db::Get('SELECT * FROM {page_statistics} WHERE page_url = ?', $page_url);
        
        if ($existing) {
            // Mettre à jour les statistiques existantes
            $visits = $existing['visits'] + 1;
            $unique_visits = $existing['unique_visits'];
            
            // Vérifier si c'est un nouveau visiteur unique
            $unique_check = Db::Get('SELECT COUNT(*) as count FROM {statistics_visits} WHERE page_id = ? AND ip = ? AND visit_date > ?', 
                $existing['id'], $ip, date('Y-m-d', strtotime('-1 day')));
            
            if ($unique_check['count'] == 0) {
                $unique_visits++;
            }
            
            Db::Exec('UPDATE {page_statistics} SET visits = ?, unique_visits = ?, last_visit = ? WHERE id = ?',
                $visits, $unique_visits, date('Y-m-d H:i:s'), $existing['id']);
        } else {
            // Créer une nouvelle entrée
            Db::Insert('page_statistics', [
                'page_url' => $page_url,
                'page_title' => $page_title,
                'visits' => 1,
                'unique_visits' => 1,
                'last_visit' => date('Y-m-d H:i:s')
            ]);
        }
        
        // Enregistrer le détail de la visite
        try {
            $page_result = Db::Get('SELECT id FROM {page_statistics} WHERE page_url = ?', $page_url);
            $page_id = is_array($page_result) ? $page_result['id'] : $page_result;
            
            if ($page_id) {
                Db::Insert('statistics_visits', [
                    'page_id' => $page_id,
                    'user_id' => $user_id,
                    'ip' => $ip,
                    'visit_date' => date('Y-m-d H:i:s'),
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
                ]);
            }
        } catch (Exception $e) {
            // En cas d'erreur, ne pas interrompre le processus
            // Log l'erreur si possible
            error_log("Erreur lors de l'enregistrement de la visite: " . $e->getMessage());
        }
    }

    /**
     * Obtenir le titre de la page
     */
    private function getPageTitle($page_url)
    {
        // Essayer de récupérer le titre depuis la base de données
        if (strpos($page_url, '?p=') !== false) {
            $page_slug = str_replace('?p=', '', $page_url);
            
            // Vérifier d'abord si la table pages existe et quelle est sa structure
            if (Db::TableExists('pages')) {
                try {
                    // Essayer différentes colonnes possibles pour le titre
                    $columns = Db::QueryAll("PRAGMA table_info(pages)");
                    $column_names = array_column($columns, 'name');
                    
                    if (in_array('title', $column_names)) {
                        $page = Db::Get('SELECT title FROM {pages} WHERE slug = ?', $page_slug);
                        if ($page) {
                            return $page['title'];
                        }
                    } elseif (in_array('name', $column_names)) {
                        $page = Db::Get('SELECT name FROM {pages} WHERE slug = ?', $page_slug);
                        if ($page) {
                            return $page['name'];
                        }
                    } elseif (in_array('label', $column_names)) {
                        $page = Db::Get('SELECT label FROM {pages} WHERE slug = ?', $page_slug);
                        if ($page) {
                            return $page['label'];
                        }
                    }
                } catch (Exception $e) {
                    // En cas d'erreur, continuer avec les titres par défaut
                }
            }
        }
        
        // Titres par défaut pour les pages spéciales
        $titles = [
            '/' => 'Accueil',
            '/login' => 'Connexion',
            '/register' => 'Inscription',
            '/forums' => 'Forums',
            '/gallery' => 'Galerie',
            '/downloads' => 'Téléchargements',
            '/admin' => 'Administration',
            '/modules' => 'Modules',
            '/settings' => 'Paramètres',
            '/users' => 'Utilisateurs',
            '/groups' => 'Groupes',
            '/history' => 'Historique',
            '/backup' => 'Sauvegarde',
            '/security' => 'Sécurité',
            '/reports' => 'Rapports',
            '/servers' => 'Serveurs',
            '/comments' => 'Commentaires',
            '/broadcast' => 'Diffusion',
            '/file_editor' => 'Éditeur de fichiers',
            '/phpinfo' => 'Informations PHP'
        ];
        
        return $titles[$page_url] ?? 'Page inconnue';
    }

    /**
     * Mettre à jour les statistiques quotidiennes
     */
    private function updateDailyStats()
    {
        // Vérifier que la table existe avant de l'utiliser
        if (!Db::TableExists('statistics')) {
            $this->createStatsTable();
            if (!Db::TableExists('statistics')) {
                return; // Éviter l'erreur si la création échoue
            }
        }

        $today = date('Y-m-d');
        
        // Vérifier si les stats du jour existent
        $existing = Db::Get('SELECT * FROM {statistics} WHERE date = ?', $today);
        
        if ($existing) {
            // Mettre à jour
            $visitors = $existing['visitors'] + 1;
            $page_views = $existing['page_views'] + 1;
            
            // Calculer les visiteurs uniques
            $unique_visitors = Db::Get('SELECT COUNT(DISTINCT ip) as count FROM {statistics_visits} WHERE DATE(visit_date) = ?', $today)['count'];
            
            Db::Exec('UPDATE {statistics} SET visitors = ?, page_views = ?, unique_visitors = ? WHERE id = ?',
                $visitors, $page_views, $unique_visitors, $existing['id']);
        } else {
            // Créer une nouvelle entrée
            Db::Insert('statistics', [
                'date' => $today,
                'visitors' => 1,
                'page_views' => 1,
                'unique_visitors' => 1
            ]);
        }
    }

    /**
     * Obtenir les données de statistiques
     */
    public function getStatsData()
    {
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        
        $today_stats = Db::Get('SELECT * FROM {statistics} WHERE date = ?', $today) ?: [];
        $yesterday_stats = Db::Get('SELECT * FROM {statistics} WHERE date = ?', $yesterday) ?: [];
        
        // Statistiques des 7 derniers jours
        $weekly_stats = Db::QueryAll('SELECT * FROM {statistics} WHERE date >= ? ORDER BY date DESC LIMIT 7', 
            date('Y-m-d', strtotime('-7 days')));
        
        return [
            'today' => $today_stats,
            'yesterday' => $yesterday_stats,
            'weekly' => $weekly_stats,
            'total_pages' => Db::Get('SELECT COUNT(*) as count FROM {page_statistics}')['count'],
            'total_visits' => Db::Get('SELECT SUM(visits) as total FROM {page_statistics}')['total'] ?: 0
        ];
    }

    /**
     * Obtenir les statistiques des pages
     */
    public function getPageStats()
    {
        return Db::QueryAll('SELECT * FROM {page_statistics} ORDER BY visits DESC LIMIT 10');
    }

    /**
     * Obtenir les statistiques des visiteurs
     */
    public function getVisitorStats()
    {
        $today = date('Y-m-d');
        $this_week = date('Y-m-d', strtotime('-7 days'));
        $this_month = date('Y-m-d', strtotime('-30 days'));
        
        return [
            'today' => Db::Get('SELECT COUNT(DISTINCT ip) as count FROM {statistics_visits} WHERE DATE(visit_date) = ?', $today)['count'],
            'this_week' => Db::Get('SELECT COUNT(DISTINCT ip) as count FROM {statistics_visits} WHERE DATE(visit_date) >= ?', $this_week)['count'],
            'this_month' => Db::Get('SELECT COUNT(DISTINCT ip) as count FROM {statistics_visits} WHERE DATE(visit_date) >= ?', $this_month)['count']
        ];
    }

    /**
     * Afficher les statistiques publiques
     */
    public function displayPublicStats()
    {
        $stats = $this->getStatsData();
        $visitor_stats = $this->getVisitorStats();
        
        $html = '<div class="statistics-widget">';
        $html .= '<h3><i class="fa fa-chart-bar"></i> Statistiques du site</h3>';
        
        if (!empty($stats['today'])) {
            $html .= '<div class="row">';
            $html .= '<div class="col-md-3"><div class="stat-box"><strong>Aujourd\'hui</strong><br>Visiteurs: ' . $stats['today']['visitors'] . '</div></div>';
            $html .= '<div class="col-md-3"><div class="stat-box"><strong>Pages vues</strong><br>' . $stats['today']['page_views'] . '</div></div>';
            $html .= '<div class="col-md-3"><div class="stat-box"><strong>Visiteurs uniques</strong><br>' . $stats['today']['unique_visitors'] . '</div></div>';
            $html .= '<div class="col-md-3"><div class="stat-box"><strong>Total</strong><br>' . $stats['total_visits'] . ' visites</div></div>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Widget pour afficher les statistiques
     */
    public function Widget()
    {
        $stats = $this->getStatsData();
        $visitor_stats = $this->getVisitorStats();
        
        $html = '<div class="statistics-widget card">';
        $html .= '<div class="card-header"><h5><i class="fa fa-chart-bar"></i> Statistiques</h5></div>';
        $html .= '<div class="card-body">';
        
        if (!empty($stats['today'])) {
            $html .= '<div class="row text-center">';
            $html .= '<div class="col-6"><small>Aujourd\'hui</small><br><strong>' . $stats['today']['visitors'] . '</strong></div>';
            $html .= '<div class="col-6"><small>Pages vues</small><br><strong>' . $stats['today']['page_views'] . '</strong></div>';
            $html .= '</div>';
        }
        
        $html .= '<div class="mt-2"><small>Total: ' . $stats['total_visits'] . ' visites</small></div>';
        $html .= '</div></div>';
        
        return $html;
    }

    /**
     * Supprimer les tables (optionnel)
     */
    private function dropStatsTables()
    {
        if (Db::TableExists('statistics')) {
            Db::DropTable('statistics');
        }
        if (Db::TableExists('page_statistics')) {
            Db::DropTable('page_statistics');
        }
        if (Db::TableExists('statistics_visits')) {
            Db::DropTable('statistics_visits');
        }
    }

    /**
     * Diagnostic des tables (pour le débogage)
     */
    public function diagnoseTables()
    {
        $diagnosis = [];
        $tables = ['statistics', 'page_statistics', 'statistics_visits'];
        
        foreach ($tables as $table) {
            $diagnosis[$table] = [
                'exists' => Db::TableExists($table),
                'columns' => []
            ];
            
            if ($diagnosis[$table]['exists']) {
                try {
                    $columns = Db::QueryAll("PRAGMA table_info({$table})");
                    $diagnosis[$table]['columns'] = $columns;
                } catch (Exception $e) {
                    $diagnosis[$table]['error'] = $e->getMessage();
                }
            }
        }
        
        return $diagnosis;
    }
};
