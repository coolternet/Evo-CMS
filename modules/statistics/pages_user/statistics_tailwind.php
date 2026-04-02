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

<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- En-tête -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4 flex items-center justify-center">
                <svg class="w-10 h-10 text-primary mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Statistiques du site
            </h1>
            <p class="text-xl text-gray-600">Découvrez les performances et l'activité de notre site web</p>
        </div>

        <!-- Statistiques du jour -->
        <div class="mb-12">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-200">
                    <h2 class="text-2xl font-semibold text-gray-900 flex items-center">
                        <svg class="w-6 h-6 text-primary mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Aujourd'hui
                    </h2>
                </div>
                <div class="p-8">
                    <?php if (!empty($stats['today'])): ?>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <div class="text-center">
                            <div class="stat-number text-5xl font-bold text-primary mb-3"><?= $stats['today']['visitors'] ?></div>
                            <div class="stat-label text-gray-600 font-medium uppercase tracking-wide">Visiteurs</div>
                        </div>
                        <div class="text-center">
                            <div class="stat-number text-5xl font-bold text-success mb-3"><?= $stats['today']['page_views'] ?></div>
                            <div class="stat-label text-gray-600 font-medium uppercase tracking-wide">Pages vues</div>
                        </div>
                        <div class="text-center">
                            <div class="stat-number text-5xl font-bold text-info mb-3"><?= $stats['today']['unique_visitors'] ?></div>
                            <div class="stat-label text-gray-600 font-medium uppercase tracking-wide">Visiteurs uniques</div>
                        </div>
                        <div class="text-center">
                            <div class="stat-number text-5xl font-bold text-warning mb-3"><?= $stats['total_visits'] ?></div>
                            <div class="stat-label text-gray-600 font-medium uppercase tracking-wide">Total des visites</div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <p class="text-gray-500 text-lg">Aucune donnée disponible pour aujourd'hui.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Graphique de la semaine -->
        <div class="mb-12">
            <div class="bg-white rounded-2xl shadow-xl">
                <div class="px-8 py-6 border-b border-gray-200">
                    <h2 class="text-2xl font-semibold text-gray-900 flex items-center">
                        <svg class="w-6 h-6 text-primary mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                        </svg>
                        Activité de la semaine
                    </h2>
                </div>
                <div class="p-8">
                    <canvas id="weeklyChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Contenu principal -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
            <!-- Pages populaires -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-xl">
                    <div class="px-8 py-6 border-b border-gray-200">
                        <h2 class="text-2xl font-semibold text-gray-900 flex items-center">
                            <svg class="w-6 h-6 text-warning mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                            Pages les plus visitées
                        </h2>
                    </div>
                    <div class="p-8">
                        <?php if (!empty($page_stats)): ?>
                        <div class="space-y-4">
                            <?php foreach (array_slice($page_stats, 0, 5) as $index => $page): ?>
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                                <div class="flex items-center space-x-4">
                                    <span class="flex-shrink-0 w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center text-sm font-bold">#<?= $index + 1 ?></span>
                                    <div>
                                        <h3 class="font-semibold text-gray-900"><?= html_encode($page['page_title']) ?></h3>
                                        <p class="text-sm text-gray-500"><?= html_encode($page['page_url']) ?></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-bold text-success"><?= $page['visits'] ?> visites</div>
                                    <div class="text-sm text-gray-500"><?= $page['unique_visits'] ?> uniques</div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-gray-500 text-lg">Aucune donnée de page disponible.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar droite -->
            <div class="space-y-8">
                <!-- Statistiques des visiteurs -->
                <div class="bg-white rounded-2xl shadow-xl">
                    <div class="px-6 py-6 border-b border-gray-200">
                        <h3 class="text-xl font-semibold text-gray-900 flex items-center">
                            <svg class="w-5 h-5 text-info mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                            Visiteurs
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
                                <span class="text-gray-700 font-medium">Aujourd'hui</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary text-white"><?= $visitor_stats['today'] ?></span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-cyan-50 rounded-lg">
                                <span class="text-gray-700 font-medium">Cette semaine</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-info text-white"><?= $visitor_stats['this_week'] ?></span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                                <span class="text-gray-700 font-medium">Ce mois</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-success text-white"><?= $visitor_stats['this_month'] ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informations -->
                <div class="bg-white rounded-2xl shadow-xl">
                    <div class="px-6 py-6 border-b border-gray-200">
                        <h3 class="text-xl font-semibold text-gray-900 flex items-center">
                            <svg class="w-5 h-5 text-secondary mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            À propos
                        </h3>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600 leading-relaxed">
                            Ces statistiques sont mises à jour en temps réel et reflètent l'activité réelle de notre site web.
                            Les données sont anonymisées pour respecter votre vie privée.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques détaillées -->
        <div class="bg-white rounded-2xl shadow-xl">
            <div class="px-8 py-6 border-b border-gray-200">
                <h2 class="text-2xl font-semibold text-gray-900 flex items-center">
                    <svg class="w-6 h-6 text-secondary mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
                    </svg>
                    Statistiques détaillées
                </h2>
            </div>
            <div class="p-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Comparaison avec hier -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Comparaison avec hier</h3>
                        <?php if (!empty($stats['yesterday'])): ?>
                        <div class="bg-gray-50 rounded-xl p-6">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-700 font-medium">Visiteurs</span>
                                    <div class="flex items-center space-x-4">
                                        <span class="text-gray-500"><?= $stats['yesterday']['visitors'] ?? 0 ?></span>
                                        <span class="text-gray-900 font-semibold"><?= $stats['today']['visitors'] ?? 0 ?></span>
                                        <?php
                                        if (!empty($stats['yesterday']['visitors']) && !empty($stats['today']['visitors'])) {
                                            $change = (($stats['today']['visitors'] - $stats['yesterday']['visitors']) / $stats['yesterday']['visitors']) * 100;
                                            $badge_class = $change >= 0 ? 'bg-success text-white' : 'bg-danger text-white';
                                            $change_text = $change >= 0 ? '+' . number_format($change, 1) : number_format($change, 1);
                                            echo "<span class='inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {$badge_class}'>{$change_text}%</span>";
                                        } else {
                                            echo "<span class='text-gray-400'>-</span>";
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-700 font-medium">Pages vues</span>
                                    <div class="flex items-center space-x-4">
                                        <span class="text-gray-500"><?= $stats['yesterday']['page_views'] ?? 0 ?></span>
                                        <span class="text-gray-900 font-semibold"><?= $stats['today']['page_views'] ?? 0 ?></span>
                                        <?php
                                        if (!empty($stats['yesterday']['page_views']) && !empty($stats['today']['page_views'])) {
                                            $change = (($stats['today']['page_views'] - $stats['yesterday']['page_views']) / $stats['yesterday']['page_views']) * 100;
                                            $badge_class = $change >= 0 ? 'bg-success text-white' : 'bg-danger text-white';
                                            $change_text = $change >= 0 ? '+' . number_format($change, 1) : number_format($change, 1);
                                            echo "<span class='inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {$badge_class}'>{$change_text}%</span>";
                                        } else {
                                            echo "<span class='text-gray-400'>-</span>";
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-8">
                            <p class="text-gray-500">Aucune donnée de comparaison disponible.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Résumé global -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Résumé global</h3>
                        <div class="bg-gray-50 rounded-xl p-6">
                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-700">Pages suivies</span>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-info text-white"><?= $stats['total_pages'] ?? 0 ?></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-700">Total des visites</span>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary text-white"><?= $stats['total_visits'] ?? 0 ?></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-700">Période de suivi</span>
                                    <span class="text-gray-500">7 jours</span>
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
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#3B82F6',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2
            }, {
                label: 'Pages vues',
                data: pageViews,
                borderColor: '#10B981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#10B981',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: {
                            size: 14
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        font: {
                            size: 12
                        }
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        font: {
                            size: 12
                        }
                    }
                }
            },
            elements: {
                point: {
                    radius: 6,
                    hoverRadius: 8
                }
            }
        }
    });
});
</script>
