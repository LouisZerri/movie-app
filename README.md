# 🎬 LouFlix

Application web de recherche et consultation de films utilisant l'API TMDB (The Movie Database). Développée avec Symfony 7 et Docker.


## 📋 Fonctionnalités

- 🔍 **Recherche de films** par titre
- 🎥 **Consultation des détails** d'un film (synopsis, acteurs, réalisateur, note, durée)
- 🔥 **Films populaires** - Les films les plus regardés du moment
- 📅 **Films à venir** - Les prochaines sorties cinéma
- 🎭 **Filtrage par genre**
- 📌 **Historique des films consultés** - Widget sidebar avec les 5 derniers films vus
- 💡 **Suggestions de recherche** - Films populaires affichés sur la page de recherche
- ⚡ **Cache intelligent** - Mise en cache des appels API (1 heure)

## 🏗️ Architecture

### Stack Technique

- **Backend** : Symfony 7.2, PHP 8.4
- **Frontend** : CSS pur (sans framework), JavaScript vanilla
- **API** : TMDB (The Movie Database)
- **Conteneurisation** : Docker + Docker Compose
- **Tests** : PHPUnit (unitaires, intégration, régression)

### Patterns & Concepts

- **Architecture MVC** avec Symfony
- **Services** pour la logique métier
- **Event Listeners & Subscribers** pour la gestion des événements
- **Twig Extensions** pour les fonctionnalités de templating
- **Dependency Injection** native Symfony
- **Cache HTTP** avec Symfony Cache Component

## 🚀 Installation

### Prérequis

- Docker Desktop installé
- Git
- Compte TMDB pour obtenir une clé API (gratuit)

### Étapes d'installation

1. **Cloner le repository**
```bash
git clone https://github.com/votre-username/louflix.git
cd louflix
```

