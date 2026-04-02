<?php
defined('EVO') or die('Que fais-tu là?');
has_permission('manage_icons', true);

// Récupération des paramètres
$iconFramework = App::getConfig('modules.icon-manager.icon_framework', 'fontawesome');
$iconStyle = App::getConfig('modules.icon-manager.icon_style', 'fa-solid');
$iconPack = App::getConfig('modules.icon-manager.icon_pack', '');
$iconName = App::getConfig('modules.icon-manager.icon_name', 'fa-flag');
$ignoreNonFA = App::getConfig('modules.icon-manager.ignore_non_fa', false);

// Traitement du formulaire
if (isset($_POST['save_settings'])) {
    $iconStyle = App::POST('icon_style', 'fa-solid');
    $iconPack = App::POST('icon_pack', '');
    $ignoreNonFA = App::POST('ignore_non_fa', false) ? 1 : 0;
    
    try {
        Db::Query('INSERT INTO {settings} (name, value, default_value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)', 'modules.icon-manager.icon_style', $iconStyle, 'fa-solid');
        Db::Query('INSERT INTO {settings} (name, value, default_value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)', 'modules.icon-manager.icon_pack', $iconPack, '');
        Db::Query('INSERT INTO {settings} (name, value, default_value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)', 'modules.icon-manager.ignore_non_fa', $ignoreNonFA, '0');
        App::setSuccess('Paramètres sauvegardés avec succès !');
    } catch (Exception $e) {
        App::setWarning('Erreur lors de la sauvegarde : ' . $e->getMessage());
        return;
    }
}

$finalClass = trim("$iconStyle $iconPack $iconName");

// Fonctions utilitaires
function option($value, $text, $selected) {
    return "<option value=\"$value\"" . ($selected ? ' selected' : '') . ">$text</option>";
}

$styles = ['fa-solid' => 'Solid', 'fa-regular' => 'Regular', 'fa-light' => 'Light', 'fa-thin' => 'Thin'];
$packs = ['' => 'Classic', 'fa-duotone' => 'Duotone', 'fa-sharp' => 'Sharp', 'fa-sharp-duotone' => 'Sharp Duotone'];
$icons = ['fa-flag', 'fa-check', 'fa-ban', 'fa-times', 'fa-user', 'fa-heart', 'fa-star', 'fa-home', 'fa-search', 'fa-cog'];
?>

