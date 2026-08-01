# SmartEco Academy

![Statut](https://img.shields.io/badge/statut-en%20développement-f59e0b)
![PHP](https://img.shields.io/badge/PHP-%3E%3D%208.3-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?logo=mysql&logoColor=white)
![Vite](https://img.shields.io/badge/Vite-8-646CFF?logo=vite&logoColor=white)

**SmartEco Academy** est une plateforme web éducative et professionnelle développée avec Laravel.  
Le projet vise à réunir, dans un même environnement, des services d’accompagnement, de formation et d’aide à l’insertion professionnelle.

Le dépôt est actuellement en **développement actif**. Certaines fonctionnalités décrites dans la feuille de route ne sont pas encore disponibles dans la branche principale.

---

## Sommaire

- [Objectifs](#objectifs)
- [État actuel](#état-actuel)
- [Fonctionnalités prévues](#fonctionnalités-prévues)
- [Technologies](#technologies)
- [Prérequis](#prérequis)
- [Installation locale](#installation-locale)
- [Configuration MySQL](#configuration-mysql)
- [Lancement du projet](#lancement-du-projet)
- [Commandes utiles](#commandes-utiles)
- [Tests et qualité](#tests-et-qualité)
- [Structure du projet](#structure-du-projet)
- [Module de veille d’emploi](#module-de-veille-demploi)
- [Organisation du développement](#organisation-du-développement)
- [Collaboration avec ChatGPT et Claude](#collaboration-avec-chatgpt-et-claude)
- [Sécurité](#sécurité)
- [Contribution](#contribution)
- [Feuille de route](#feuille-de-route)

---

## Objectifs

SmartEco Academy a pour objectifs de :

- proposer une expérience numérique claire et accessible ;
- centraliser les services éducatifs et professionnels de la plateforme ;
- accompagner les utilisateurs dans le développement de leurs compétences ;
- faciliter la recherche d’opportunités adaptées à chaque profil ;
- permettre une évolution progressive vers des services intelligents et personnalisés ;
- garantir une architecture maintenable, testable et compatible avec MySQL.

---

## État actuel

La branche principale contient actuellement la base technique du projet Laravel :

- structure standard Laravel ;
- configuration Vite ;
- intégration de Tailwind CSS et Bootstrap ;
- configuration initiale des sessions, du cache et des files d’attente ;
- tests PHPUnit de base ;
- page d’accueil Laravel initiale.

> Les modules fonctionnels de SmartEco Academy seront ajoutés progressivement dans des branches dédiées, puis intégrés dans `main` après revue et validation.

---

## Fonctionnalités prévues

### Plateforme

- gestion des utilisateurs et des profils ;
- espace personnel sécurisé ;
- tableau de bord utilisateur ;
- gestion des contenus et services de la plateforme ;
- notifications internes et par email ;
- administration des données et des utilisateurs.

### Emploi et orientation

- dépôt d’un CV en PDF, DOCX ou TXT ;
- extraction des compétences et des expériences ;
- création de critères de recherche personnalisés ;
- veille automatique des offres d’emploi ;
- calcul d’un score de compatibilité entre le profil et les offres ;
- suppression des doublons ;
- alertes pour les nouvelles offres pertinentes ;
- historique des offres détectées ;
- activation, suspension et modification d’une veille.

### Évolutions possibles

- recommandations personnalisées ;
- analyse des compétences manquantes ;
- génération de conseils d’amélioration du CV ;
- tableau de bord statistique ;
- intégration de sources d’offres autorisées ;
- services d’intelligence artificielle avec validation et contrôle des données.

---

## Technologies

### Backend

- PHP 8.3 ou supérieur ;
- Laravel 13 ;
- Eloquent ORM ;
- Laravel Queue ;
- Laravel Scheduler ;
- PHPUnit.

### Base de données

- MySQL 8.x recommandé ;
- moteur InnoDB ;
- encodage `utf8mb4`.

### Frontend

- Blade ;
- Vite 8 ;
- Tailwind CSS 4 ;
- Bootstrap 5 ;
- JavaScript ES Modules.

> Le projet contient actuellement Tailwind CSS et Bootstrap. Avant de développer plusieurs interfaces, l’équipe doit définir dans la documentation d’architecture lequel sera utilisé comme système principal afin d’éviter des styles contradictoires.

---

## Prérequis

Installez les outils suivants :

- PHP 8.3 ou supérieur ;
- Composer 2 ;
- Node.js 20 ou supérieur ;
- npm ;
- MySQL 8.x ;
- Git.

Extensions PHP recommandées :

- `pdo_mysql` ;
- `mbstring` ;
- `openssl` ;
- `tokenizer` ;
- `xml` ;
- `ctype` ;
- `json` ;
- `fileinfo`.

---

## Installation locale

### 1. Cloner le dépôt

```bash
git clone https://github.com/sircompus/smarteco-academy.git
cd smarteco-academy
```

### 2. Installer les dépendances PHP

```bash
composer install
```

### 3. Installer les dépendances frontend

```bash
npm install
```

### 4. Créer le fichier d’environnement

Sous Linux ou macOS :

```bash
cp .env.example .env
```

Sous Windows :

```powershell
Copy-Item .env.example .env
```

### 5. Générer la clé Laravel

```bash
php artisan key:generate
```

### 6. Configurer MySQL

Modifiez les variables de base de données dans `.env` avant d’exécuter les migrations.

### 7. Créer les tables

```bash
php artisan migrate
```

### 8. Compiler les ressources

```bash
npm run build
```

---

## Configuration MySQL

Créez une base de données dédiée :

```sql
CREATE DATABASE smarteco_academy
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
```

Exemple de configuration `.env` :

```env
APP_NAME="SmartEco Academy"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

APP_LOCALE=fr
APP_FALLBACK_LOCALE=fr
APP_FAKER_LOCALE=fr_FR

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smarteco_academy
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

Après modification :

```bash
php artisan config:clear
php artisan migrate
```

Pour préparer les tables utilisées par les files d’attente, vérifiez d’abord si leurs migrations sont déjà présentes. Dans le cas contraire :

```bash
php artisan make:queue-table
php artisan migrate
```

---

## Lancement du projet

### Mode de développement complet

Le projet contient une commande Composer qui lance le serveur Laravel, la file d’attente, les journaux et Vite :

```bash
composer run dev
```

L’application sera généralement disponible à l’adresse :

```text
http://127.0.0.1:8000
```

### Lancement manuel

Terminal 1 :

```bash
php artisan serve
```

Terminal 2 :

```bash
npm run dev
```

Terminal 3 :

```bash
php artisan queue:work
```

---

## Commandes utiles

```bash
# Vider les caches Laravel
php artisan optimize:clear

# Exécuter les migrations
php artisan migrate

# Annuler la dernière migration
php artisan migrate:rollback

# Recréer la base en environnement local
php artisan migrate:fresh

# Lancer le worker de la file d’attente
php artisan queue:work

# Afficher les routes
php artisan route:list

# Compiler les ressources de production
npm run build

# Lancer le serveur frontend Vite
npm run dev
```

---

## Tests et qualité

### Exécuter les tests

```bash
composer test
```

Ou :

```bash
php artisan test
```

### Vérifier le style PHP

```bash
./vendor/bin/pint --test
```

### Corriger automatiquement le style PHP

```bash
./vendor/bin/pint
```

### Vérification recommandée avant une Pull Request

```bash
php artisan optimize:clear
./vendor/bin/pint --test
php artisan test
npm run build
```

---

## Structure du projet

```text
smarteco-academy/
├── app/                  # Logique métier, modèles, services et tâches
├── bootstrap/            # Initialisation de Laravel
├── config/               # Configuration de l’application
├── database/
│   ├── factories/        # Fabriques de données de test
│   ├── migrations/       # Structure de la base MySQL
│   └── seeders/          # Données initiales
├── public/               # Point d’entrée public
├── resources/
│   ├── css/              # Feuilles de style
│   ├── js/               # JavaScript
│   └── views/            # Vues Blade
├── routes/               # Routes web, console et autres routes
├── storage/              # Fichiers, logs et cache
├── tests/                # Tests automatisés
├── .env.example          # Exemple de configuration
├── artisan               # Interface de commande Laravel
├── composer.json         # Dépendances PHP
├── package.json          # Dépendances frontend
└── vite.config.js        # Configuration Vite
```

---

## Module de veille d’emploi

Le module de veille sera intégré nativement dans Laravel afin de réutiliser :

- l’authentification du site ;
- les utilisateurs existants ;
- la base MySQL ;
- Eloquent ;
- Laravel Queue ;
- Laravel Scheduler ;
- les notifications Laravel.

### Flux prévu

```text
Utilisateur
    │
    ├── dépose son CV
    ├── choisit ses critères
    └── démarre une veille
             │
             ▼
      Extraction du profil
             │
             ▼
      Recherche périodique
             │
             ▼
      Scoring des offres
             │
             ▼
      Déduplication MySQL
             │
             ▼
      Notification utilisateur
```

### Tables envisagées

```text
users
cv_profiles
job_watches
job_watch_keywords
job_sources
job_offers
job_matches
job_notifications
```

### Traitements planifiés

Laravel Scheduler pourra déclencher une commande de veille :

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('jobs:monitor')
    ->everyFifteenMinutes()
    ->withoutOverlapping();
```

Le serveur devra exécuter le planificateur Laravel :

```cron
* * * * * cd /chemin/vers/smarteco-academy && php artisan schedule:run >> /dev/null 2>&1
```

---

## Organisation du développement

### Branches

- `main` : version stable ;
- `develop` : intégration, si cette branche est adoptée ;
- `feature/*` : nouvelles fonctionnalités ;
- `fix/*` : corrections ;
- `test/*` : tests ;
- `docs/*` : documentation ;
- `ai/chatgpt-*` : travaux confiés à ChatGPT ;
- `ai/claude-*` : travaux confiés à Claude.

Exemples :

```bash
git checkout -b feature/job-watch
git checkout -b fix/mysql-connection
git checkout -b docs/update-readme
```

### Convention de commits

```text
feat: ajouter une fonctionnalité
fix: corriger un problème
docs: modifier la documentation
test: ajouter ou corriger des tests
refactor: restructurer sans changer le comportement
style: corriger le formatage
chore: maintenance technique
```

Exemples :

```bash
git commit -m "feat(job-watch): add watch creation workflow"
git commit -m "fix(database): correct MySQL connection settings"
git commit -m "docs: improve installation instructions"
```

---

## Collaboration avec ChatGPT et Claude

Pour éviter les divergences entre assistants IA :

1. utiliser GitHub comme source unique de vérité ;
2. ne jamais faire travailler deux assistants sur le même fichier au même moment ;
3. créer une branche distincte pour chaque tâche ;
4. documenter les décisions dans `docs/DECISIONS.md` ;
5. attribuer les fichiers dans `docs/TASKS.md` ;
6. faire développer une fonctionnalité par un assistant ;
7. faire relire et tester cette branche par l’autre ;
8. fusionner uniquement après réussite des tests.

Répartition recommandée :

| Domaine | Responsable principal | Revue |
|---|---|---|
| Architecture Laravel | ChatGPT | Claude |
| MySQL et Eloquent | ChatGPT | Claude |
| Files et planification | ChatGPT | Claude |
| Interface utilisateur | Claude | ChatGPT |
| Tests | Claude | ChatGPT |
| Sécurité | Claude | ChatGPT |
| Documentation | Claude | ChatGPT |

Documents recommandés à la racine ou dans `docs/` :

```text
AGENTS.md
CLAUDE.md
docs/PROJECT_CONTEXT.md
docs/ARCHITECTURE.md
docs/DECISIONS.md
docs/TASKS.md
docs/API_CONTRACT.md
docs/DATABASE_SCHEMA.md
```

---

## Sécurité

- ne jamais versionner le fichier `.env` ;
- ne jamais enregistrer une clé API dans le code ;
- valider tous les fichiers importés ;
- limiter les formats et la taille des CV ;
- conserver les CV sur un disque privé ;
- vérifier qu’un utilisateur ne peut accéder qu’à ses propres veilles ;
- utiliser des Policies Laravel ;
- chiffrer les données sensibles lorsque cela est nécessaire ;
- limiter les requêtes avec le rate limiting ;
- utiliser uniquement des API ou des sources d’offres autorisées ;
- journaliser les erreurs sans exposer de données personnelles ;
- prévoir une politique de suppression des CV et profils.

Avant la production :

```env
APP_ENV=production
APP_DEBUG=false
```

Puis :

```bash
php artisan optimize
```

---

## Contribution

1. créer une issue ou identifier une tâche ;
2. créer une branche depuis la dernière version de `main` ;
3. développer uniquement le périmètre prévu ;
4. ajouter ou adapter les tests ;
5. exécuter les contrôles de qualité ;
6. pousser la branche ;
7. ouvrir une Pull Request ;
8. demander une revue ;
9. corriger les problèmes confirmés ;
10. fusionner après validation.

```bash
git checkout main
git pull origin main
git checkout -b feature/nom-de-la-fonctionnalite
```

---

## Feuille de route

### Phase 1 — Fondations

- [x] Initialiser Laravel ;
- [x] configurer Composer, Vite et les tests ;
- [ ] configurer définitivement MySQL ;
- [ ] définir le système CSS principal ;
- [ ] mettre en place l’authentification ;
- [ ] créer la documentation d’architecture ;
- [ ] ajouter l’intégration continue GitHub Actions.

### Phase 2 — Comptes et profils

- [ ] créer les profils utilisateurs ;
- [ ] sécuriser les espaces personnels ;
- [ ] permettre le dépôt et la gestion des CV ;
- [ ] mettre en place les autorisations Laravel.

### Phase 3 — Veille d’emploi

- [ ] créer les migrations du module ;
- [ ] créer les modèles Eloquent ;
- [ ] intégrer les sources d’offres autorisées ;
- [ ] développer le moteur de compatibilité ;
- [ ] planifier les recherches ;
- [ ] ajouter les notifications ;
- [ ] construire le tableau de bord.

### Phase 4 — Qualité et production

- [ ] renforcer les tests ;
- [ ] réaliser un audit de sécurité ;
- [ ] optimiser les performances ;
- [ ] documenter le déploiement ;
- [ ] préparer les sauvegardes MySQL ;
- [ ] déployer la première version stable.

---

## Dépôt

```text
https://github.com/sircompus/smarteco-academy
```

---

<p align="center">
  <strong>SmartEco Academy</strong><br>
  Apprendre, développer ses compétences et accéder à de nouvelles opportunités.
</p>
