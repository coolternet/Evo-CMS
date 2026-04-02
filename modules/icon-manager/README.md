# Plugin Icon Manager pour EvoCMS

## Description

Le plugin **Icon Manager** permet de gérer automatiquement les icônes dans votre site EvoCMS. Il détecte les balises `<i>` avec des classes d'icônes et applique automatiquement les paramètres configurés dans l'administration.

## Fonctionnalités

- ✅ **Détection automatique** des balises `<i>` avec des classes d'icônes
- ✅ **Remplacement dynamique** selon les paramètres configurés
- ✅ **Interface d'administration** intuitive
- ✅ **Mappings personnalisés** pour des icônes spécifiques
- ✅ **Support des packs d'icônes** (ex: fa-duotone, fa-solid)
- ✅ **Traitement en temps réel** des éléments ajoutés dynamiquement

## Installation

1. Le module est déjà présent dans `/modules/icon-manager/`
2. Activez-le depuis l'administration EvoCMS
3. Configurez les paramètres dans **Administration > Icon Manager**

## Configuration

### Paramètres principaux

- **Style d'icône par défaut** : Style de base (ex: `fa` pour Font Awesome)
- **Pack d'icône par défaut** : Pack optionnel (ex: `fa-duotone`, `fa-solid`)
- **Remplacement automatique** : Active/désactive le plugin
- **Mappings personnalisés** : Règles spécifiques pour certaines icônes

### Exemple de configuration

```
Style : fa
Pack : fa-duotone
```

**Résultat :**
```html
<!-- Avant -->
<i class="fa fa-flag"></i>

<!-- Après -->
<i class="fa fa-duotone fa-flag"></i>
```

### Mappings personnalisés

Vous pouvez définir des règles spécifiques pour certaines icônes :

```json
{
  "fa-flag": {
    "style": "fa",
    "pack": "fa-duotone", 
    "name": "fa-flag"
  },
  "fa-check": {
    "style": "fas",
    "pack": "",
    "name": "fa-check"
  }
}
```

## Utilisation

### Dans vos templates PHP

Le plugin fonctionne automatiquement sur toutes les balises `<i>` existantes :

```php
<button class="btn btn-sm btn-danger">
    <i class="fa fa-flag"></i> Signaler
</button>
```

### En JavaScript

```javascript
// Traiter tous les éléments
window.IconManager.processAll();

// Traiter un élément spécifique
window.IconManager.processElement(document.getElementById('my-element'));

// Tester le remplacement
window.testIconReplacement();
```

## Pages d'administration

- **Paramètres** : Configuration du plugin
- **Démo** : Démonstration en temps réel du fonctionnement

## Exemples d'utilisation

### Page des commentaires

Le plugin détecte automatiquement les icônes dans la page des commentaires :

```html
<button class="btn btn-sm btn-danger">
    <i class="fa fa-flag"></i>
</button>
<button class="btn btn-sm btn-success">
    <i class="fa fa-check"></i>
</button>
<button class="btn btn-sm btn-warning">
    <i class="fa fa-ban"></i>
</button>
<button class="btn btn-sm btn-danger">
    <i class="fa fa-times"></i>
</button>
```

### Avec configuration fa-duotone

Toutes ces icônes seront automatiquement transformées en :

```html
<button class="btn btn-sm btn-danger">
    <i class="fa fa-duotone fa-flag"></i>
</button>
<button class="btn btn-sm btn-success">
    <i class="fa fa-duotone fa-check"></i>
</button>
<button class="btn btn-sm btn-warning">
    <i class="fa fa-duotone fa-ban"></i>
</button>
<button class="btn btn-sm btn-danger">
    <i class="fa fa-duotone fa-times"></i>
</button>
```

## Développement

### Structure des fichiers

```
modules/icon-manager/
├── index.php                 # Classe principale du module
├── module.json              # Configuration du module
├── pages_admin/
│   ├── index.php            # Page d'accueil (redirection)
│   ├── settings.php         # Interface de configuration
│   ├── demo.php             # Page de démonstration
│   └── icon-replacer.js     # Plugin JavaScript
└── README.md                # Documentation
```

### Hooks utilisés

- `hook_admin_menu()` : Ajout des menus d'administration
- `hook_head()` : Injection de la configuration JavaScript
- `hook_footer()` : Chargement du script de remplacement
- `hook_ajax()` : Gestion des requêtes AJAX

## Support

Pour toute question ou problème, consultez la documentation EvoCMS ou contactez l'équipe de développement.

## Version

**Version actuelle :** 1.0.0  
**Compatibilité :** EvoCMS 2.x  
**Dernière mise à jour :** 10 janvier 2025