<div class="container-fluid p-3">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header py-2">
                    <h5 class="card-title mb-0">
                        <i class="fa fa-icons me-2"></i>Configuration du Gestionnaire d'Icônes
                    </h5>
                </div>
                <div class="card-body p-3">
                    <!-- Navigation par onglets Bootstrap 5.3 -->
                    <ul class="nav nav-tabs" id="iconManagerTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" type="button" role="tab" aria-controls="settings" aria-selected="true">
                                <i class="fa fa-cog me-2"></i>Paramètres du Site
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="preview-tab" data-bs-toggle="tab" data-bs-target="#preview" type="button" role="tab" aria-controls="preview" aria-selected="false">
                                <i class="fa fa-eye me-2"></i>Aperçu des Icônes
                            </button>
                        </li>
                    </ul>
                    <!-- Contenu des onglets -->
                    <div class="tab-content" id="iconManagerTabsContent">
                        <!-- Onglet 1: Paramètres du Site -->
                        <div class="tab-pane fade p-3 show active" id="settings" role="tabpanel" aria-labelledby="settings-tab">
                            <form method="post" id="icon-settings-form">
                                <div class="row g-3">
                                    <!-- Selectbox 0: Framework -->
                                    <div class="col-md-3">
                                        <label for="icon_framework" class="form-label">Framework d'icônes</label>
                                        <select class="form-select" id="icon_framework" name="icon_framework" onchange="updateFramework()">
                                            <option value="fontawesome" <?= $iconFramework === 'fontawesome' ? 'selected' : '' ?>>Font Awesome 7.1.0</option>
                                        </select>
                                    </div>
                                    <!-- Selectbox 1: Style -->
                                    <div class="col-md-3">
                                        <label for="icon_style" class="form-label">Icon Style</label>
                                        <select class="form-select" id="icon_style" name="icon_style" onchange="updatePreview()">
                                            <?php foreach($styles as $val => $text) echo option($val, $text, $iconStyle === $val); ?>
                                        </select>
                                    </div>
                                    <!-- Selectbox 2: Pack -->
                                    <div class="col-md-3">
                                        <label for="icon_pack" class="form-label">Icon Family</label>
                                        <select class="form-select" id="icon_pack" name="icon_pack" onchange="updatePreview()">
                                            <?php foreach($packs as $val => $text) echo option($val, $text, $iconPack === $val); ?>
                                        </select>
                                    </div>
                                    <!-- Selectbox 3: Nom d'icône -->
                                    <div class="col-md-3">
                                        <label for="icon_name" class="form-label">Nom d'icône</label>
                                        <select class="form-select" id="icon_name" name="icon_name" onchange="updatePreview()">
                                            <?php foreach($icons as $icon) echo option($icon, $icon, $iconName === $icon); ?>
                                        </select>
                                    </div>
                                </div>
                                <!-- Option pour ignorer les classes non-FontAwesome -->
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="ignore_non_fa" name="ignore_non_fa" <?= $ignoreNonFA ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="ignore_non_fa">
                                                <i class="fa fa-filter me-2"></i>Ignorer les classes qui ne sont pas de FontAwesome
                                            </label>
                                            <div class="form-text">
                                                <small class="text-muted">
                                                    <i class="fa fa-info-circle me-1"></i>
                                                    Quand cette option est activée, seules les classes FontAwesome (fa-*) seront traitées. 
                                                    Les autres classes CSS seront préservées sans modification.
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Message de combinaison non supportée -->
                                <div id="unsupported-combination" class="row mt-4 d-none">
                                    <div class="col-12">
                                        <div class="alert alert-warning d-flex align-items-center">
                                            <i class="fa fa-exclamation-triangle fa-2x me-3 text-warning"></i>
                                            <div>
                                                <h5 class="alert-heading mb-2">
                                                    <i class="fa fa-info-circle me-2"></i>Combinaison non supportée
                                                </h5>
                                                <p class="mb-2">
                                                    La combinaison <strong><span id="unsupported-style"></span> + <span id="unsupported-pack"></span></strong> 
                                                    n'est pas supportée par Font Awesome 7.1.0 Pro.
                                                </p>
                                                <p class="mb-0">
                                                    <strong>Consultez la section ci-dessus</strong> pour voir les combinaisons possibles pour chaque Icon Family.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Aperçu en temps réel -->
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="card bg-light">
                                            <div class="card-header py-2">
                                                <h6 class="card-title mb-0">
                                                    <i class="fa fa-eye me-2"></i>Aperçu en temps réel
                                                </h6>
                                            </div>
                                            <div class="card-body p-3">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h6>Icône actuelle :</h6>
                                                        <div class="preview-icon border rounded p-2 text-center bg-white" style="font-size: 1.5rem;">
                                                            <i id="preview-icon" class="<?= htmlspecialchars($finalClass) ?>"></i>
                                                        </div>
                                                        <p class="text-muted mt-2 small">
                                                            <strong>Classe générée :</strong><br>
                                                            <code id="preview-class" class="bg-dark text-light px-2 py-1 rounded small"><?= htmlspecialchars($finalClass) ?></code>
                                                        </p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6>Exemple d'utilisation :</h6>
                                                        <div class="d-flex gap-1 mb-2">
                                                            <button class="btn btn-primary btn-sm">
                                                                <i id="preview-button-icon" class="<?= htmlspecialchars($finalClass) ?> me-1"></i>Exemple
                                                            </button>
                                                            <button class="btn btn-success btn-sm">
                                                                <i id="preview-button-icon2" class="<?= htmlspecialchars($finalClass) ?> me-1"></i>Autre
                                                            </button>
                                                        </div>
                                                        <div class="mt-2">
                                                            <small class="text-muted">
                                                                <strong>Code HTML :</strong><br>
                                                                <code class="bg-dark text-light px-2 py-1 rounded small">&lt;i class="<span id="preview-html-class"><?= htmlspecialchars($finalClass) ?></span>"&gt;&lt;/i&gt;</code>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 mt-3">
                                    <button type="submit" name="save_settings" class="btn btn-primary btn-sm">
                                        <i class="fa fa-save me-1"></i>Sauvegarder
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="resetToDefault()">
                                        <i class="fa fa-undo me-1"></i>Réinitialiser
                                    </button>
                                </div>
                            </form>
                        </div>
                        <!-- Onglet 2: Aperçu des Icônes -->
                        <div class="tab-pane fade p-3" id="preview" role="tabpanel" aria-labelledby="preview-tab">
                            <div class="row">
                                <div class="col-12">
                                    <h6 class="mb-3">
                                        <i class="fa fa-eye me-2"></i>Aperçu des Icônes les Plus Utilisées
                                    </h6>
                                    <!-- Formulaire de configuration pour la prévisualisation -->
                                    <div class="card mb-3">
                                        <div class="card-header py-2">
                                            <h6 class="card-title mb-0">
                                                <i class="fa fa-cog me-2"></i>Configuration de la Prévisualisation
                                            </h6>
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label for="preview_icon_style" class="form-label">Icon Style</label>
                                                    <select class="form-select" id="preview_icon_style" onchange="updateIconPreview()">
                                                        <option value="fa-solid">Solid</option>
                                                        <option value="fa-regular">Regular</option>
                                                        <option value="fa-light">Light</option>
                                                        <option value="fa-thin">Thin</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="preview_icon_pack" class="form-label">Icon Family</label>
                                                    <select class="form-select" id="preview_icon_pack" onchange="updateIconPreview()">
                                                        <option value="">Classic</option>
                                                        <option value="fa-duotone">Duotone</option>
                                                        <option value="fa-sharp">Sharp</option>
                                                        <option value="fa-sharp-duotone">Sharp Duotone</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="preview_icon_size" class="form-label">Taille des Icônes</label>
                                                    <select class="form-select" id="preview_icon_size" onchange="updateIconPreview()">
                                                        <option value="fa-1x">Normal (1x)</option>
                                                        <option value="fa-2x" selected>Grand (2x)</option>
                                                        <option value="fa-3x">Très Grand (3x)</option>
                                                        <option value="fa-4x">Énorme (4x)</option>
                                                        <option value="fa-5x">Géant (5x)</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-muted mb-3 small">
                                        Voici un aperçu des icônes les plus couramment utilisées avec la configuration sélectionnée :
                                        <strong><span id="current-preview-style"><?= htmlspecialchars($iconStyle) ?></span></strong> + 
                                        <strong><span id="current-preview-pack"><?= htmlspecialchars($iconPack ?: 'Classic') ?></span></strong>
                                    </p>
                                    <!-- Grille d'icônes -->
                                    <div class="row" id="icon-preview-grid">
                                        <!-- Les icônes seront générées par JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateFramework() {
    const framework = document.getElementById('icon_framework').value;
    
    // Charger le CSS du framework sélectionné
    loadFrameworkCSS(framework);
    
    // Mettre à jour les options selon le framework
    updateFrameworkOptions(framework);
    
    // Mettre à jour l'aperçu
    updatePreview();
}

