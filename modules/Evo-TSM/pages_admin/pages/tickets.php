<?php
/**
 * Template pour l'affichage des listes de tickets
 * Compatible avec le système AJAX et la suppression immédiate des lignes
 */

// Configuration des données du cache pour JavaScript
$cache_installed = \DB::TableExists('tss_cache');
$cache_entries = 0;

if ($cache_installed) {
    $cache_result = \DB::Get("SELECT COUNT(*) as count FROM {tss_cache} WHERE cache_key LIKE 'evo_tsm_%'");
    $cache_entries = $cache_result['count'] ?? 0;
}

$cache_data = [
    'status' => $cache_installed ? 'active' : 'inactive',
    'message' => $cache_installed ? 'Actif' : 'Non installé',
    'icon' => $cache_installed ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle',
    'color' => $cache_installed ? 'success' : 'warning',
    'entries' => $cache_entries
];
?>

<div class="col-12">
    <div class="card">
        <div class="card-body" style="padding:0px;">
            <table id="tickets-table" class="table table-hover card-footer text-muted small">
                <?php if($type === "close") : ?>
                    <thead>
                        <tr class="table-dark">
                            <th><input type="checkbox" id="selectAll"></th>
                            <th scope="col"><?= function_exists('__') ? __('Evo-TSM/tss_table.account') : 'Compte' ?></th>
                            <th scope="col"><?= function_exists('__') ? __('Evo-TSM/tss_table.subject') : 'Sujet' ?></th>
                            <th scope="col"><?= function_exists('__') ? __('Evo-TSM/tss_table.end_date') : 'Date de fermeture' ?></th>
                            <th scope="col"><?= function_exists('__') ? __('Evo-TSM/tss_table.assigned') : 'Assigné à' ?></th>
                            <th scope="col"><?= function_exists('__') ? __('Evo-TSM/tss_table.rate') : 'Note' ?></th>
                            <th scope="col"><?= function_exists('__') ? __('Evo-TSM/tss_table.action') : 'Actions' ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($get) : ?>
                            <?php foreach($get as $key => $value) : ?>
                                <tr>
                                    <th><input type="checkbox" value="<?= $value['id'] ?>" class="ticket-checkbox"></th>
                                    <td><a href="/admin/?page=user_view&id=<?= $value['sid']; ?>" target="_blank"><?= htmlspecialchars($value["account"] ?? 'Inconnu') ?></a></td>
                                    <td><a href="?p=Evo-TSM/view&id=<?= $value["id"] ?>"><?= htmlspecialchars($value["subject"] ?? 'Sans sujet') ?></a></td>
                                    <td><?= htmlspecialchars($value["close_date"] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($value["moderator"] ?? 'Non assigné') ?></td>
                                    <td><?= htmlspecialchars($value["score"] ?? 'N/A') ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="?p=Evo-TSM/view&id=<?= $value["id"] ?>" class="btn btn-sm btn-outline-primary" title="Voir le ticket">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-warning" onclick="reopenTicket(<?= $value['id'] ?>)" title="Rouvrir le ticket">
                                                <i class="fa fa-unlock"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteTicket(<?= $value['id'] ?>)" title="Supprimer le ticket">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="7">
                                    <div class="alert alert-primary text-center"><?= function_exists('__') ? __('Evo-TSM/tss_alert.solved') : 'Aucun ticket résolu' ?></div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                <?php elseif($type === "critical") : ?>
                    <thead>
                        <tr class="table-dark">
                            <th><input type="checkbox" id="selectAll"></th>
                            <th scope="col"><?= function_exists('__') ? __('Evo-TSM/tss_table.account') : 'Compte' ?></th>
                            <th scope="col"><?= function_exists('__') ? __('Evo-TSM/tss_table.subject') : 'Sujet' ?></th>
                            <th scope="col"><?= function_exists('__') ? __('Evo-TSM/tss_table.end_date') : 'Date de fermeture' ?></th>
                            <th scope="col"><?= function_exists('__') ? __('Evo-TSM/tss_table.assigned') : 'Assigné à' ?></th>
                            <th scope="col"><?= function_exists('__') ? __('Evo-TSM/tss_table.action') : 'Actions' ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($get) : ?>
                            <?php foreach($get as $key => $value) : ?>
                                <tr>
                                    <th><input type="checkbox" value="<?= $value['id'] ?>" class="ticket-checkbox"></th>
                                    <td><a href="/admin/?page=user_view&id=<?= $value['sid']; ?>" target="_blank"><?= htmlspecialchars($value["account"] ?? 'Inconnu') ?></a></td>
                                    <td><a href="?p=Evo-TSM/view&id=<?= $value["id"] ?>"><?= htmlspecialchars($value["subject"] ?? 'Sans sujet') ?></a></td>
                                    <td><?= htmlspecialchars($value["close_date"] ?? 'N/A') ?></td>
                                    <td><a href="/admin/?page=user_view&id=<?= $value['assignation']; ?>" target="_blank"><?= htmlspecialchars($value["moderator"] ?? 'Non assigné') ?></a></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="?p=Evo-TSM/view&id=<?= $value["id"] ?>" class="btn btn-sm btn-outline-primary" title="Voir le ticket">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-success" onclick="closeTicket(<?= $value['id'] ?>)" title="Fermer le ticket">
                                                <i class="fa fa-lock"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteTicket(<?= $value['id'] ?>)" title="Supprimer le ticket">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6">
                                    <div class="alert alert-primary text-center"><?= function_exists('__') ? __('Evo-TSM/tss_alert.nocritical') : 'Aucun ticket critique' ?></div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                <?php elseif($type === "open") : ?>
                    <thead>
                        <tr class="table-dark">
                            <th><input type="checkbox" id="selectAll"></th>
                            <th scope="col"><?= function_exists('__') ? __('Evo-TSM/tss_table.account') : 'Compte' ?></th>
                            <th scope="col"><?= function_exists('__') ? __('Evo-TSM/tss_table.subject') : 'Sujet' ?></th>
                            <th scope="col"><?= function_exists('__') ? __('Evo-TSM/tss_table.start_date') : 'Date de création' ?></th>
                            <th scope="col"><?= function_exists('__') ? __('Evo-TSM/tss_table.assigned') : 'Assigné à' ?></th>
                            <th scope="col"><?= function_exists('__') ? __('Evo-TSM/tss_table.action') : 'Actions' ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($get) : ?>
                            <?php foreach($get as $key => $value) : ?>
                                <tr>
                                    <th><input type="checkbox" value="<?= $value['id'] ?>" class="ticket-checkbox"></th>
                                    <td><a href="/admin/?page=user_view&id=<?= $value['sid']; ?>" target="_blank"><?= htmlspecialchars($value["account"] ?? 'Inconnu') ?></a></td>
                                    <td><a href="?p=Evo-TSM/view&id=<?= $value["id"] ?>"><?= htmlspecialchars($value["subject"] ?? 'Sans sujet') ?></a></td>
                                    <td><?= htmlspecialchars($value["create_date"] ?? 'N/A') ?></td>
                                    <td><a href="/admin/?page=user_view&id=<?= $value['assignation']; ?>" target="_blank"><?= htmlspecialchars($value["moderator"] ?? 'Non assigné') ?></a></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="?p=Evo-TSM/view&id=<?= $value["id"] ?>" class="btn btn-sm btn-outline-primary" title="Voir le ticket">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-success" onclick="closeTicket(<?= $value['id'] ?>)" title="Fermer le ticket">
                                                <i class="fa fa-lock"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteTicket(<?= $value['id'] ?>)" title="Supprimer le ticket">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6">
                                    <div class="alert alert-primary text-center"><?= function_exists('__') ? __('Evo-TSM/tss_alert.unsolved') : 'Aucun ticket ouvert' ?></div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                <?php elseif($type === "unassigned") : ?>
                    <thead>
                        <tr class="table-dark">
                            <th><input type="checkbox" id="selectAll"></th>
                            <th scope="col"><?= function_exists('__') ? __('Evo-TSM/tss_table.account') : 'Compte' ?></th>
                            <th scope="col"><?= function_exists('__') ? __('Evo-TSM/tss_table.subject') : 'Sujet' ?></th>
                            <th scope="col"><?= function_exists('__') ? __('Evo-TSM/tss_table.start_date') : 'Date de création' ?></th>
                            <th scope="col"><?= function_exists('__') ? __('Evo-TSM/tss_table.assigned') : 'Assigné à' ?></th>
                            <th scope="col"><?= function_exists('__') ? __('Evo-TSM/tss_table.action') : 'Actions' ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($get) : ?>
                            <?php foreach($get as $key => $value) : ?>
                                <tr>
                                    <th><input type="checkbox" value="<?= $value['id'] ?>" class="ticket-checkbox"></th>
                                    <td><?= htmlspecialchars($value["account"] ?? 'Inconnu') ?></td>
                                    <td><a href="?p=Evo-TSM/view&id=<?= $value["id"] ?>"><?= htmlspecialchars($value["subject"] ?? 'Sans sujet') ?></a></td>
                                    <td><?= htmlspecialchars($value["create_date"] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($value["moderator"] ?? 'Non assigné') ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="?p=Evo-TSM/view&id=<?= $value["id"] ?>" class="btn btn-sm btn-outline-primary" title="Voir le ticket">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-success" onclick="closeTicket(<?= $value['id'] ?>)" title="Fermer le ticket">
                                                <i class="fa fa-lock"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteTicket(<?= $value['id'] ?>)" title="Supprimer le ticket">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6">
                                    <div class="alert alert-primary text-center"><?= function_exists('__') ? __('Evo-TSM/tss_alert.unassigned') : 'Aucun ticket non assigné' ?></div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<!-- Styles pour les animations et indicateurs -->
<style>
.ticket-row {
    transition: all 0.3s ease;
}

.ticket-row:hover {
    background-color: #f8f9fa;
    transform: translateX(5px);
}


.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
}

.notification-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
}

.alert-notification {
    min-width: 300px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.table-container {
    position: relative;
    overflow: hidden;
}

.fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<!-- Scripts pour le système AJAX -->
<script type="text/javascript">
// Configuration des données du cache pour JavaScript
window.cacheData = <?= json_encode($cache_data) ?>;

// Configuration du gestionnaire de tableau
window.ticketsTableConfig = {
    currentState: '<?= $type ?>',
    autoRefreshInterval: 30000, // 30 secondes
    ajaxEndpoint: '/modules/Evo-TSM/direct-ajax.php'
};

// Initialiser le gestionnaire de tableau quand le DOM est prêt
document.addEventListener('DOMContentLoaded', function() {
    if (typeof TicketsTable !== 'undefined') {
        TicketsTable.init('<?= $type ?>');
    }
});
</script>