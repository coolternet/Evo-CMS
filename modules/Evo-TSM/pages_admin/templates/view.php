<link rel="stylesheet" href="../modules/Evo-TSM/pages_admin/assets/css/styles.css"/>
<script type="text/javascript" src="../modules/Evo-TSM/pages_admin/assets/js/ajax.js"></script>
<script type="text/javascript" src="../modules/Evo-TSM/pages_admin/assets/js/Chart.js"></script>
<script type="text/javascript" src="../modules/Evo-TSM/pages_admin/assets/js/rating.js"></script>

<div class="plugin_header bg-grad-evo">
    <div class="container header">
        <div class="title text-left">
            <h3 class="text-muted medium"><?= __('Evo-TSM/tss_view.h_ticket'); ?> #<?= $_GET['id'] ?></h3>
            <h2 class="text-white"><?= $title ?></h2>
        </div>
        <div class="opt right text-end text-white d-none d-md-block">
            <div class="input-group">
                <a href="?p=Evo-TSM/home" class="btn btn-dark" title="Go to Home"><i class="fas fa-home"></i></a>
                <a href="<?= isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '?p=Evo-TSM/tickets' ?>" class="btn btn-dark"  title="Back to tickets list"><i class="fas fa-stream"></i></a>
            </div>
        </div>
    </div>
</div>