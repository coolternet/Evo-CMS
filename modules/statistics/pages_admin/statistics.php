<?php
defined('EVO') or die;

// Vérifier les permissions
if (!has_permission('modules.statistics.admin')) {
    die('Accès refusé à l\'administration des statistiques.');
}

$module = App::getModule('statistics');
$stats = $module->getStatsData();
$page_stats = $module->getPageStats();
$visitor_stats = $module->getVisitorStats();

// Gestion des actions
if (IS_POST) {
    if (App::POST('action') === 'clear_stats') {
        if (has_permission('modules.statistics.delete')) {
            // Nettoyer les anciennes données
            $retention_days = $module->getConfig('retention_days', 90);
            $cutoff_date = date('Y-m-d', strtotime("-{$retention_days} days"));
            
            Db::Exec('DELETE FROM {statistics_visits} WHERE visit_date < ?', $cutoff_date);
            Db::Exec('DELETE FROM {statistics} WHERE date < ?', $cutoff_date);
            
            App::setNotice("Données de statistiques nettoyées avec succès !");
        }
    }
    
    if (App::POST('action') === 'export_csv') {
        if (has_permission('modules.statistics.export')) {
            $this->exportStatsCSV();
        }
    }
}

// Fonction d'export CSV
function exportStatsCSV() {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=statistics_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    
    // En-têtes
    fputcsv($output, ['Date', 'Visiteurs', 'Pages vues', 'Visiteurs uniques', 'Taux de rebond', 'Temps moyen']);
    
    // Données des 30 derniers jours
    $stats = Db::QueryAll('SELECT * FROM {statistics} WHERE date >= ? ORDER BY date DESC LIMIT 30', 
        date('Y-m-d', strtotime('-30 days')));
    
    foreach ($stats as $stat) {
        fputcsv($output, [
            $stat['date'],
            $stat['visitors'],
            $stat['page_views'],
            $stat['unique_visitors'],
            $stat['bounce_rate'],
            $stat['avg_time']
        ]);
    }
    
    fclose($output);
    exit;
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1><i class="fa fa-chart-bar"></i> Tableau de bord des statistiques</h1>
            <p class="text-muted">Suivi complet des performances de votre site web</p>
        </div>
    </div>

    <!-- Statistiques générales -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3><?= $stats['today']['visitors'] ?? 0 ?></h3>
                    <p class="mb-0">Visiteurs aujourd'hui</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3><?= $stats['today']['page_views'] ?? 0 ?></h3>
                    <p class="mb-0">Pages vues aujourd'hui</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3><?= $stats['today']['unique_visitors'] ?? 0 ?></h3>
                    <p class="mb-0">Visiteurs uniques</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h3><?= $stats['total_visits'] ?? 0 ?></h3>
                    <p class="mb-0">Total des visites</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphique des 7 derniers jours -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fa fa-chart-line"></i> Évolution des 7 derniers jours</h5>
                </div>
                <div class="card-body">
                    <canvas id="weeklyChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Pages les plus populaires -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fa fa-star"></i> Pages les plus populaires</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Page</th>
                                    <th>Titre</th>
                                    <th>Visites</th>
                                    <th>Visites uniques</th>
                                    <th>Dernière visite</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($page_stats as $page): ?>
                                <tr>
                                    <td><code><?= html_encode($page['page_url']) ?></code></td>
                                    <td><?= html_encode($page['page_title']) ?></td>
                                    <td><span class="badge badge-primary"><?= $page['visits'] ?></span></td>
                                    <td><span class="badge badge-info"><?= $page['unique_visits'] ?></span></td>
                                    <td><small><?= date('d/m/Y H:i', strtotime($page['last_visit'])) ?></small></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques des visiteurs -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fa fa-users"></i> Visiteurs</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Aujourd'hui
                            <span class="badge badge-primary badge-pill"><?= $visitor_stats['today'] ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Cette semaine
                            <span class="badge badge-info badge-pill"><?= $visitor_stats['this_week'] ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Ce mois
                            <span class="badge badge-success badge-pill"><?= $visitor_stats['this_month'] ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5><i class="fa fa-cogs"></i> Actions</h5>
                </div>
                <div class="card-body">
                    <form method="post" class="mb-2">
                        <input type="hidden" name="action" value="export_csv">
                        <button type="submit" class="btn btn-outline-primary btn-sm btn-block">
                            <i class="fa fa-download"></i> Exporter CSV
                        </button>
                    </form>
                    
                    <?php if (has_permission('modules.statistics.delete')): ?>
                    <form method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir nettoyer les anciennes données ?')">
                        <input type="hidden" name="action" value="clear_stats">
                        <button type="submit" class="btn btn-outline-warning btn-sm btn-block">
                            <i class="fa fa-trash"></i> Nettoyer les données
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Configuration du module -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fa fa-cog"></i> Configuration du module</h5>
                </div>
                <div class="card-body">
                    <?php
                    $settings = [
                        'track_bots' => 'Suivre les robots',
                        'track_user_agents' => 'Enregistrer les User-Agents',
                        'retention_days' => 'Durée de rétention',
                        'auto_cleanup' => 'Nettoyage automatique',
                        'privacy_mode' => 'Mode confidentialité'
                    ];
                    
                    foreach ($settings as $key => $label):
                        $value = $module->getConfig($key);
                        $type = gettype($value);
                    ?>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label"><?= $label ?></label>
                        <div class="col-sm-9">
                            <?php if ($type === 'boolean'): ?>
                                <span class="badge badge-<?= $value ? 'success' : 'secondary' ?>">
                                    <?= $value ? 'Activé' : 'Désactivé' ?>
                                </span>
                            <?php else: ?>
                                <code><?= html_encode($value) ?></code>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script pour le graphique -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('weeklyChart').getContext('2d');
    
    // Données pour le graphique
    const weeklyData = <?= json_encode($stats['weekly']) ?>;
    const labels = weeklyData.map(item => item.date).reverse();
    const visitors = weeklyData.map(item => item.visitors).reverse();
    const pageViews = weeklyData.map(item => item.page_views).reverse();
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Visiteurs',
                data: visitors,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }, {
                label: 'Pages vues',
                data: pageViews,
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>

<style>
.statistics-widget .stat-box {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    text-align: center;
    margin-bottom: 15px;
}

.statistics-widget .stat-box strong {
    color: #007bff;
    font-size: 1.2em;
}
</style>
