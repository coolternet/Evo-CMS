<?php
/**
 * Module Icon Manager pour EvoCMS
 * 
 * @package EvoCMS
 * @subpackage Modules
 * @author EvoCMS Team <team@evocms.com>
 * @version 1.0.0
 * @since 2025-01-10
 */

defined('EVO') or die('Que fais-tu là?');

return new class extends Evo\Module {
    public function init()
    {
        // Initialisation du module
    }

    public function activate()
    {
        App::setNotice("Module Icon Manager activé !");
    }

    public function deactivate()
    {
        App::setNotice("Module Icon Manager désactivé !");
    }

    /**
     * Hook pour ajouter le menu d'administration
     */
    public function hook_admin_menu(array &$items)
    {
        $items[] = ['Icon Manager', 'fa-icons', '?p=icon-manager/settings', 'manage_icons'];
    }

    /**
     * Hook pour injecter le CSS et JavaScript dans l'administration
     */
    public function hook_head_admin()
    {
        $this->injectIconManagerAssets();
    }

    /**
     * Hook pour injecter le CSS et JavaScript dans le head
     */
    public function hook_head()
    {
        $this->injectIconManagerAssets();
    }

    /**
     * Méthode privée pour injecter les assets du module
     */
    private function injectIconManagerAssets()
    {
        $iconFramework = $this->getConfig('icon_framework', 'fontawesome');
        $iconStyle = $this->getConfig('icon_style', 'fa-solid');
        $iconPack = $this->getConfig('icon_pack', '');
        $iconName = $this->getConfig('icon_name', 'fa-flag');
        $ignoreNonFA = $this->getConfig('ignore_non_fa', false);
        
        // Charger le CSS du framework sélectionné
        if ($iconFramework === 'fontawesome') {
            // CSS de base Font Awesome 7.1.0
            echo '<link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v7.1.0/css/all.css" data-framework-css="fontawesome-base">';
            
            // CSS spécifique selon la combinaison Style + Pack
            $specificCSS = '';
            
            // DuoTone combinations
            if ($iconPack === 'fa-duotone') {
                if ($iconStyle === 'fa-light') {
                    $specificCSS = 'https://site-assets.fontawesome.com/releases/v7.1.0/css/duotone-light.css';
                } elseif ($iconStyle === 'fa-regular') {
                    $specificCSS = 'https://site-assets.fontawesome.com/releases/v7.1.0/css/duotone-regular.css';
                } elseif ($iconStyle === 'fa-thin') {
                    $specificCSS = 'https://site-assets.fontawesome.com/releases/v7.1.0/css/duotone-thin.css';
                }
            }
            // Sharp combinations
            elseif ($iconPack === 'fa-sharp') {
                if ($iconStyle === 'fa-light') {
                    $specificCSS = 'https://site-assets.fontawesome.com/releases/v7.1.0/css/sharp-light.css';
                } elseif ($iconStyle === 'fa-regular') {
                    $specificCSS = 'https://site-assets.fontawesome.com/releases/v7.1.0/css/sharp-regular.css';
                } elseif ($iconStyle === 'fa-solid') {
                    $specificCSS = 'https://site-assets.fontawesome.com/releases/v7.1.0/css/sharp-solid.css';
                } elseif ($iconStyle === 'fa-thin') {
                    $specificCSS = 'https://site-assets.fontawesome.com/releases/v7.1.0/css/sharp-thin.css';
                }
            }
            // Sharp Duotone combinations
            elseif ($iconPack === 'fa-sharp-duotone') {
                if ($iconStyle === 'fa-light') {
                    $specificCSS = 'https://site-assets.fontawesome.com/releases/v7.1.0/css/sharp-duotone-light.css';
                } elseif ($iconStyle === 'fa-regular') {
                    $specificCSS = 'https://site-assets.fontawesome.com/releases/v7.1.0/css/sharp-duotone-regular.css';
                } elseif ($iconStyle === 'fa-solid') {
                    $specificCSS = 'https://site-assets.fontawesome.com/releases/v7.1.0/css/sharp-duotone-solid.css';
                } elseif ($iconStyle === 'fa-thin') {
                    $specificCSS = 'https://site-assets.fontawesome.com/releases/v7.1.0/css/sharp-duotone-thin.css';
                }
            }
            
            if ($specificCSS) {
                echo '<link rel="stylesheet" href="' . $specificCSS . '" data-framework-css="fontawesome-specific">';
            }
        }
        
        echo '<script>
        window.IconManagerConfig = {
            framework: "' . htmlspecialchars($iconFramework) . '",
            iconStyle: "' . htmlspecialchars($iconStyle) . '",
            iconPack: "' . htmlspecialchars($iconPack) . '",
            iconName: "' . htmlspecialchars($iconName) . '",
            ignoreNonFA: ' . ($ignoreNonFA ? 'true' : 'false') . ',
            enabled: true
        };
        </script>';
    }

    /**
     * Hook pour injecter le JavaScript de remplacement
     */
    public function hook_footer()
    {
        echo '<script src="' . App::getURL('modules/icon-manager/pages_admin/icon-replacer.js') . '"></script>';
    }

    /**
     * Hook AJAX pour sauvegarder les paramètres
     */
    public function hook_ajax($action)
    {
        if ($action === 'save_icon_settings') {
            if (!$this->has_permission('manage_icons')) {
                echo json_encode(['success' => false, 'message' => 'Permission refusée']);
                return;
            }

            $iconStyle = App::POST('icon_style', 'fa');
            $iconPack = App::POST('icon_pack', '');
            $enableAutoReplace = App::POST('enable_auto_replace', false);
            $customMappings = App::POST('custom_mappings', '{}');

            // Validation des mappings JSON
            $decodedMappings = json_decode($customMappings, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo json_encode(['success' => false, 'message' => 'Format JSON invalide pour les mappings']);
                return;
            }

            $this->setConfig('icon_style', $iconStyle);
            $this->setConfig('icon_pack', $iconPack);
            $this->setConfig('enable_auto_replace', $enableAutoReplace);
            $this->setConfig('custom_mappings', $customMappings);

            echo json_encode(['success' => true, 'message' => 'Paramètres sauvegardés avec succès']);
        }
    }
};
