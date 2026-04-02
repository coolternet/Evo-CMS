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
            exportStatsCSV();
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

<!-- Inclusion de Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: '#3B82F6',
                    secondary: '#6B7280',
                    success: '#10B981',
                    warning: '#F59E0B',
                    danger: '#EF4444',
                    info: '#06B6D4'
                }
            }
        }
    }
</script>

<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- En-tête -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                <svg class="w-8 h-8 text-primary mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Tableau de bord des statistiques
            </h1>
            <p class="mt-2 text-gray-600">Suivi complet des performances de votre site web</p>
        </div>

        <!-- Statistiques générales -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Visiteurs aujourd'hui -->
            <div class="bg-gradient-to-r from-primary to-blue-600 rounded-xl shadow-lg">
                <div class="p-6 text-center text-white">
                    <div class="text-3xl font-bold mb-2"><?= $stats['today']['visitors'] ?? 0 ?></div>
                    <div class="text-blue-100">Visiteurs aujourd'hui</div>
                </div>
            </div>

            <!-- Pages vues aujourd'hui -->
            <div class="bg-gradient-to-r from-success to-green-600 rounded-xl shadow-lg">
                <div class="p-6 text-center text-white">
                    <div class="text-3xl font-bold mb-2"><?= $stats['today']['page_views'] ?? 0 ?></div>
                    <div class="text-green-100">Pages vues aujourd'hui</div>
                </div>
            </div>

            <!-- Visiteurs uniques -->
            <div class="bg-gradient-to-r from-info to-cyan-600 rounded-xl shadow-lg">
                <div class="p-6 text-center text-white">
                    <div class="text-3xl font-bold mb-2"><?= $stats['today']['unique_visitors'] ?? 0 ?></div>
                    <div class="text-cyan-100">Visiteurs uniques</div>
                </div>
            </div>

            <!-- Total des visites -->
            <div class="bg-gradient-to-r from-warning to-yellow-600 rounded-xl shadow-lg">
                <div class="p-6 text-center text-white">
                    <div class="text-3xl font-bold mb-2"><?= $stats['total_visits'] ?? 0 ?></div>
                    <div class="text-yellow-100">Total des visites</div>
                </div>
            </div>
        </div>

        <!-- Graphique des 7 derniers jours -->
        <div class="bg-white rounded-xl shadow-lg mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h5 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                    </svg>
                    Évolution des 7 derniers jours
                </h5>
            </div>
            <div class="p-6">
                <canvas id="weeklyChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Contenu principal -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Pages les plus populaires -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h5 class="text-lg font-semibold text-gray-900 flex items-center">
                            <svg class="w-5 h-5 text-warning mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                            Pages les plus populaires
                        </h5>
                    </div>
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Page</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Titre</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visites</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visites uniques</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dernière visite</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($page_stats as $page): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <code class="text-sm bg-gray-100 px-2 py-1 rounded text-gray-800"><?= html_encode($page['page_url']) ?></code>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= html_encode($page['page_title']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary text-white"><?= $page['visits'] ?></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-info text-white"><?= $page['unique_visits'] ?></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('d/m/Y H:i', strtotime($page['last_visit'])) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar droite -->
            <div class="space-y-6">
                <!-- Statistiques des visiteurs -->
                <div class="bg-white rounded-xl shadow-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h5 class="text-lg font-semibold text-gray-900 flex items-center">
                            <svg class="w-5 h-5 text-info mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                            Visiteurs
                        </h5>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-700">Aujourd'hui</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary text-white"><?= $visitor_stats['today'] ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-700">Cette semaine</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-info text-white"><?= $visitor_stats['this_week'] ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-700">Ce mois</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success text-white"><?= $visitor_stats['this_month'] ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white rounded-xl shadow-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h5 class="text-lg font-semibold text-gray-900 flex items-center">
                            <svg class="w-5 h-5 text-secondary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Actions
                        </h5>
                    </div>
                    <div class="p-6 space-y-3">
                        <form method="post">
                            <input type="hidden" name="action" value="export_csv">
                            <button type="submit" class="w-full flex items-center justify-center px-4 py-2 border border-primary text-primary hover:bg-primary hover:text-white rounded-lg transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Exporter CSV
                            </button>
                        </form>
                        
                        <?php if (has_permission('modules.statistics.delete')): ?>
                        <form method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir nettoyer les anciennes données ?')">
                            <input type="hidden" name="action" value="clear_stats">
                            <button type="submit" class="w-full flex items-center justify-center px-4 py-2 border border-warning text-warning hover:bg-warning hover:text-white rounded-lg transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Nettoyer les données
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuration du module -->
        <div class="mt-8">
            <div class="bg-white rounded-xl shadow-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h5 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 text-secondary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Configuration du module
                    </h5>
                </div>
                <div class="p-6">
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
                    <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-b-0">
                        <label class="text-sm font-medium text-gray-700"><?= $label ?></label>
                        <div>
                            <?php if ($type === 'boolean'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $value ? 'bg-success text-white' : 'bg-gray-200 text-gray-800' ?>">
                                    <?= $value ? 'Activé' : 'Désactivé' ?>
                                </span>
                            <?php else: ?>
                                <code class="text-sm bg-gray-100 px-2 py-1 rounded text-gray-800"><?= html_encode($value) ?></code>
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
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Pages vues',
                data: pageViews,
                borderColor: '#10B981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                }
            }
        }
    });
});
</script>
