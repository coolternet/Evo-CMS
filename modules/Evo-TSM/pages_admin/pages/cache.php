<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-database"></i> Gestion du Cache Evo-TSM
                    </h4>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-<?= $message_type === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                            <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-triangle' ?>"></i>
                            <?= htmlspecialchars($message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Statistiques du cache -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h3><?= $cache_stats['total_entries'] ?></h3>
                                    <p class="mb-0">Entrées totales</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h3><?= $cache_stats['ticket_entries'] ?></h3>
                                    <p class="mb-0">Cache Tickets</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h3><?= $cache_stats['score_entries'] ?></h3>
                                    <p class="mb-0">Cache Scores</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h3><?= $cache_stats['cache_size'] ?></h3>
                                    <p class="mb-0">Taille totale</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions de cache -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-trash"></i> Vider le Cache
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">Sélectionnez le type de cache à vider :</p>
                                    
                                    <div class="d-grid gap-2">
                                        <a href="?p=Evo-TSM/cache&action=clear_cache&type=all" 
                                           class="btn btn-danger" 
                                           onclick="return confirm('Êtes-vous sûr de vouloir vider tout le cache ?')">
                                            <i class="fas fa-trash"></i> Vider tout le cache
                                        </a>
                                        
                                        <a href="?p=Evo-TSM/cache&action=clear_cache&type=tickets" 
                                           class="btn btn-warning" 
                                           onclick="return confirm('Vider le cache des tickets ?')">
                                            <i class="fas fa-ticket-alt"></i> Vider le cache des tickets
                                        </a>
                                        
                                        <a href="?p=Evo-TSM/cache&action=clear_cache&type=scores" 
                                           class="btn btn-info" 
                                           onclick="return confirm('Vider le cache des scores ?')">
                                            <i class="fas fa-star"></i> Vider le cache des scores
                                        </a>
                                        
                                        <a href="?p=Evo-TSM/cache&action=clear_cache&type=contact" 
                                           class="btn btn-secondary" 
                                           onclick="return confirm('Vider le cache des messages de contact ?')">
                                            <i class="fas fa-envelope"></i> Vider le cache des messages
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-info-circle"></i> Informations
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <h6>Types de cache :</h6>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-ticket-alt text-warning"></i> <strong>Tickets :</strong> Compteurs et listes</li>
                                        <li><i class="fas fa-star text-info"></i> <strong>Scores :</strong> Évaluations et moyennes</li>
                                        <li><i class="fas fa-envelope text-secondary"></i> <strong>Messages :</strong> Messages de contact</li>
                                    </ul>
                                    
                                    <h6 class="mt-3">Configuration :</h6>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-clock text-primary"></i> <strong>TTL par défaut :</strong> 5 minutes</li>
                                        <li><i class="fas fa-database text-success"></i> <strong>Stockage :</strong> Table {cache}</li>
                                        <li><i class="fas fa-sync text-info"></i> <strong>Auto-invalidation :</strong> Activée</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Détails du cache -->
                    <?php if (isset($cache_stats['error'])): ?>
                        <div class="alert alert-warning mt-4">
                            <i class="fas fa-exclamation-triangle"></i>
                            <?= htmlspecialchars($cache_stats['error']) ?>
                        </div>
                    <?php else: ?>
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-list"></i> Détails du Cache
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Type</th>
                                                <th>Entrées</th>
                                                <th>Pourcentage</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><i class="fas fa-ticket-alt text-warning"></i> Tickets</td>
                                                <td><?= $cache_stats['ticket_entries'] ?></td>
                                                <td>
                                                    <?php 
                                                    $percentage = $cache_stats['total_entries'] > 0 ? 
                                                        round(($cache_stats['ticket_entries'] / $cache_stats['total_entries']) * 100, 1) : 0;
                                                    ?>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-warning" style="width: <?= $percentage ?>%">
                                                            <?= $percentage ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <a href="?p=Evo-TSM/cache&action=clear_cache&type=tickets" 
                                                       class="btn btn-sm btn-warning" 
                                                       onclick="return confirm('Vider le cache des tickets ?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><i class="fas fa-star text-info"></i> Scores</td>
                                                <td><?= $cache_stats['score_entries'] ?></td>
                                                <td>
                                                    <?php 
                                                    $percentage = $cache_stats['total_entries'] > 0 ? 
                                                        round(($cache_stats['score_entries'] / $cache_stats['total_entries']) * 100, 1) : 0;
                                                    ?>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-info" style="width: <?= $percentage ?>%">
                                                            <?= $percentage ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <a href="?p=Evo-TSM/cache&action=clear_cache&type=scores" 
                                                       class="btn btn-sm btn-info" 
                                                       onclick="return confirm('Vider le cache des scores ?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><i class="fas fa-envelope text-secondary"></i> Messages</td>
                                                <td><?= $cache_stats['contact_entries'] ?></td>
                                                <td>
                                                    <?php 
                                                    $percentage = $cache_stats['total_entries'] > 0 ? 
                                                        round(($cache_stats['contact_entries'] / $cache_stats['total_entries']) * 100, 1) : 0;
                                                    ?>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-secondary" style="width: <?= $percentage ?>%">
                                                            <?= $percentage ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <a href="?p=Evo-TSM/cache&action=clear_cache&type=contact" 
                                                       class="btn btn-sm btn-secondary" 
                                                       onclick="return confirm('Vider le cache des messages ?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>