function loadFrameworkCSS(framework) {
    // Supprimer les anciens CSS
    const existingCSS = document.querySelectorAll('link[data-framework-css]');
    existingCSS.forEach(link => link.remove());
    
    if (framework === 'fontawesome') {
        // Charger Font Awesome 7.1.0 de base
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://site-assets.fontawesome.com/releases/v7.1.0/css/all.css';
        link.setAttribute('data-framework-css', 'fontawesome-base');
        document.head.appendChild(link);
        
        // Charger le CSS spécifique selon la combinaison Style + Pack
        loadSpecificDuotoneCSS();
    }
    // Ajouter d'autres frameworks ici si nécessaire
}

function loadSpecificDuotoneCSS() {
    const iconStyle = document.getElementById('icon_style').value;
    const iconPack = document.getElementById('icon_pack').value;
    
    const cssMap = {
        'fa-duotone': { 'fa-light': 'duotone-light', 'fa-regular': 'duotone-regular', 'fa-thin': 'duotone-thin' },
        'fa-sharp': { 'fa-light': 'sharp-light', 'fa-regular': 'sharp-regular', 'fa-solid': 'sharp-solid', 'fa-thin': 'sharp-thin' },
        'fa-sharp-duotone': { 'fa-light': 'sharp-duotone-light', 'fa-regular': 'sharp-duotone-regular', 'fa-solid': 'sharp-duotone-solid', 'fa-thin': 'sharp-duotone-thin' }
    };
    
    const specificCSS = cssMap[iconPack]?.[iconStyle] ? `https://site-assets.fontawesome.com/releases/v7.1.0/css/${cssMap[iconPack][iconStyle]}.css` : '';
    
    if (specificCSS) {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = specificCSS;
        link.setAttribute('data-framework-css', 'fontawesome-specific');
        document.head.appendChild(link);
    }
}

