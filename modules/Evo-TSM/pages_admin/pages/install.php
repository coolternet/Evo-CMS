<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-cog"></i> Installation du Système de Cache Evo-TSM
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

                    <!-- Informations sur le système de cache -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5><i class="fas fa-info-circle"></i> À propos du Cache</h5>
                                    <p class="mb-0">Le système de cache Evo-TSM améliore les performances en stockant temporairement les données fréquemment utilisées.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5><i class="fas fa-rocket"></i> Avantages</h5>
                                    <ul class="mb-0">
                                        <li>Réduction des requêtes DB</li>
                                        <li>Amélioration des performances</li>
                                        <li>Interface de gestion intégrée</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bouton d'installation -->
                    <?php if (!$action): ?>
                        <div class="text-center mb-4">
                            <a href="?p=Evo-TSM/install&action=install" 
                               class="btn btn-primary btn-lg" 
                               onclick="return confirm('Installer le système de cache ?')">
                                <i class="fas fa-download"></i> Installer le Système de Cache
                            </a>
                        </div>
                    <?php endif; ?>

                    <!-- Résultats de l'installation -->
                    <?php if (!empty($results)): ?>
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-list-check"></i> Résultats de l'Installation
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    <?php foreach ($results as $result): ?>
                                        <div class="list-group-item">
                                            <?= $result ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Fonctionnalités du cache -->
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body text-center">
                                    <i class="fas fa-database fa-3x text-primary mb-3"></i>
                                    <h5>Stockage Intelligent</h5>
                                    <p class="text-muted">Utilise la table {cache} native d'EvoCMS avec fallback statique.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body text-center">
                                    <i class="fas fa-clock fa-3x text-warning mb-3"></i>
                                    <h5>TTL Configurable</h5>
                                    <p class="text-muted">Durée de vie des données en cache personnalisable par type.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body text-center">
                                    <i class="fas fa-sync fa-3x text-info mb-3"></i>
                                    <h5>Auto-invalidation</h5>
                                    <p class="text-muted">Le cache se met à jour automatiquement lors des modifications.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Types de cache -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-layer-group"></i> Types de Cache Intégrés
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="fas fa-ticket-alt text-warning"></i> Cache des Tickets</h6>
                                    <ul>
                                        <li>Compteurs de tickets (ouverts, fermés, critiques)</li>
                                        <li>Listes de tickets récents</li>
                                        <li>Statistiques globales</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-star text-info"></i> Cache des Scores</h6>
                                    <ul>
                                        <li>Moyennes des évaluations</li>
                                        <li>Statistiques de notation</li>
                                        <li>Scores globaux par module</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-envelope text-secondary"></i> Cache des Messages</h6>
                                    <ul>
                                        <li>Compteur de messages de contact</li>
                                        <li>Listes de messages récents</li>
                                        <li>Statistiques de contact</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-cog text-primary"></i> Cache Système</h6>
                                    <ul>
                                        <li>Informations des modules</li>
                                        <li>Configuration système</li>
                                        <li>Métadonnées</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions après installation -->
                    <?php if ($action === 'install' && $message_type === 'success'): ?>
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-check-circle text-success"></i> Installation Terminée
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="text-success">Le système de cache est maintenant opérationnel !</p>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                    <a href="?p=Evo-TSM/cache" class="btn btn-primary">
                                        <i class="fas fa-database"></i> Gérer le Cache
                                    </a>
                                    <a href="?p=Evo-TSM/home" class="btn btn-success">
                                        <i class="fas fa-home"></i> Retour à l'Accueil
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>