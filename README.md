# 🎮 Game Hub - Plateforme de Jeux Multijoueur PHP

Plateforme de jeux en ligne multijoueur développée en PHP avec MySQL.
cette platforme a été crée avec l'accompagnement d'une ia (claude dans ce cas) 

## 🎯 Jeux disponibles

1. **🎯 Loup Touche-Touche** - Déplace-toi pour échapper au loup
2. **🌙 Loup-Garou** - Jeu de rôle classique avec votes
3. **🏎️ Course de Voitures** - Clique pour accélérer et gagner
4. **🏆 Quiz Party** - Questions de culture générale
5. **⚡ Jeu de Réflexes** - Teste ta vitesse de réaction
(de nouveaux jeu arrive)

## 📋 Prérequis

- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Serveur web (Apache, Nginx)
- Extension PDO PHP activée

## 🚀 Installation

### 1. Créer la base de données

```bash
mysql -u root -p
```

Puis exécuter le contenu du fichier `database.sql` :

```sql
CREATE DATABASE game_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- (copier le reste du fichier database.sql)
```

### 2. Configuration de la connexion

Éditer les fichiers suivants et modifier les paramètres de connexion :

**index.php** (lignes 5-8) :
```php
$host = 'localhost';
$dbname = 'game_platform';
$username = 'root';
$password = 'votre_mot_de_passe';
```

**update_activity.php** (lignes 8-11) : même configuration

### 3. Structure des fichiers

Créer l'arborescence suivante :

```
game-platform/
├── index.php                 # Fichier principal
├── update_activity.php       # Mise à jour de l'activité
├── database.sql              # Schéma de la base de données
├── README.md                 # Ce fichier
└── games/                    # Dossier des jeux
    ├── tag.php              # Loup Touche-Touche
    ├── werewolf.php         # Loup-Garou
    ├── racing.php           # Course de Voitures
    ├── quiz.php             # Quiz Party
    └── reaction.php         # Jeu de Réflexes
```

### 4. Déploiement

#### Option A : Serveur local (XAMPP, WAMP, MAMP)

1. Copier tous les fichiers dans le dossier `htdocs` (XAMPP) ou `www` (WAMP)
2. Démarrer Apache et MySQL
3. Accéder à `http://localhost/game-platform/`

#### Option B : Serveur Linux

```bash
# Copier les fichiers dans le dossier web
sudo cp -r game-platform/ /var/www/html/

# Définir les permissions
sudo chown -R www-data:www-data /var/www/html/game-platform
sudo chmod -R 755 /var/www/html/game-platform
```

## 🎮 Utilisation

1. Ouvrir `http://localhost/game-platform/` dans votre navigateur
2. Entrer un pseudo pour se connecter
3. Choisir un jeu parmi les 5 disponibles
4. Jouer avec les autres joueurs connectés !

## 🔧 Personnalisation

### Ajouter des questions au Quiz

Éditer `games/quiz.php` et modifier le tableau `$questions` :

```php
$questions = [
    [
        'q' => 'Votre question ?', 
        'options' => ['Réponse 1', 'Réponse 2', 'Réponse 3', 'Réponse 4'], 
        'answer' => 0  // Index de la bonne réponse (0-3)
    ],
    // Ajouter d'autres questions...
];
```

### Modifier les rôles du Loup-Garou

Éditer `games/werewolf.php` ligne 3 :

```php
$roles = ['Loup-Garou', 'Loup-Garou', 'Voyante', 'Villageois', 'Villageois', 'Chasseur', 'Cupidon'];
```

### Changer le délai d'inactivité

Éditer `index.php` ligne 29 (par défaut 5 minutes) :

```php
$pdo->exec("DELETE FROM players WHERE last_activity < DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
```

## 🐛 Dépannage

### "Erreur de connexion à la base de données"
- Vérifier que MySQL est démarré
- Vérifier les identifiants de connexion dans `index.php`
- Vérifier que la base `game_platform` existe

### "Call to undefined function json_encode()"
- Activer l'extension JSON PHP dans `php.ini`

### Les joueurs ne s'actualisent pas
- Vérifier que le fichier `update_activity.php` est accessible
- Vérifier la console JavaScript pour les erreurs

### Page blanche
- Activer l'affichage des erreurs PHP :
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 🔒 Sécurité

Pour un environnement de production :

1. Utiliser des identifiants MySQL sécurisés
2. Utiliser HTTPS
3. Implémenter un système de captcha
4. Limiter le nombre de connexions par IP
5. Valider et échapper toutes les entrées utilisateur
6. Utiliser des sessions sécurisées

## 📝 Améliorations possibles

- [ ] Système de salles de jeu privées
- [ ] Chat en temps réel entre joueurs
- [ ] Système de classement global
- [ ] Notifications WebSocket pour le multijoueur en temps réel
- [ ] Mode spectateur
- [ ] Personnalisation des avatars
- [ ] Système d'amis
- [ ] Statistiques détaillées des joueurs

## 📄 Licence

Ce projet est libre d'utilisation pour des projets personnels et éducatifs.

## 👨‍💻 Support

Pour toute question ou problème, créer une issue sur le dépôt du projet.

---

Bon jeu ! 🎮🎉
