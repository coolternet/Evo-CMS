<div class="container">
    <!-- Indicateur de cache -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info d-flex justify-content-between align-items-center" role="alert">
                <div>
                    <i class="fas fa-database"></i>
                    <strong>Système de Cache Evo-TSM</strong> - 
                    <span id="cache-status">Vérification...</span>
                </div>
                <div>
                    <a href="?p=Evo-TSM/cache" class="btn btn-sm btn-outline-info me-2">
                        <i class="fas fa-cog"></i> Gérer
                    </a>
                    <a href="?p=Evo-TSM/install" class="btn btn-sm btn-outline-warning">
                        <i class="fas fa-download"></i> Installer
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div id="tickets_stats_bloc" class="row">
        <a href="?p=Evo-TSM/tickets&state=completed" class="col-sm-12 col-md-3 col-lg-3">
            <div class="card completed">
                <div class="card-body">
                    <span class="icon-background fa fa-lock"></span>
                    <div class="card-text ui four statistics">
                        <div class="statistic">
                            <h1 class="text-end"><?= $ticket_counts['closed'] ?></h1>
                            <div class="label text-end"><?= __('Evo-TSM/tss_home.completed'); ?></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-muted small">
                    <span><?= __('Evo-TSM/tss_home.details'); ?></span>
                    <span class="right"><i class="fas fa-arrow-circle-right"></i></span>
                </div>
            </div>
        </a>
        <a href="?p=Evo-TSM/tickets&state=critical" class="col-sm-12 col-md-3 col-lg-3">
            <div class="card critical">
                <div class="card-body">
                    <span class="icon-background fa fa-exclamation-circle"></span>
                    <div class="card-text ui four statistics">
                        <div class="statistic">
                            <h1 class="text-end"><?= $ticket_counts['critical'] ?></h1>
                            <div class="label text-end"><?= __('Evo-TSM/tss_home.critical'); ?></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-muted small">
                    <span><?= __('Evo-TSM/tss_home.details'); ?></span>
                    <span class="right"><i class="fas fa-arrow-circle-right"></i></span>
                </div>
            </div>
        </a>
        <a href="?p=Evo-TSM/tickets&state=opened" class="col-sm-12 col-md-3 col-lg-3">
            <div class="card inprogress">
                <div class="card-body">
                    <span class="icon-background fa fa-lock-open"></span>
                    <div class="card-text ui four statistics">
                        <div class="statistic">
                            <h1 class="text-end"><?= $ticket_counts['open'] ?></h1>
                            <div class="label text-end"><?= __('Evo-TSM/tss_home.open'); ?></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-muted small">
                    <span><?= __('Evo-TSM/tss_home.details'); ?></span>
                    <span class="right"><i class="fas fa-arrow-circle-right"></i></span>
                </div>
            </div>
        </a>
        <a href="?p=Evo-TSM/tickets&state=unassigned" class="col-sm-12 col-md-3 col-lg-3">
            <div class="card unassigned">
                <div class="card-body">
                    <span class="icon-background fa fa-user-times"></span>
                    <div class="card-text ui four statistics">
                        <div class="statistic">
                            <h1 class="text-end"><?= $ticket_counts['unassigned'] ?></h1>
                            <div class="label text-end"><?= __('Evo-TSM/tss_home.unassigned'); ?></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-muted small">
                    <span><?= __('Evo-TSM/tss_home.details'); ?></span>
                    <span class="right"><i class="fas fa-arrow-circle-right"></i></span>
                </div>
            </div>
        </a>
    </div>

    <div id="tickets_list_bloc" class="row">
        <div class="col-sm-12 col-md-3 col-lg-3">
            <div class="card">
                <div class="ui statistic" style="margin-bottom: 0;margin-top: 30px;text-align: center;font-weight: 300;">
                    <h1><?= $ticket_counts['total'] ?></h1>
                    <div class="text-muted">
                        <?= __('Evo-TSM/tss_home.total'); ?>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="myChart" width="400" height="400"></canvas>
                    <script>
                        /**
                         * Evo-TSM Dashboard - Initialisation
                         * Gestion de l'interface utilisateur et des graphiques
                         */
                        
                        // Configuration des données du graphique
                        window.ticketChartData = {
                            open: <?= $ticket_counts['open'] ?>,
                            close: <?= $ticket_counts['closed'] ?>,
                            unassigned: <?= $ticket_counts['unassigned'] ?>
                        };

                        // Configuration des données du cache
                        window.cacheData = <?= json_encode($cache_data) ?>;

                        /**
                         * Initialisation du statut du cache
                         * Met à jour l'interface avec les données PHP
                         */
                        (function initCacheStatus() {
                            const elements = {
                                status: document.getElementById('cache-status'),
                                entries: document.getElementById('cache-entries'),
                                statusBadge: document.getElementById('cache-status-badge'),
                                performance: document.getElementById('cache-performance')
                            };
                            
                            // Mise à jour de l'alerte principale
                            if (elements.status) {
                                elements.status.innerHTML = `
                                    <i class="<?= $cache_data['icon'] ?> text-<?= $cache_data['color'] ?>"></i> 
                                    <?= $cache_data['message'] ?>
                                `;
                                const alertElement = elements.status.parentElement.parentElement;
                                alertElement.className = `alert alert-<?= $cache_data['color'] ?> d-flex justify-content-between align-items-center`;
                            }
                            
                            // Mise à jour des widgets
                            if (elements.entries) {
                                elements.entries.textContent = '<?= $cache_data['entries'] ?>';
                            }
                            
                            if (elements.statusBadge) {
                                elements.statusBadge.textContent = '<?= $cache_data['status'] === 'active' ? 'Actif' : 'Inactif' ?>';
                                elements.statusBadge.className = 'text-<?= $cache_data['color'] ?>';
                            }
                            
                            if (elements.performance) {
                                elements.performance.textContent = '<?= $cache_data['status'] === 'active' ? 'Optimisé' : 'Standard' ?>';
                                elements.performance.className = 'text-<?= $cache_data['color'] ?>';
                            }
                        })();
                        
                        /**
                         * Initialisation du graphique Chart.js
                         * Crée un graphique en donut pour les statistiques des tickets
                         */
                        function initChart() {
                            if (typeof Chart === 'undefined') {
                                console.warn('EvoTSM: Chart.js non disponible');
                                return;
                            }
                            
                            const ctx = document.getElementById('myChart');
                            if (!ctx) {
                                console.warn('EvoTSM: Canvas myChart non trouvé');
                                return;
                            }
                            
                            try {
                                new Chart(ctx, {
                                    type: 'doughnut',
                                    data: {
                                        labels: ['Ouverts', 'Fermés', 'Non assignés'],
                                        datasets: [{
                                            data: [
                                                window.ticketChartData.open,
                                                window.ticketChartData.close,
                                                window.ticketChartData.unassigned
                                            ],
                                            backgroundColor: [
                                                'rgba(54, 162, 235, 0.2)',
                                                'rgba(75, 192, 192, 0.2)',
                                                'rgba(153, 102, 255, 0.2)'
                                            ],
                                            borderColor: [
                                                'rgba(54, 162, 235, 1)',
                                                'rgba(75, 192, 192, 1)',
                                                'rgba(153, 102, 255, 1)'
                                            ],
                                            borderWidth: 2
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: true,
                                        aspectRatio: 1,
                                        plugins: {
                                            legend: {
                                                position: 'bottom',
                                                labels: {
                                                    padding: 20,
                                                    usePointStyle: true
                                                }
                                            },
                                            title: {
                                                display: true,
                                                text: 'Statistiques des Tickets',
                                                font: {
                                                    size: 16,
                                                    weight: 'bold'
                                                }
                                            }
                                        },
                                        elements: {
                                            arc: {
                                                borderWidth: 2
                                            }
                                        }
                                    }
                                });
                            } catch (error) {
                                console.error('EvoTSM: Erreur lors de la création du graphique', error);
                            }
                        }
                        
                        // Initialiser le graphique
                        initChart();
                        
                        // Test pour vérifier que les fonctions de tickets sont chargées
                        console.log('Fonctions de tickets disponibles:', {
                            closeTicket: typeof closeTicket,
                            deleteTicket: typeof deleteTicket
                        });
                    </script>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-9 col-lg-9">
            <!-- Widget de cache -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-database"></i> Statut du Cache
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center m-0">
                        <div class="col-md-4">
                            <div class="border-end">
                                <h5 id="cache-entries" class="text-primary">-</h5>
                                <small class="text-muted">Entrées</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border-end">
                                <h5 id="cache-status-badge" class="text-success">-</h5>
                                <small class="text-muted">Statut</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border-end">
                                <h5 id="cache-performance" class="text-info">-</h5>
                                <small class="text-muted">Performance</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-star"></i> <?= __('Evo-TSM/tss_home.rates_stats'); ?>
                    </h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <?php 
                    $rates_stats = get_ticket_rates_stats();
                    if (!empty($rates_stats)) : 
                    ?>
                        <div class="list-group list-group-flush">
                            <?php foreach($rates_stats as $rate) : ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between mb-2">
                                        <div>
                                            <h6 class="mb-0">
                                                <strong>Client:</strong> <?= htmlspecialchars($rate['client_name'] ?? 'Client inconnu') ?>
                                            </h6>
                                            <small class="text-muted">
                                                <strong>Responsable:</strong> <?= htmlspecialchars($rate['username'] ?? 'Utilisateur inconnu') ?>
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted d-block"><?= date('d/m/Y H:i', strtotime($rate['send_date'])) ?></small>
                                            <div class="ui star rating" data-rating="<?= $rate['score'] ?>" data-max-rating="5" data-interactive="false"></div>
                                        </div>
                                    </div>
                                    
                                    <!-- Div en bas : Commentaire seulement -->
                                    <div>
                                        <?php if (!empty($rate['comment'])) : ?>
                                            <p class="mb-0 text-muted">
                                                <i class="fas fa-quote-left"></i> 
                                                <?= htmlspecialchars($rate['comment']) ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle"></i> 
                            <?= __('Evo-TSM/tss_home.no_rates'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-body" style="padding:0px;">
                    <table class="table table-hover card-footer text-muted small">
                        <thead>
                            <tr class="table-dark">
                                <th><?= __('Evo-TSM/tss_table.id'); ?></th>
                                <th><?= __('Evo-TSM/tss_table.from'); ?></th>
                                <th><?= __('Evo-TSM/tss_table.start_date'); ?></th>
                                <th><?= __('Evo-TSM/tss_table.assigned'); ?></th>
                                <th><?= __('Evo-TSM/tss_table.state'); ?></th>
                                <th class="center"><?= __('Evo-TSM/tss_table.action'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($ticket_counts['open'] > 0) : ?>
                                <?php foreach($get_open AS $key => $val) : ?>
                                    <tr>
                                        <th><?= $val["id"] ?></th>
                                        <td><a href="/admin/?page=user_view&id=<?= $val['sid']; ?>" target="_blank"><?= $val["account"] ?></a></td>
                                        <td><?= $val["create_date"] ?></td>
                                        <td><?= $val["moderator"] ?: 'Non assigné' ?></td>
                                        <td>Answered</td>
                                        <td class="center">
                                            <div class="btn-group" role="group">
                                                <a href="/admin/?p=Evo-TSM/view&id=<?= $val["id"] ?>" class="btn btn-sm btn-outline-primary" title="Voir le ticket">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <button class="btn btn-sm btn-outline-success" onclick="closeTicket(<?= $val["id"] ?>)" title="Fermer le ticket">
                                                    <i class="fa fa-lock"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteTicket(<?= $val["id"] ?>)" title="Supprimer le ticket">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                            <tr>
                                <td colspan="6">
                                    <div class="alert alert-primary center" role="alert"><?= __('Evo-TSM/tss_home.no_topen'); ?></div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
