<div class="container">
    <div id="tickets_stats_bloc" class="row">
        <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
            <div class="card">
                <div class="card-header text-muted small">
                    <span><?= __('Evo-TSM/tss_contact.prov_inf'); ?></span>
                </div>
                <div class="card-body">
                    <div class="ui list" style="margin: 0em 0em;">
                        <div class="item">
                            <small class="ui sub header"><i class="fa fa-globe"></i> <?= __('Evo-TSM/tss_contact.url'); ?></small>
                            <div class="content small"><?= $_SERVER["SERVER_NAME"] ?></div>
                        </div>
                        <div class="item">
                            <small class="ui sub header"><i class="fa fa-envelope"></i> <?= __('Evo-TSM/tss_contact.email'); ?></small>
                            <div class="content small"><?= App::getCurrentUser()->email ?></div>
                        </div>
                        <div class="item">
                            <small class="ui sub header"><i class="fas fa-tablet-alt"></i> <?= __('Evo-TSM/tss_contact.browser'); ?></small>
                            <div class="content small">
                                <?= \Widgets::userAgentIcons($useragent) ?>
                            </div>
                        </div>
                        <div class="item">
                            <small class="ui sub header"><i class="fas fa-map-marker"></i> <?= __('Evo-TSM/tss_contact.ip'); ?></small>
                            <div class="content small"><?= $_SERVER["SERVER_ADDR"] ?></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer card-header text-muted small">
                    <span><?= __('Evo-TSM/tss_contact.Soft_inf'); ?></span>
                </div>
                <div class="card-body">
                    <div class="ui list" style="margin: 0em 0em;">
                        <div class="item">
                            <small class="ui sub header"><i class="fas fa-laptop-code"></i> <?= __('Evo-TSM/tss_contact.phpv'); ?></small>
                            <div class="content small"><?= $phpver[0] ?></div>
                        </div>
                        <div class="item">
                            <small class="ui sub header"><i class="fas fa-database"></i> <?= __('Evo-TSM/tss_contact.mysqlv'); ?></small>
                            <div class="content small"><?= Db::ServerVersion() ?></div>
                        </div>
                        <div class="item">
                            <small class="ui sub header"><i class="fas fa-microchip"></i> <?= __('Evo-TSM/tss_contact.cmsv'); ?></small>
                            <div class="content small"><?= EVO_VERSION ?></div>
                        </div>
                        <div class="item">
                            <small class="ui sub header"><i class="fas fa-code-branch"></i> <?= __('Evo-TSM/tss_contact.commit'); ?></small>
                            <div class="content small"><?= EVO_BUILD ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-8 col-lg-9 col-xl-9">
            <?php if ($message_sent): ?>
                <?php if (isset($email_sent) && $email_sent): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle"></i> 
                        Votre message a été envoyé avec succès ! 
                        <br><small class="text-muted">
                            L'email a été envoyé au créateur du plugin et sauvegardé dans la base de données.
                        </small>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Votre message a été enregistré mais l'email n'a pas pu être envoyé. 
                        <br><small class="text-muted">
                            Le message a été sauvegardé dans la base de données. 
                            Vous pouvez le consulter dans la section "Messages de Contact" de l'administration.
                            <?php if (isset($error_message) && $error_message): ?>
                                <br><strong>Raison :</strong> <?= htmlspecialchars($error_message) ?>
                                <br><strong>Solution :</strong> 
                                <?php if (strpos($error_message, 'fonction d\'envoi d\'email de PHP') !== false): ?>
                                    Configurer un serveur SMTP dans les paramètres d'administration ou contacter l'administrateur système.
                                <?php elseif (strpos($error_message, 'SMTP') !== false): ?>
                                    Vérifier la configuration SMTP dans les paramètres d'administration.
                                <?php else: ?>
                                    Configurer l'envoi d'emails dans les paramètres d'administration d'EvoCMS.
                                <?php endif; ?>
                            <?php else: ?>
                                <br><strong>Raison :</strong> Configuration email d'EvoCMS non disponible.
                                <br><strong>Solution :</strong> Configurer l'envoi d'emails dans les paramètres d'administration d'EvoCMS.
                            <?php endif; ?>
                        </small>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="card">
                    <div class="card-header text-muted small">
                        <span><?= __('Evo-TSM/tss_contact.about'); ?></span>
                    </div>
                    <div class="card-body">
                        <input type="text" name="subject" class="form-control" 
                               placeholder="<?= __('Evo-TSM/tss_contact.sub_placeholder'); ?>"
                               value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header text-muted small">
                        <span><?= __('Evo-TSM/tss_contact.composer'); ?></span>
                    </div>
                    <div class="card-body">
                        <textarea name="message" class="form-control" rows="6"
                                  placeholder="<?= __('Evo-TSM/tss_contact.mes_placeholder'); ?>" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="followup" name="followup" value="1">
                            <label class="custom-control-label" for="followup">
                                <?= __('Evo-TSM/tss_contact.followup'); ?>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="contact_btn center">
                    <button type="submit" class="btn btn-sm btn-dark center">
                        <i class="fas fa-paper-plane"></i> Envoyer le message
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>