<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-envelope"></i> 
                        Messages de Contact 
                        <span class="badge badge-primary"><?= $message_count ?></span>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($messages)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            Aucun message de contact pour le moment.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Utilisateur</th>
                                        <th>Sujet</th>
                                        <th>Message</th>
                                        <th>Suivi</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($messages as $msg): ?>
                                        <tr>
                                            <td>
                                                <small class="text-muted">
                                                    <?= date('d/m/Y H:i', strtotime($msg['created_date'])) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($msg['username']) ?></strong><br>
                                                <small class="text-muted"><?= htmlspecialchars($msg['user_email']) ?></small>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($msg['subject']) ?></strong>
                                            </td>
                                            <td>
                                                <div class="message-preview" style="max-width: 300px;">
                                                    <?= htmlspecialchars(substr($msg['message'], 0, 100)) ?>
                                                    <?php if (strlen($msg['message']) > 100): ?>
                                                        <span class="text-muted">...</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($msg['followup']): ?>
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-flag"></i> Suivi demandé
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Normal</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary" 
                                                        data-toggle="modal" 
                                                        data-target="#messageModal<?= $msg['id'] ?>">
                                                    <i class="fas fa-eye"></i> Voir
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals pour afficher les messages complets -->
<?php foreach ($messages as $msg): ?>
    <div class="modal fade" id="messageModal<?= $msg['id'] ?>" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-envelope"></i> 
                        Message de <?= htmlspecialchars($msg['username']) ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Date:</strong> <?= date('d/m/Y H:i:s', strtotime($msg['created_date'])) ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Email:</strong> <?= htmlspecialchars($msg['user_email']) ?>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <strong>Sujet:</strong><br>
                            <p class="mb-3"><?= htmlspecialchars($msg['subject']) ?></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <strong>Message:</strong><br>
                            <div class="border p-3 bg-light">
                                <?= nl2br(htmlspecialchars($msg['message'])) ?>
                            </div>
                        </div>
                    </div>
                    <?php if ($msg['followup']): ?>
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <i class="fas fa-flag"></i> 
                                    <strong>Suivi demandé:</strong> L'utilisateur souhaite un suivi de ce message.
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>IP:</strong> <?= htmlspecialchars($msg['ip_address']) ?>
                        </div>
                        <div class="col-md-6">
                            <strong>User Agent:</strong><br>
                            <small class="text-muted"><?= htmlspecialchars($msg['user_agent']) ?></small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                    <a href="mailto:<?= htmlspecialchars($msg['user_email']) ?>?subject=Re: <?= urlencode($msg['subject']) ?>" 
                       class="btn btn-primary">
                        <i class="fas fa-reply"></i> Répondre par email
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
