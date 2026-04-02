<div class="ticket-view container">
    <div class="row col-12">
        <div class="col-sm-12 col-md-4 col-lg-4 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="media">
                        <figure class="avatar150 rounded border">
                            <?= get_avatar($info["sid"], 128, false) ?>
                        </figure>
                    </div>
                    <hr/>
                    <p class="card-text">
                        <div class="ui list">
                            <div class="item">
                                <h6 class="ui sub header"><i class="fas fa-user"></i></i> <?= __('Evo-TSM/tss_view.account'); ?></h6>
                                <div class="content small" style="text-transform: capitalize;"><a href="?page=user_view&id=<?= $info["sid"] ?>" target="_blank"><?= $info["account"] ?></a></div>
                            </div>
                            <div class="item">
                                <h6 class="ui sub header"><i class="fas fa-globe-americas"></i></i> <?= __('Evo-TSM/tss_view.country'); ?></h6>
                                <div class="content small" style="text-transform: capitalize;">
                                    <img src="<?= App::getAsset('/img/flags/'.strtolower($info['country']).'.png') ?>" style="margin-bottom: 4px;"/>
                                    <?= @COUNTRIES[$info['country']] ?>
                                </div>
                            </div>
                            <div class="item">
                                <h6 class="ui sub header"><i class="fas fa-envelope"></i></i> <?= __('Evo-TSM/tss_view.email'); ?></h6>
                                <div class="content small"><a href="mailto:<?= $info["email"] ?>"><?= $info["email"] ?></a></div>
                            </div>
                            <div class="item">
                                <h6 class="ui sub header"><i class="fas fa-calendar-alt"></i></i> <?= __('Evo-TSM/tss_view.register'); ?></h6>
                                <div class="content small"><?= date('Y-m-d', $info["registered"]) ?></div>
                            </div>
                        </div>
                    </p>    
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <a class="text-muted small"><?= __('Evo-TSM/tss_view.opening'); ?> : <?= $info["create_date"] ?></a></br>
                    <?php if($info['close_date']) : ?>
                        <a class="text-muted small"><?= __('Evo-TSM/tss_view.closing'); ?> : <?= $info["close_date"] ?></a></br>
                        <a class="text-muted small"><?= __('Evo-TSM/tss_view.rate'); ?> : <?php if(isset($info["score"])){ echo $info["score"]; }else{ echo "N/A"; } ?> / 5</a>
                    <?php endif; ?>
                </div>
            </div>
            <p class="center">
                <?php if($info['close_date'] === NULL) : ?>
                    <?php if($info['level'] == 1) : ?>
                        <button name="mark_normal" data-id="<?= App::GET('id') ?>" class="btn btn-sm btn-primary mb-2"><?= __('Evo-TSM/tss_view.manormal'); ?></button><br/>
                    <?php else : ?>
                        <button name="mark_critical" data-id="<?= App::GET('id') ?>" class="btn btn-sm btn-warning mb-2"><?= __('Evo-TSM/tss_view.macritical'); ?></button><br/>
                    <?php endif; ?>
                    <button name="mark_solved" data-id="<?= App::GET('id') ?>" class="btn btn-sm btn-success"><?= __('Evo-TSM/tss_view.masolved'); ?></button>
                <?php else : ?>
                    <button name="mark_unsolved" data-id="<?= App::GET('id') ?>" class="btn btn-sm btn-danger"><?= __('Evo-TSM/tss_view.masusolved'); ?></button>
                <?php endif; ?>
            </p>
        </div>
        <div class="col-sm-12 col-md-8 col-lg-8 col-xl-9">
            <div class="card">
                <?php if($info['close_date']) : ?>
                    <span class="ui red ribbon label"><?= __('Evo-TSM/tss_view.ticket_close'); ?></span>
                <?php endif; ?>
                <?php if($info['level'] == 1) : ?>
                    <span class="ui orange ribbon label">Ce billet est classé comme prioritaire</span>
                <?php endif; ?>
                <div class="card-body">
                    <blockquote>
                        <?= $info["short_desc"]; ?>
                    </blockquote>
                    <div id="converbloc" class="card-text">
                        <?php foreach($content AS $key => $val) : ?>

                            <?php if($val['sid'] > '0') : ?>
                                <div class="media message-item" data-message-id="<?= $val['id'] ?>">
                                    <img src="<?= get_avatar($val['sid'], 64, true) ?>" class="align-self-start mr-3 border rounded" />
                                    <div class="media-body">
                                        <span class="text-muted small"><?= $info['account'] ?></span></br>
                                        <span class="text-muted small"><?= $val['send_date'] ?></span></br>
                                        <div class="blue-text text-darken-2" style="font-family: ubuntu;font-weight: 500;"><?= $val['msg'] ?></div>
                                    </div>
                                </div>

                            <?php else : ?>

                                <div class="media text-end message-item" data-message-id="<?= $val['id'] ?>">
                                    <div class="media-body">
                                        <span class="text-muted small"><?= __('Evo-TSM/tss_view.smember'); ?></span></br>
                                        <span class="text-muted small"><?= $val['send_date'] ?></span></br>
                                        <div class="green-text text-darken-2" style="font-family: ubuntu;font-weight: 500;"><?= $val['msg'] ?></div>
                                    </div>
                                    <img src="<?= get_avatar($val['mid'], 64, true) ?>" class="align-self-start mx-3 border rounded" style="margin-right:0 !important">
                                </div>

                            <?php endif; ?>
                            
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php if(!$info['close_date']) : ?>
                <h4 class="ui horizontal divider header"><i class="tag icon"></i><?= __('Evo-TSM/tss_view.addcomm'); ?></h4>
                <div class="card">
                    <div id="ticket-ans" class="card-body">
                        <textarea name="ticket_answer_msg" class="form-control" style="border-radius: 0px" rows="3" autofocus></textarea>
                    </div>
                    <button data-id="<?= App::GET('id') ?>" name="ticket_answer_btn" class="btn btn-sm btn-primary" type="submit"><?= __('Evo-TSM/tss_view.send'); ?></button>                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Scripts pour la page de visualisation dynamique -->
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des boutons d'action
    const actionButtons = document.querySelectorAll('[name="mark_solved"], [name="mark_unsolved"], [name="mark_critical"], [name="mark_normal"]');
    
    actionButtons.forEach(button => {
        button.addEventListener('click', function() {
            const action = this.getAttribute('name');
            const ticketId = this.getAttribute('data-id');
            
            // Animation du bouton
            this.disabled = true;
            this.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Traitement...';
            
            // Envoyer la requête AJAX
            fetch('/modules/Evo-TSM/direct-ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: action,
                    tid: ticketId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Afficher une notification de succès
                    showNotification(data.message, 'success');
                    
                    // Recharger la page après un délai
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showNotification(data.error, 'error');
                    // Restaurer le bouton
                    this.disabled = false;
                    this.innerHTML = this.getAttribute('data-original-text') || this.textContent;
                }
            })
            .catch(error => {
                showNotification('Erreur de connexion', 'error');
                this.disabled = false;
                this.innerHTML = this.getAttribute('data-original-text') || this.textContent;
            });
        });
        
        // Sauvegarder le texte original
        button.setAttribute('data-original-text', button.textContent);
    });
    
    // Gestion du bouton d'envoi de réponse
    const answerButton = document.querySelector('[name="ticket_answer_btn"]');
    const answerTextarea = document.querySelector('[name="ticket_answer_msg"]');
    
    if (answerButton && answerTextarea) {
        answerButton.addEventListener('click', function() {
            const ticketId = this.getAttribute('data-id');
            const message = answerTextarea.value.trim();
            
            if (!message) {
                showNotification('Veuillez saisir un message', 'warning');
                answerTextarea.focus();
                return;
            }
            
            // Animation du bouton
            this.disabled = true;
            this.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Envoi...';
            
            // Envoyer la requête AJAX
            fetch('/modules/Evo-TSM/direct-ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'send_answer_assigned',
                    tid: ticketId,
                    msg: message
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    answerTextarea.value = '';
                    
                    // Recharger la page pour afficher le nouveau message
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showNotification(data.error, 'error');
                    this.disabled = false;
                    this.innerHTML = '<?= __('Evo-TSM/tss_view.send'); ?>';
                }
            })
            .catch(error => {
                showNotification('Erreur de connexion', 'error');
                this.disabled = false;
                this.innerHTML = '<?= __('Evo-TSM/tss_view.send'); ?>';
            });
        });
    }
    
    // Fonction pour afficher les notifications
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        
        const icon = {
            'success': 'fa-check-circle',
            'error': 'fa-exclamation-triangle',
            'info': 'fa-info-circle',
            'warning': 'fa-exclamation-circle'
        }[type] || 'fa-info-circle';
        
        notification.innerHTML = `
            <i class="fa ${icon}"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Supprimer automatiquement après 5 secondes
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }
    
    // Auto-scroll vers le bas de la conversation
    const conversation = document.getElementById('converbloc');
    if (conversation) {
        conversation.scrollTop = conversation.scrollHeight;
        
        // Ajouter un indicateur de scroll
        const scrollIndicator = document.createElement('div');
        scrollIndicator.className = 'scroll-indicator';
        scrollIndicator.innerHTML = '<i class="fa fa-arrow-down"></i> Nouveaux messages';
        conversation.parentElement.style.position = 'relative';
        conversation.parentElement.appendChild(scrollIndicator);
        
        // Gérer l'affichage de l'indicateur
        conversation.addEventListener('scroll', function() {
            const isAtBottom = conversation.scrollTop + conversation.clientHeight >= conversation.scrollHeight - 10;
            if (isAtBottom) {
                scrollIndicator.classList.remove('show');
            } else {
                scrollIndicator.classList.add('show');
            }
        });
        
        // Clic sur l'indicateur pour aller en bas
        scrollIndicator.addEventListener('click', function() {
            conversation.scrollTop = conversation.scrollHeight;
            this.classList.remove('show');
        });
    }
});
</script>