function updateFrameworkOptions(framework) {
    const selects = ['icon_style', 'icon_pack', 'icon_name'];
    const options = {
        fontawesome: {
            icon_style: ['fa-solid', 'fa-regular', 'fa-light', 'fa-thin'],
            icon_pack: ['', 'fa-duotone', 'fa-sharp', 'fa-sharp-duotone'],
            icon_name: ['fa-flag', 'fa-check', 'fa-ban', 'fa-times', 'fa-user', 'fa-heart', 'fa-star', 'fa-home', 'fa-search', 'fa-cog']
        }
    };
    
    if (options[framework]) {
        selects.forEach(id => {
            const select = document.getElementById(id);
            select.innerHTML = options[framework][id].map(val => `<option value="${val}">${val || 'Classic'}</option>`).join('');
            select.value = select.options[0].value;
        });
    }
}

function updatePreview() {
    const iconStyle = document.getElementById('icon_style').value;
    const iconPack = document.getElementById('icon_pack').value;
    const iconName = document.getElementById('icon_name').value;
    
    // Vérifier si la combinaison est supportée
    const isSupported = checkCombinationSupport(iconStyle, iconPack);
    
    // Afficher ou masquer le message d'erreur
    const unsupportedDiv = document.getElementById('unsupported-combination');
    const previewDiv = document.querySelector('.card.bg-light').parentElement;
    
            if (!isSupported) {
                unsupportedDiv.classList.remove('d-none');
                previewDiv.classList.add('d-none');
                
                // Mettre à jour le message d'erreur
                document.getElementById('unsupported-style').textContent = iconStyle;
                document.getElementById('unsupported-pack').textContent = iconPack || 'Classic';
                
                return;
            } else {
                unsupportedDiv.classList.add('d-none');
                previewDiv.classList.remove('d-none');
            }
    
    // Recharger le CSS spécifique si nécessaire
    loadSpecificDuotoneCSS();
    
    // Construction de la classe finale
    let finalClass = iconStyle;
    if (iconPack) {
        finalClass += ' ' + iconPack;
    }
    finalClass += ' ' + iconName;
    
    // Mise à jour de l'aperçu
    const previewIcon = document.getElementById('preview-icon');
    const previewButtonIcon = document.getElementById('preview-button-icon');
    const previewButtonIcon2 = document.getElementById('preview-button-icon2');
    const previewClass = document.getElementById('preview-class');
    const previewHtmlClass = document.getElementById('preview-html-class');
    
    previewIcon.className = finalClass;
    previewButtonIcon.className = finalClass;
    previewButtonIcon2.className = finalClass;
    previewClass.textContent = finalClass;
    previewHtmlClass.textContent = finalClass;
}

function checkCombinationSupport(style, pack) {
    // Définir les combinaisons supportées pour les 4 familles conservées
    const supportedCombinations = {
        'fa-solid': ['', 'fa-duotone', 'fa-sharp', 'fa-sharp-duotone'],
        'fa-regular': ['', 'fa-duotone', 'fa-sharp', 'fa-sharp-duotone'],
        'fa-light': ['', 'fa-duotone', 'fa-sharp', 'fa-sharp-duotone'],
        'fa-thin': ['', 'fa-duotone', 'fa-sharp', 'fa-sharp-duotone']
    };
    
    // Vérifier si la combinaison est dans la liste des combinaisons supportées
    if (supportedCombinations[style] && supportedCombinations[style].includes(pack)) {
        return true;
    }
    
    return false;
}

function resetToDefault() {
    document.getElementById('icon_framework').value = 'fontawesome';
    document.getElementById('icon_style').value = 'fa-solid';
    document.getElementById('icon_pack').value = '';
    document.getElementById('icon_name').value = 'fa-flag';
    updateFramework();
}

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            // Charger le CSS du framework par défaut
            loadFrameworkCSS('<?= $iconFramework ?>');
            
            // S'assurer que l'aperçu initial est correct
            const previewIcon = document.getElementById('preview-icon');
            if (previewIcon) {
                previewIcon.className = '<?= htmlspecialchars($finalClass) ?>';
            }
            
            // Initialiser les valeurs du formulaire de prévisualisation
            document.getElementById('preview_icon_style').value = '<?= $iconStyle ?>';
            document.getElementById('preview_icon_pack').value = '<?= $iconPack ?>';
            
            updatePreview();
            generateIconPreview();
            
            // Mettre à jour l'aperçu des icônes quand les paramètres changent
            document.getElementById('icon_style').addEventListener('change', function() {
                updatePreview();
                generateIconPreview();
            });
            
            document.getElementById('icon_pack').addEventListener('change', function() {
                updatePreview();
                generateIconPreview();
            });
        });

