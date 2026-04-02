<link rel="stylesheet" href="../modules/Evo-TSM/pages_admin/assets/css/styles.css"/>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<script type="text/javascript" src="../modules/Evo-TSM/pages_admin/assets/js/rating.js"></script>
<script type="text/javascript" src="../modules/Evo-TSM/pages_admin/assets/js/Chart.js"></script>
<script type="text/javascript" src="../modules/Evo-TSM/pages_admin/assets/js/ajax.js"></script>
<script type="text/javascript" src="../modules/Evo-TSM/pages_admin/assets/js/evo-tsm.js"></script>

<div class="plugin_header bg-grad-evo">
    <div class="container header">
        <div class="title left"><h3 class="text-white"><?= __('Evo-TSM/tss_header.name'); ?></h3>
            <span class="text-secondary small"><?= __('Evo-TSM/tss_header.version'); ?> : <?= App::getModule('Evo-TSM')->version ?? '1.0.0' ?></span><br/>
            <span class="text-secondary small"><?= __('Evo-TSM/tss_header.author'); ?> : <?= is_array(App::getModule('Evo-TSM')->author ?? 'Evo-CMS Team') ? implode(', ', App::getModule('Evo-TSM')->author) : (App::getModule('Evo-TSM')->author ?? 'Evo-CMS Team') ?></span>
        </div>
        <div class="opt right text-end text-white d-none d-md-block">
            <div class="input-group">
                <a href="?p=Evo-TSM/home" class="btn btn-dark" aria-label="Retour à l'accueil" data-bs-original-title="Retour à l'accueil"><i class="fa-solid fa-duotone fa-home fas"></i></a>
                <a href="?p=Evo-TSM/bug" class="btn btn-dark" aria-label="Suivi des Bugs" data-bs-original-title="Suivi des Bugs" aria-describedby="tooltip564203"><i class="fa-solid fa-duotone fa-bug fas"></i></a>
                <a href="?p=Evo-TSM/contact" class="btn btn-dark" aria-label="Nous Contacter" data-bs-original-title="Nous Contacter"><i class="fa-solid fa-duotone fa-envelope far"></i></a>
                <a href="?p=Evo-TSM/messages" class="btn btn-dark" aria-label="Messages de Contact" data-bs-original-title="Messages de Contact"><i class="fa-solid fa-duotone fa-inbox fas"></i></a>
                
                <?php $cache_installed = \DB::TableExists('tss_cache'); ?>
                
                <?php if ($cache_installed): ?>
                <a href="/admin/?p=Evo-TSM/cache" class="btn btn-dark" aria-label="Gestion du Cache" data-bs-original-title="Gestion du Cache"><i class="fa-solid fa-duotone fa-database fa-fw fa-lg fa"></i></a>
                <a href="/admin/?p=Evo-TSM/performance" class="btn btn-dark" aria-label="Performance" data-bs-original-title="Performance"><i class="fa-solid fa-duotone fa-tachometer-alt fa-fw fa-lg fa"></i></a>
                <?php else: ?>
                <a href="/admin/?p=Evo-TSM/install" class="btn btn-dark" aria-label="Installation Cache" data-bs-original-title="Installation Cache"><i class="fa-solid fa-duotone fa-cog fa-fw fa-lg fa"></i></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>