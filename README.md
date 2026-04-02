# 🚀 EvoCMS

<div align="center">

![EvoCMS Logo](assets/img/evo-logo.png)

**Un CMS moderne et puissant pour créer des sites web dynamiques**

[![Version](https://img.shields.io/badge/version-1.3.0--beta-blue.svg)](https://github.com/evocms-project/evocms)
[![PHP](https://img.shields.io/badge/PHP-7.1%2B-777BB4.svg)](https://php.net)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3.3-7952B3.svg)](https://getbootstrap.com)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

[📖 Documentation](#-documentation) • [⚡ Installation](#-installation-rapide) • [🎯 Fonctionnalités](#-fonctionnalités) • [🤝 Contribution](#-contribution)

</div>

---

## 📋 À propos

**EvoCMS** est un système de gestion de contenu (CMS) moderne et flexible, conçu pour offrir une expérience de développement et d'administration exceptionnelle. Construit avec PHP et Bootstrap 5.3.3, il combine puissance et simplicité pour créer des sites web professionnels.

### ✨ Pourquoi EvoCMS ?

- 🎨 **Interface moderne** avec Bootstrap 5.3.3
- 🔧 **Architecture modulaire** extensible
- 🚀 **Performance optimisée** 
- 🛡️ **Sécurité renforcée**
- 🌍 **Multilingue** (Français/Anglais)
- 📱 **Responsive design**

---

## 🎯 Fonctionnalités

### 🏠 **Gestion de Contenu**
- **Pages personnalisées** avec éditeur WYSIWYG
- **Système de blog** intégré
- **Galerie d'images** avec upload multiple
- **Gestion de fichiers** et téléchargements

### 👥 **Gestion des Utilisateurs**
- **Système d'authentification** complet
- **Groupes et permissions** granulaires
- **Profils utilisateurs** personnalisables
- **Système d'amis** et messagerie

### 💬 **Communication**
- **Forums** avec modération
- **Système de commentaires**
- **Messagerie privée**
- **Notifications** en temps réel

### 🎮 **Gaming Features**
- **Serveurs de jeux** (Minecraft, CS:GO, etc.)
- **Statistiques** en temps réel
- **Système de votes**
- **Intégration Steam**

### ⚙️ **Administration**
- **Interface d'administration** intuitive
- **Sauvegardes automatiques**
- **Gestion des modules**
- **Statistiques détaillées**

---

## ⚡ Installation Rapide

### 📋 Prérequis

- **PHP** 7.1 ou supérieur
- **MySQL** 5.5+ / **MariaDB** 5.5+ / **SQLite3**
- **Serveur web** (Apache/Nginx)

### 🚀 Installation

1. **Cloner le repository**
```bash
git clone https://github.com/evocms-project/evocms.git
cd evocms
```

2. **Configurer les permissions**
```bash
chmod 755 upload/
chmod 755 backups/
chmod 755 logs/
```

3. **Accéder à l'installation**
```
http://votre-domaine.com/install/
```

4. **Suivre l'assistant d'installation**
   - Configuration de la base de données
   - Paramètres du site
   - Création de l'administrateur

### 🐳 Docker (Optionnel)

```bash
docker-compose up -d
```

---

## 🔧 Configuration

### 🌐 Nginx

```nginx
location ~ /\.ht.+$ {
   deny all;
   return 404;
}

location ~ ^/db-.+$ {
   deny all;
   return 404;
}

location ~* ^/assets/ {
    expires 2h;
}

try_files $uri $uri/ /index.php?p=$uri&$args;
error_page 404 /index.php;
```

### 🔒 Apache (.htaccess)

Le fichier `.htaccess` est inclus et configuré automatiquement.

---

## 📖 Documentation (Bientôt dispo)

### 📚 Guides

- [📖 Guide d'installation](docs/installation.md) (Bientot disponible)
- [🎨 Personnalisation](docs/customization.md) (Bientot disponible)
- [🔌 Développement de modules](docs/modules.md) (Bientot disponible)
- [🎯 API Reference](docs/api.md) (Bientot disponible)

### 🎥 Tutoriels

- [🎬 Créer votre premier site](tutorials/first-site.md) (Bientot disponible)
- [🎨 Personnaliser le thème](tutorials/theming.md) (Bientot disponible)
- [🔌 Créer un module](tutorials/module-creation.md) (Bientot disponible)

---

## 🏗️ Architecture

```
EvoCMS/
├── 📁 admin/              # Interface d'administration
├── 📁 assets/             # Ressources statiques (CSS, JS, images)
├── 📁 includes/           # Classes et bibliothèques PHP
├── 📁 pages/              # Pages publiques
├── 📁 modules/            # Modules personnalisés
├── 📁 upload/             # Fichiers uploadés
├── 📁 backups/            # Sauvegardes automatiques
└── 📁 install/            # Assistant d'installation
```

### 🔧 Technologies

- **Backend** : PHP 7.1+ (maximum 8.2)
- **Frontend** : Bootstrap 5.3.3, jQuery
- **Base de données** : MySQL/MariaDB/SQLite3
- **Éditeur** : CKEditor 4
- **Icons** : Font Awesome 5

---

## 🎨 Thèmes et Personnalisation

EvoCMS utilise un système de thèmes flexible :

```php
// Configuration du thème
App::setConfig('theme.name', 'mon-theme');
App::setConfig('theme.logo', '/img/logo.png');
```

### 🎨 Créer un thème

1. Créer le dossier `themes/mon-theme/`
2. Ajouter les templates personnalisés
3. Configurer les styles CSS
4. Activer le thème dans l'administration

---

## 🔌 Modules

### 📦 Modules Inclus

- **Blog** : Système de blog complet
- **Forums** : Forums avec modération
- **Galerie** : Galerie d'images
- **Téléchargements** : Gestion de fichiers
- **Serveurs** : Intégration serveurs de jeux

### 🛠️ Développement de Modules

```php
class MonModule extends Module {
    public function hook_user_menu(&$items) {
        $items[] = ['Mon Menu', 'fa-icon', '/mon-lien'];
    }
}
```

---

## 🚀 Performance

### ⚡ Optimisations

- **Cache intelligent** des requêtes
- **Compression** des assets
- **Lazy loading** des images
- **Minification** CSS/JS

### 📊 Monitoring

- Statistiques détaillées
- Logs d'activité
- Monitoring des performances
- Alertes automatiques

---

## 🛡️ Sécurité

### 🔒 Fonctionnalités

- **Protection CSRF** intégrée
- **Validation** des entrées
- **Échappement** automatique
- **Permissions** granulaires
- **Audit trail** complet

### 🚨 Bonnes Pratiques

- Mise à jour régulière
- Sauvegardes automatiques
- Monitoring des logs
- Configuration sécurisée

---

## 🤝 Contribution

Nous accueillons toutes les contributions ! Voici comment participer :

### 🐛 Signaler un Bug

1. Vérifier les [issues existantes](https://github.com/evocms-project/evocms/issues)
2. Créer une nouvelle issue avec :
   - Description détaillée
   - Étapes de reproduction
   - Version d'EvoCMS
   - Logs d'erreur

### 💡 Proposer une Amélioration

1. Créer une [feature request](https://github.com/evocms-project/evocms/issues/new?template=feature_request.md)
2. Décrire l'amélioration
3. Expliquer le cas d'usage

### 🔧 Contribuer au Code

1. **Fork** le repository
2. Créer une **branche** pour votre fonctionnalité
3. **Commit** vos changements
4. **Push** vers votre fork
5. Créer une **Pull Request**

### 📝 Guidelines

- Suivre les standards PSR-12
- Ajouter des tests unitaires
- Documenter le code
- Respecter la structure existante

---

## 📄 Licence

Ce projet est sous licence **MIT**. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

---

## 👥 Équipe

### 🏆 Développeurs Principaux

- **Yan Bourgeois** (Coolternet) - Designer
- **Alex Duchesne** (Alexus) - Développeur

### 🙏 Remerciements

Merci à tous les contributeurs qui participent au développement d'EvoCMS !

---

## 📞 Support

### 💬 Communauté

- [💬 Discussions GitHub](https://github.com/evocms-project/evocms/discussions)
- [🐛 Issues](https://github.com/evocms-project/evocms/issues)

### 📧 Contact

- **Email** : coolternet@evolution-network.ca
- **Site Web** : [https://evolution-network.ca](https://evolution-network.ca)

---

## 🗺️ Roadmap

### 🎯 Version 1.4.0 (en étude)
- [ ] Support des plusieurs langues
- [ ] Amélioration des thèmes
- [ ] Modules marketplace
- [ ] Prise en charge d'encore plus de serveur de jeu

---

<div align="center">

**⭐ Si EvoCMS vous plaît, n'hésitez pas à nous donner une étoile !**

[![GitHub stars](https://img.shields.io/github/stars/evocms-project/evocms?style=social)](https://github.com/evocms-project/evocms/stargazers)
[![GitHub forks](https://img.shields.io/github/forks/evocms-project/evocms?style=social)](https://github.com/evocms-project/evocms/network)

---

*Fait avec ❤️ par l'équipe EvoCMS*

</div>