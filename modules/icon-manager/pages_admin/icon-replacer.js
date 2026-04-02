/*
 * Plugin Icon Manager - Remplacement automatique des icônes
 */

(function() {
    'use strict';

    if (!window.IconManagerConfig || !window.IconManagerConfig.enabled) {
        return;
    }

    const config = window.IconManagerConfig;
    
    class IconManager {
        constructor() {
            this.config = config;
            this.processedElements = new WeakSet();
            this.init();
        }

        init() {
            this.setupMutationObserver();
            this.processExistingIcons();
            document.addEventListener('DOMContentLoaded', () => {
                this.processExistingIcons();
                this.showDetectionSummary();
            });
        }

        showDetectionSummary() {}

        setupMutationObserver() {
            const observer = new MutationObserver(mutations => {
                mutations.forEach(mutation => {
                    mutation.addedNodes.forEach(node => {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            this.processElement(node);
                        }
                    });
                });
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }

        processExistingIcons() {
            document.querySelectorAll('i[class*="fa-"]').forEach(element => {
                this.processIconElement(element);
            });
        }

        processElement(element) {
            if (this.processedElements.has(element)) {
                return;
            }

            if (element.tagName === 'I' && element.className.includes('fa-')) {
                this.processIconElement(element);
            }

            element.querySelectorAll('i[class*="fa-"]').forEach(iconElement => {
                this.processIconElement(iconElement);
            });

            this.processedElements.add(element);
        }

        processIconElement(element) {
            if (this.processedElements.has(element)) {
                return;
            }

            if (element.className.includes('icon-preview-exclude')) {
                return;
            }

            const originalClass = element.className;
            const newClass = this.generateNewIconClass(originalClass);
        
            if (newClass !== originalClass) {
                element.className = newClass;
                this.processedElements.add(element);
            }
        }

        generateNewIconClass(originalClass) {
            if (!originalClass.includes('fa-')) {
                return originalClass;
            }

            // Ignorer les icônes Font Awesome Brands (fab)
            if (originalClass.includes('fab') || originalClass.includes('fa-brands')) {
                return originalClass;
            }

            // Extraire les classes non-FontAwesome pour les préserver
            const nonFAClasses = this.extractNonFontAwesomeClasses(originalClass);
            
            // Extraire le nom de l'icône (la classe qui n'est pas un style ou utilitaire)
            const iconNameMatch = originalClass.match(/(fa-[a-zA-Z0-9-]+)/);
            if (!iconNameMatch) {
                return originalClass;
            }

            let iconName = iconNameMatch[1];
            
            // Liste des classes de style et utilitaires à ignorer lors de l'extraction du nom d'icône
            const utilityAndStyleClasses = [
                'fa-fw', 'fa-spin', 'fa-pulse', 'fa-border', 'fa-inverse', 'fa-stack', 'fa-stack-1x', 'fa-stack-2x',
                'fa-lg', 'fa-xs', 'fa-sm', 'fa-2x', 'fa-3x', 'fa-4x', 'fa-5x', 'fa-6x', 'fa-7x', 'fa-8x', 'fa-9x', 'fa-10x',
                'fas', 'far', 'fal', 'fat', 'fad', 'fab', 'fa-solid', 'fa-regular', 'fa-light', 'fa-thin', 'fa-duotone', 'fa-brands'
            ];
            
            // Si le premier match est une classe de style/utilitaire, chercher la vraie icône
            if (utilityAndStyleClasses.includes(iconName)) {
                const allMatches = originalClass.match(/(fa-[a-zA-Z0-9-]+)/g);
                if (allMatches) {
                    for (let match of allMatches) {
                        if (!utilityAndStyleClasses.includes(match)) {
                            iconName = match;
                            break;
                        }
                    }
                }
            }
            
            // Extraire les classes utilitaires FontAwesome (sans les styles)
            const utilityClasses = this.extractUtilityClasses(originalClass);
            
            // Construire la nouvelle classe en remplaçant complètement les styles FontAwesome
            let newClass = this.config.iconStyle;
            
            // Ajouter le pack si configuré
            if (this.config.iconPack && this.config.iconPack.trim()) {
                newClass += ' ' + this.config.iconPack.trim();
            }
            
            // Ajouter le nom de l'icône
            newClass += ' ' + iconName;
            
            // Ajouter les classes utilitaires FontAwesome (sans les styles)
            if (utilityClasses.length > 0) {
                newClass += ' ' + utilityClasses.join(' ');
            }

            // Ajouter les classes non-FontAwesome originales
            if (nonFAClasses.length > 0) {
                newClass += ' ' + nonFAClasses.join(' ');
            }

            return newClass;
        }

        extractFontAwesomeClasses(originalClass) {
            // Extraire toutes les classes qui commencent par 'fa-'
            const faClassRegex = /\b(fa-[a-zA-Z0-9-]+)\b/g;
            const faClasses = [];
            let match;
            
            while ((match = faClassRegex.exec(originalClass)) !== null) {
                faClasses.push(match[1]);
            }
            
            return faClasses;
        }

        extractNonFontAwesomeClasses(originalClass) {
            // Extraire toutes les classes qui ne commencent PAS par 'fa-'
            const allClasses = originalClass.split(/\s+/);
            const nonFAClasses = allClasses.filter(className => {
                return className.trim() && !className.startsWith('fa-');
            });
            
            return nonFAClasses;
        }

        extractUtilityClasses(originalClass) {
            const utilityClasses = [];
            const utilityPatterns = [
                'fa-fw', 'fa-spin', 'fa-pulse', 'fa-border', 'fa-inverse', 'fa-stack', 'fa-stack-1x', 'fa-stack-2x',
                'fa-lg', 'fa-xs', 'fa-sm', 'fa-2x', 'fa-3x', 'fa-4x', 'fa-5x', 'fa-6x', 'fa-7x', 'fa-8x', 'fa-9x', 'fa-10x'
            ];

            const stylePatterns = [
                'fas', 'far', 'fal', 'fat', 'fad', 'fab',
                'fa-solid', 'fa-regular', 'fa-light', 'fa-thin', 'fa-duotone', 'fa-brands'
            ];

            // Traitement spécial pour les icônes fa-regular
            const isRegularIcon = originalClass.includes('fa-regular') || originalClass.includes('far');
            
            utilityPatterns.forEach(pattern => {
                if (originalClass.includes(pattern)) {
                    const isStyle = stylePatterns.some(style => 
                        originalClass.includes(style + ' ' + pattern) || 
                        originalClass.includes(pattern + ' ' + style)
                    );
                    if (!isStyle && !utilityClasses.includes(pattern)) {
                        utilityClasses.push(pattern);
                    }
                }
            });

            // Pour les icônes fa-regular, s'assurer que les classes utilitaires sont préservées
            if (isRegularIcon) {
                // Ajouter des classes utilitaires spécifiques si elles existent
                const regularUtilityClasses = ['me-2', 'me-1', 'me-3', 'ms-2', 'ms-1', 'ms-3'];
                regularUtilityClasses.forEach(utilClass => {
                    if (originalClass.includes(utilClass) && !utilityClasses.includes(utilClass)) {
                        utilityClasses.push(utilClass);
                    }
                });
            }

            return utilityClasses;
        }

        processElementPublic(element) {
            this.processElement(element);
        }

        processAll() {
            this.processExistingIcons();
        }

        getConfig() {
            return this.config;
        }

        updateConfig(newConfig) {
            this.config = { ...this.config, ...newConfig };
        }
    }

    const iconManager = new IconManager();
    window.IconManager = iconManager;
    window.testIconReplacement = function() { 
        iconManager.processAll(); 
    };
    window.processIconElement = function(element) { 
        iconManager.processElementPublic(element); 
    };
    
    // Fonction de test spécifique pour les icônes fa-regular
    window.testRegularIconSupport = function() {
        console.log('=== Test du support fa-regular ===');
        
        // Test avec l'exemple fourni
        const testElement = document.createElement('i');
        testElement.className = 'fa fa-browser me-2';
        
        console.log('Classe originale:', testElement.className);
        iconManager.processIconElement(testElement);
        console.log('Classe après traitement:', testElement.className);
        
        // Test avec fa-regular explicite
        const testElement2 = document.createElement('i');
        testElement2.className = 'fa-regular fa-browser me-2';
        
        console.log('Classe originale (fa-regular):', testElement2.className);
        iconManager.processIconElement(testElement2);
        console.log('Classe après traitement (fa-regular):', testElement2.className);
        
        return {
            original: 'fa fa-browser me-2',
            processed: testElement.className,
            originalRegular: 'fa-regular fa-browser me-2',
            processedRegular: testElement2.className
        };
    };

})();