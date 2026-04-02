<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-tachometer-alt"></i> Monitoring des Performances Evo-TSM
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

                    <!-- Métriques de performance -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h3><?= $performance_stats['execution_time'] ?>ms</h3>
                                    <p class="mb-0">Temps d'exécution</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h3><?= $performance_stats['memory_usage'] ?>KB</h3>
                                    <p class="mb-0">Utilisation mémoire</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h3><?= $performance_stats['optimization_score'] ?>%</h3>
                                    <p class="mb-0">Score d'optimisation</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h3><?= $performance_stats['cache_hits'] ?></h3>
                                    <p class="mb-0">Cache hits</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions d'optimisation -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-rocket"></i> Optimisations Rapides
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="?p=Evo-TSM/performance&action=optimize_assets" 
                                           class="btn btn-primary" 
                                           onclick="return confirm('Optimiser tous les assets ?')">
                                            <i class="fas fa-compress"></i> Optimiser les Assets
                                        </a>
                                        
                                        <a href="?p=Evo-TSM/performance&action=clear_all_cache" 
                                           class="btn btn-warning" 
                                           onclick="return confirm('Vider tout le cache ?')">
                                            <i class="fas fa-trash"></i> Vider le Cache
                                        </a>
                                        
                                        <a href="?p=Evo-TSM/performance" 
                                           class="btn btn-info">
                                            <i class="fas fa-sync"></i> Actualiser les Métriques
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-chart-line"></i> Recommandations
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if ($performance_stats['execution_time'] > 100): ?>
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            Temps d'exécution élevé. Considérez l'optimisation du cache.
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($performance_stats['memory_usage'] > 1000): ?>
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            Utilisation mémoire élevée. Vérifiez les requêtes de base de données.
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($performance_stats['optimization_score'] > 80): ?>
                                        <div class="alert alert-success">
                                            <i class="fas fa-check-circle"></i>
                                            Excellentes performances ! Le module est bien optimisé.
                                        </div>
                                    <?php elseif ($performance_stats['optimization_score'] > 60): ?>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i>
                                            Bonnes performances. Quelques optimisations mineures possibles.
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-danger">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            Performances faibles. Optimisation recommandée.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Détails techniques -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-cogs"></i> Détails Techniques
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Configuration du Cache</h6>
                                    <ul class="list-unstyled">
                                        <li><strong>TTL Tickets :</strong> <?= evo_tsm_get_config('cache.ticket_counts_ttl', 60) ?>s</li>
                                        <li><strong>TTL Scores :</strong> <?= evo_tsm_get_config('cache.global_score_ttl', 300) ?>s</li>
                                        <li><strong>TTL Messages :</strong> <?= evo_tsm_get_config('cache.contact_messages_ttl', 300) ?>s</li>
                                        <li><strong>Cache activé :</strong> <?= evo_tsm_get_config('performance.enable_caching', true) ? 'Oui' : 'Non' ?></li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6>Statistiques Système</h6>
                                    <ul class="list-unstyled">
                                        <li><strong>PHP Version :</strong> <?= PHP_VERSION ?></li>
                                        <li><strong>Mémoire limit :</strong> <?= ini_get('memory_limit') ?></li>
                                        <li><strong>Max execution time :</strong> <?= ini_get('max_execution_time') ?>s</li>
                                        <li><strong>OPcache :</strong> <?= extension_loaded('Zend OPcache') ? 'Activé' : 'Désactivé' ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