2. **Obtenir une clé API TMDB**
   - Créer un compte sur [themoviedb.org](https://www.themoviedb.org/signup)
   - Aller dans **Paramètres → API**
   - Demander une clé API (gratuite)

3. **Configurer l'environnement**
```bash
# Créer le fichier .env.local
cp .env .env.local

# Éditer .env.local et ajouter votre clé API
nano .env.local
```

Ajouter dans `.env.local` :
```env
TMDB_API_KEY=votre_cle_api_ici
```

4. **Démarrer les conteneurs Docker**
```bash
docker-compose up -d --build
```

5. **Installer les dépendances**
```bash
# Composer (PHP)
docker exec -it louflix_web composer install

# NPM (Assets)
docker exec -it louflix_web npm install
```

6. **Compiler les assets**
```bash
docker exec -it louflix_web npm run build
```

7. **Vider le cache**
```bash
docker exec -it louflix_web php bin/console cache:clear
```

8. **Accéder à l'application**

Ouvrir votre navigateur : **http://localhost:8080**

## 🧪 Tests

L'application dispose d'une suite complète de tests :

### Lancer tous les tests
```bash
docker exec -it louflix_web php bin/phpunit
```

### Tests par catégorie

**Tests unitaires** (Services, Listeners, Extensions)
```bash
docker exec -it louflix_web php bin/phpunit tests/Unit
```

**Tests d'intégration** (Contrôleurs, Navigation)
```bash
docker exec -it louflix_web php bin/phpunit tests/Integration
```

**Tests de régression** (UI, Structure HTML, Performance)
```bash
docker exec -it louflix_web php bin/phpunit tests/Regression
```

### Tests spécifiques
```bash
# Un fichier de test particulier
docker exec -it louflix_web php bin/phpunit tests/Unit/Service/TmdbApiServiceTest.php

# Avec détails
docker exec -it louflix_web php bin/phpunit --testdox

# Avec couverture de code (si Xdebug activé)
docker exec -it louflix_web php bin/phpunit --coverage-html coverage/
```

### Configuration des tests

Pour les tests, créer `.env.test` :
```env
APP_ENV=test
APP_SECRET=test_secret
TMDB_API_KEY=votre_cle_api
```

## 📁 Structure du projet
```
louflix/
├── assets/
│   ├── app.js              # JavaScript principal
│   └── styles/
│       └── app.css         # Styles globaux (CSS pur)
├── config/
│   ├── packages/           # Configuration Symfony
│   ├── routes.yaml         # Routes de l'application
│   └── services.yaml       # Configuration des services
├── docker/
│   └── apache/
│       └── vhost.conf      # Configuration Apache
├── public/
│   └── index.php           # Point d'entrée
├── src/
│   ├── Controller/
│   │   └── MovieController.php
│   ├── EventListener/
│   │   └── RecentlyViewedMoviesListener.php
│   ├── EventSubscriber/
│   │   └── SearchSuggestionSubscriber.php
│   ├── Service/
│   │   └── TmdbApiService.php
│   └── Twig/
│       └── RecentMoviesExtension.php
├── templates/
│   ├── base.html.twig      # Template de base
│   └── movie/
│       ├── index.html.twig
│       ├── search.html.twig
│       ├── details.html.twig
│       └── upcoming.html.twig
├── tests/
│   ├── Unit/               # Tests unitaires
│   ├── Integration/        # Tests d'intégration
│   └── Regression/         # Tests de régression
├── docker-compose.yml
├── Dockerfile
└── README.md
```

## 🔧 Commandes utiles

### Docker
```bash
# Démarrer les conteneurs
docker-compose up -d

# Arrêter les conteneurs
docker-compose down

# Reconstruire les conteneurs
docker-compose up -d --build

# Voir les logs
docker-compose logs -f

# Accéder au conteneur web
docker exec -it louflix_web bash
```

### Symfony
```bash
# Vider le cache
docker exec -it louflix_web php bin/console cache:clear

# Lister les routes
docker exec -it louflix_web php bin/console debug:router

# Lister les services
docker exec -it louflix_web php bin/console debug:container

# Voir les événements
docker exec -it louflix_web php bin/console debug:event-dispatcher

# Debug d'un service
docker exec -it louflix_web php bin/console debug:autowiring TmdbApiService
```

### Assets (Webpack Encore)
```bash
# Mode développement
docker exec -it louflix_web npm run dev

# Mode watch (recompilation automatique)
docker exec -it louflix_web npm run watch

# Mode production
docker exec -it louflix_web npm run build
```

### Composer
```bash
# Installer les dépendances
docker exec -it louflix_web composer install

# Mettre à jour les dépendances
docker exec -it louflix_web composer update

# Ajouter un package
docker exec -it louflix_web composer require vendor/package
```

## 📚 API Utilisée

**TMDB (The Movie Database)**
- Documentation : https://developers.themoviedb.org/3
- Endpoints utilisés :
  - `/movie/popular` - Films populaires
  - `/movie/upcoming` - Films à venir
  - `/movie/{id}` - Détails d'un film
  - `/search/movie` - Recherche de films
  - `/discover/movie` - Découverte par genre
  - `/genre/movie/list` - Liste des genres


## 🧩 Composants clés

### Services

- **TmdbApiService** : Gestion des appels API avec cache HTTP

### Event Listeners

- **RecentlyViewedMoviesListener** : Sauvegarde les films consultés en session

### Event Subscribers

- **SearchSuggestionSubscriber** : Ajoute des suggestions de films populaires

### Twig Extensions

- **RecentMoviesExtension** : Fonction Twig `get_recent_movies()` pour afficher l'historique

## 🐛 Dépannage

### L'application ne démarre pas
```bash
# Vérifier les logs
docker-compose logs -f web

# Vérifier que les ports ne sont pas utilisés
lsof -i :8080
```

### Erreur 401 de l'API TMDB
- Vérifier que la clé API est correcte dans `.env.local`
- Vider le cache : `docker exec -it louflix_web php bin/console cache:clear`

### Assets non chargés
```bash
# Recompiler les assets
docker exec -it louflix_web npm run build

# Vérifier les permissions
docker exec -it louflix_web chmod -R 777 public/build
```

### Tests qui échouent
```bash
# Vérifier la configuration de test
docker exec -it louflix_web php bin/console debug:config framework --env=test

# Vider le cache de test
docker exec -it louflix_web php bin/console cache:clear --env=test
```

## 📝 Licence

Projet développé dans le cadre d'un test technique