// Fonction pour générer l'aperçu des icônes
function generateIconPreview() {
    const iconGrid = document.getElementById('icon-preview-grid');
    if (!iconGrid) return;

    const currentStyle = document.getElementById('preview_icon_style').value;
    const currentPack = document.getElementById('preview_icon_pack').value;
    const currentSize = document.getElementById('preview_icon_size').value;

    const popularIcons = ['fa-home', 'fa-user', 'fa-cog', 'fa-search', 'fa-heart', 'fa-star', 'fa-envelope', 'fa-phone', 'fa-calendar', 'fa-clock', 'fa-download', 'fa-upload', 'fa-edit', 'fa-trash', 'fa-save', 'fa-print', 'fa-share', 'fa-link', 'fa-image', 'fa-file', 'fa-folder', 'fa-lock', 'fa-unlock', 'fa-eye', 'fa-eye-slash', 'fa-plus', 'fa-minus', 'fa-check', 'fa-times', 'fa-arrow-left', 'fa-arrow-right', 'fa-arrow-up', 'fa-arrow-down', 'fa-chevron-left', 'fa-chevron-right', 'fa-chevron-up', 'fa-chevron-down', 'fa-bars', 'fa-bell', 'fa-comment', 'fa-comments', 'fa-thumbs-up', 'fa-thumbs-down', 'fa-flag', 'fa-exclamation-triangle', 'fa-info-circle', 'fa-question-circle', 'fa-ban', 'fa-warning', 'fa-play', 'fa-pause', 'fa-stop', 'fa-forward', 'fa-backward', 'fa-volume-up', 'fa-volume-down', 'fa-volume-off', 'fa-music', 'fa-video', 'fa-camera', 'fa-microphone', 'fa-headphones'];

    iconGrid.innerHTML = popularIcons.map(iconName => {
        const iconClass = buildIconClass(currentStyle, currentPack, iconName);
        return `<div class="col-auto mb-3"><div class="card text-center h-100"><div class="card-body"><i class="${iconClass} ${currentSize} icon-preview-exclude"></i></div></div></div>`;
    }).join('');
    
    updatePreviewInfo();
}

// Fonctions utilitaires
function updatePreviewInfo() {
    const style = document.getElementById('preview_icon_style').value;
    const pack = document.getElementById('preview_icon_pack').value;
    const styleEl = document.getElementById('current-preview-style');
    const packEl = document.getElementById('current-preview-pack');
    if (styleEl) styleEl.textContent = style;
    if (packEl) packEl.textContent = pack || 'Classic';
}

function updateIconPreview() { generateIconPreview(); }

function buildIconClass(style, pack, name) {
    return [...new Set([style, pack, name].filter(Boolean))].join(' ');
}
</script>

        <style>
        .preview-icon { border: 1px solid var(--bs-border-color); border-radius: var(--bs-border-radius); padding: 0.75rem; text-align: center; background: var(--bs-body-bg); }
        .card.bg-light { background-color: var(--bs-light) !important; }
        .nav-tabs .nav-link { border: 1px solid transparent; border-top-left-radius: var(--bs-border-radius); border-top-right-radius: var(--bs-border-radius); }
        .nav-tabs .nav-link:hover { border-color: var(--bs-border-color-translucent) var(--bs-border-color-translucent) var(--bs-border-color); }
        .nav-tabs .nav-link.active { color: var(--bs-nav-tabs-link-active-color); background-color: var(--bs-nav-tabs-link-active-bg); border-color: var(--bs-nav-tabs-link-active-border-color); }
        #icon-preview-grid .card { transition: transform 0.2s ease-in-out; border: 1px solid var(--bs-border-color); }
        #icon-preview-grid .card:hover { transform: translateY(-2px); box-shadow: var(--bs-box-shadow); }
        #icon-preview-grid .card-body { padding: 0.5rem; }
        #icon-preview-grid i { color: var(--bs-secondary-color); font-size: 1.2rem; }
        </style>
