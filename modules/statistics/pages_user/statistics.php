<?php
defined('EVO') or die;

// Vérifier les permissions
if (!has_permission('modules.statistics.view')) {
    die('Accès refusé aux statistiques.');
}

$module = App::getModule('statistics');
$stats = $module->getStatsData();
$page_stats = $module->getPageStats();
$visitor_stats = $module->getVisitorStats();
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <h1><i class="fa fa-chart-bar"></i> Statistiques du site</h1>
            <p class="text-muted">Découvrez les performances et l'activité de notre site web</p>
        </div>
    </div>

    <!-- Statistiques du jour -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fa fa-calendar-day"></i> Aujourd'hui</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($stats['today'])): ?>
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-number"><?= $stats['today']['visitors'] ?></div>
                                <div class="stat-label">Visiteurs</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-number"><?= $stats['today']['page_views'] ?></div>
                                <div class="stat-label">Pages vues</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-number"><?= $stats['today']['unique_visitors'] ?></div>
                                <div class="stat-label">Visiteurs uniques</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-number"><?= $stats['total_visits'] ?></div>
                                <div class="stat-label">Total des visites</div>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="text-center text-muted">
                        <p>Aucune donnée disponible pour aujourd'hui.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphique de la semaine -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fa fa-chart-line"></i> Activité de la semaine</h5>
                </div>
                <div class="card-body">
                    <canvas id="weeklyChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Pages populaires -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fa fa-star"></i> Pages les plus visitées</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($page_stats)): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach (array_slice($page_stats, 0, 5) as $index => $page): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge badge-primary mr-2">#<?= $index + 1 ?></span>
                                <strong><?= html_encode($page['page_title']) ?></strong>
                                <br><small class="text-muted"><?= html_encode($page['page_url']) ?></small>
                            </div>
                            <div class="text-right">
                                <span class="badge badge-success"><?= $page['visits'] ?> visites</span>
                                <br><small class="text-muted"><?= $page['unique_visits'] ?> uniques</small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center text-muted">
                        <p>Aucune donnée de page disponible.</p>
                    </div>
                    <?php endif; ?>
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
                            <span>Aujourd'hui</span>
                            <span class="badge badge-primary badge-pill"><?= $visitor_stats['today'] ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Cette semaine</span>
                            <span class="badge badge-info badge-pill"><?= $visitor_stats['this_week'] ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Ce mois</span>
                            <span class="badge badge-success badge-pill"><?= $visitor_stats['this_month'] ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5><i class="fa fa-info-circle"></i> À propos</h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted">
                        Ces statistiques sont mises à jour en temps réel et reflètent l'activité réelle de notre site web.
                        Les données sont anonymisées pour respecter votre vie privée.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques détaillées -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fa fa-chart-pie"></i> Statistiques détaillées</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Comparaison avec hier</h6>
                            <?php if (!empty($stats['yesterday'])): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Métrique</th>
                                            <th>Hier</th>
                                            <th>Aujourd'hui</th>
                                            <th>Évolution</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Visiteurs</td>
                                            <td><?= $stats['yesterday']['visitors'] ?? 0 ?></td>
                                            <td><?= $stats['today']['visitors'] ?? 0 ?></td>
                                            <td>
                                                <?php
                                                if (!empty($stats['yesterday']['visitors']) && !empty($stats['today']['visitors'])) {
                                                    $change = (($stats['today']['visitors'] - $stats['yesterday']['visitors']) / $stats['yesterday']['visitors']) * 100;
                                                    $badge_class = $change >= 0 ? 'success' : 'danger';
                                                    $change_text = $change >= 0 ? '+' . number_format($change, 1) : number_format($change, 1);
                                                    echo "<span class='badge badge-{$badge_class}'>{$change_text}%</span>";
                                                } else {
                                                    echo "<span class='text-muted'>-</span>";
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Pages vues</td>
                                            <td><?= $stats['yesterday']['page_views'] ?? 0 ?></td>
                                            <td><?= $stats['today']['page_views'] ?? 0 ?></td>
                                            <td>
                                                <?php
                                                if (!empty($stats['yesterday']['page_views']) && !empty($stats['today']['page_views'])) {
                                                    $change = (($stats['today']['page_views'] - $stats['yesterday']['page_views']) / $stats['yesterday']['page_views']) * 100;
                                                    $badge_class = $change >= 0 ? 'success' : 'danger';
                                                    $change_text = $change >= 0 ? '+' . number_format($change, 1) : number_format($change, 1);
                                                    echo "<span class='badge badge-{$badge_class}'>{$change_text}%</span>";
                                                } else {
                                                    echo "<span class='text-muted'>-</span>";
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <p class="text-muted">Aucune donnée de comparaison disponible.</p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-6">
                            <h6>Résumé global</h6>
                            <div class="list-group list-group-flush">
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Pages suivies</span>
                                    <span class="badge badge-info"><?= $stats['total_pages'] ?? 0 ?></span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Total des visites</span>
                                    <span class="badge badge-primary"><?= $stats['total_visits'] ?? 0 ?></span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Période de suivi</span>
                                    <span class="text-muted">7 jours</span>
                                </div>
                            </div>
                        </div>
                    </div>
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
    const labels = weeklyData.map(item => {
        const date = new Date(item.date);
        return date.toLocaleDateString('fr-FR', { weekday: 'short', day: 'numeric' });
    }).reverse();
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
                tension: 0.1,
                fill: true
            }, {
                label: 'Pages vues',
                data: pageViews,
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.1,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    }
                }
            }
        }
    });
});
</script>

<style>
.stat-item {
    padding: 20px;
    text-align: center;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: bold;
    color: #007bff;
    margin-bottom: 10px;
}

.stat-label {
    color: #6c757d;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

.list-group-item {
    border-left: none;
    border-right: none;
}

.badge {
    font-size: 0.8rem;
}
</style>
