$path0 = "C:\laragon\www\SEA\AGENTS.md"
$content0 = @'
# AGENTS.md — Instructions pour tout assistant IA (Claude, ChatGPT, etc.)

## Règle n°1 : GitHub est la seule source de vérité

Avant de proposer quoi que ce soit, lis dans cet ordre :
1. `docs/CURRENT_STATE.md` — état exact du projet à l'instant présent
2. `docs/HANDOFF.md` — ce que le dernier assistant a fait, et quelle est la prochaine action
3. `docs/TASKS.md` — liste des tâches, statuts
4. `docs/ARCHITECTURE.md` — comment le projet est structuré
5. `docs/DECISIONS.md` — décisions déjà prises, pour ne pas les reproposer

**N'invente jamais qu'une fonctionnalité est terminée si elle n'apparaît pas comme telle dans ces fichiers.**

## Contrainte technique critique : aucun assistant n'a d'accès direct à GitHub

Aucun assistant IA sur ce projet (Claude comme ChatGPT) n'a d'identifiants Git configurés pour pousser directement vers `https://github.com/sircompus/smarteco-academy.git`. Le déploiement fonctionne ainsi :

1. L'assistant code/modifie les fichiers dans son propre environnement de travail
2. L'assistant fournit à Ali (le propriétaire du projet) les fichiers modifiés (téléchargement direct ou script d'installation)
3. **Ali** installe ces fichiers sur sa machine (`C:\laragon\www\SEA`)
4. **Ali** exécute lui-même `git add`, `git commit`, `git push`

Ne jamais présumer qu'un `git push` a été exécuté par l'assistant — c'est toujours Ali qui le fait.

## Architecture officielle (ne pas dévier)

- **Backend** : Laravel 11+ (PHP), architecture "modular monolith"
- **Base de données** : MySQL
- **Frontend** : Blade + Tailwind CSS + Alpine.js (pas de framework JS séparé type Vue/React pour l'app principale)
- **Traitements automatiques** : Laravel Queue + Laravel Scheduler (si besoin futur)
- **Interdits** : ne pas introduire FastAPI, PostgreSQL, Celery, ou tout autre stack concurrent sans validation explicite d'Ali

## Environnement local d'Ali

- Windows + Laragon (`C:\laragon\www\SEA`)
- MySQL local
- `php artisan serve` ou vhost Laragon sur `localhost:8000`
- Ali est peu technique en ligne de commande — toujours donner des instructions PowerShell/CMD complètes et testées, jamais supposer une connaissance implicite

## Une seule branche de travail : `develop`

Ce projet n'utilise **pas** un système de branches multiples par tâche ou par assistant. Tout le travail (Claude comme ChatGPT) se fait directement sur `develop`. `main` existe mais n'est quasiment pas utilisée — **ne jamais demander à Ali de travailler sur `main`**.

Raison : Ali gère seul toutes les opérations Git manuellement ; un système multi-branches complexe a déjà causé une confusion majeure (voir `docs/DECISIONS.md`, entrée sur l'incident `main`/`develop` du 02/08/2026).

## Avant de coder quoi que ce soit

1. Vérifier si la fonctionnalité demandée existe déjà (grep dans le code, pas seulement dans la doc)
2. Si un fichier similaire existe, l'étendre plutôt que dupliquer
3. Toujours fournir : les fichiers modifiés en téléchargement, les instructions d'installation exactes (y compris migrations si besoin), et si plusieurs fichiers → un script PowerShell d'installation (voir `docs/ARCHITECTURE.md` pour le patron utilisé)

## À la fin de chaque session de travail

Mettre à jour :
- `docs/CURRENT_STATE.md`
- `docs/HANDOFF.md`
- `docs/CHANGELOG_AI.md`
- `docs/TASKS.md` si une tâche change de statut

Ces mises à jour font partie du livrable, pas une option.

'@
$dir0 = Split-Path $path0 -Parent
if (-not (Test-Path $dir0)) { New-Item -ItemType Directory -Path $dir0 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path0, $content0, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: AGENTS.md" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: AGENTS.md -- $($_.Exception.Message)" -ForegroundColor Red
}

$path1 = "C:\laragon\www\SEA\CLAUDE.md"
$content1 = @'
# CLAUDE.md — Contexte spécifique pour Claude

Lis d'abord `AGENTS.md` (règles communes à tous les assistants), puis ce qui suit est propre à Claude.

## Qui est Ali

- Porteur du projet SmartEco Academy, en train de lancer une vraie startup
- Communique souvent en majuscules, en français, parfois de façon télégraphique — c'est son style, pas de l'agressivité
- Peu technique en ligne de commande : donner des commandes PowerShell/CMD complètes, jamais des fragments
- Préfère qu'on lui explique le "pourquoi" brièvement avant le "comment"
- A eu plusieurs incidents d'installation (fichiers non remplacés, mauvaise branche Git, encodage UTF-8 corrompu) — être particulièrement rigoureux sur la vérification post-installation

## Mode de livraison des fichiers (patron établi)

Pour 1-2 fichiers simples : `present_files` direct suffit.

Pour plusieurs fichiers ou contenu avec accents français (risque d'encodage cassé sous Windows PowerShell) : générer un script `.ps1` qui écrit chaque fichier via `[System.IO.File]::WriteAllText($path, $content, [System.Text.UTF8Encoding]::new($false))`, avec :
- Le fichier `.ps1` lui-même sauvegardé en UTF-8 **avec BOM** (`encoding='utf-8-sig'` côté Python) pour que PowerShell le lise correctement
- Création automatique des dossiers manquants (`New-Item -ItemType Directory -Force`)
- Un `try/catch` par fichier avec un vrai message ECHEC en cas de problème (ne jamais afficher "OK" de façon inconditionnelle)
- Lancement : `powershell -ExecutionPolicy Bypass -File nom-du-script.ps1`

## Erreurs déjà rencontrées à ne pas reproduire

1. **Backslash parasites dans les routes dynamiques** : lors d'un remplacement regex Python, des `\"` littéraux se sont insérés dans du Blade au lieu de `"` simples → `ParseError`. Toujours relire le fichier généré avant de le livrer.
2. **Mauvaise branche Git** : Ali s'est retrouvé sur `main` (presque vide) au lieu de `develop` (tout le travail réel), causant des erreurs "fichier introuvable" trompeuses. Si Ali rapporte qu'un fichier de base semble manquant, **vérifier la branche en premier** (`git status` doit dire `On branch develop`).
3. **Nom de table Eloquent indénombrable** : `CvEducation` → Eloquent devine `cv_education` (sans s) car "education" est traité comme indénombrable par l'inflecteur, alors que la vraie table est `cv_educations`. Toujours vérifier/forcer `protected $table` pour les modèles dont le nom se termine par un mot potentiellement indénombrable.
4. **Oubli de fournir le fichier après édition** : plusieurs fois, une correction a été faite dans l'environnement de travail de Claude mais jamais transmise à Ali avant de passer à autre chose. Toujours vérifier qu'un fichier modifié a bien été proposé en téléchargement avant de continuer.

## Où trouver le reste du contexte

- `docs/CURRENT_STATE.md` — état exact actuel
- `docs/ARCHITECTURE.md` — structure technique complète
- `docs/DATABASE_SCHEMA.md` — schéma de base de données
- `docs/DECISIONS.md` — décisions déjà tranchées avec Ali
- `docs/HANDOFF.md` — dernière session, prochaine action

'@
$dir1 = Split-Path $path1 -Parent
if (-not (Test-Path $dir1)) { New-Item -ItemType Directory -Path $dir1 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path1, $content1, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: CLAUDE.md" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: CLAUDE.md -- $($_.Exception.Message)" -ForegroundColor Red
}

$path2 = "C:\laragon\www\SEA\docs\ARCHITECTURE.md"
$content2 = @'
# ARCHITECTURE.md

## Structure générale

Laravel "modular monolith" — un seul projet Laravel, mais organisé pour que chaque grand domaine métier ait ses propres routes, contrôleurs, modèles et vues regroupés logiquement.

## Fichiers de routes (tous chargés depuis `routes/web.php`)

- `routes/auth.php` — authentification (scaffold Breeze standard)
- `routes/admin.php` — gestion des utilisateurs (admin uniquement)
- `routes/registration.php` — dossiers d'admission (admin + étudiant)
- `routes/centre.php` — LE plus gros fichier : structure académique, packs, paiements, bibliothèque, cours, rapports financiers (admin + superviseur + étudiant)
- `routes/training.php` — formations courtes (admin + superviseur + étudiant)
- `routes/professor.php` — espace professeur
- `routes/cv.php` — CV/ATS/Portfolio (étudiant + admin-en-édition-déléguée + portfolio public sans auth)

## Rôles & permissions

- Tables `roles`, `role_user` (pivot), middleware `EnsureUserHasRole` (accepte plusieurs rôles séparés par virgule : `role:admin,superviseur`)
- Middleware `EnsureModuleIsActive` — bloque l'accès à un module désactivé depuis la table `modules`

## Modèles clés par domaine

### Centre académique
`AcademicLevel` → `AcademicProgram` → `Semester` → `Subject` (module) → `Course` → `CourseSection` → `Lesson` → `LessonFile`

### Packs & paiements
`Pack` (type `semestre` ou `module`, `billing_type` `unique` ou `mensuel`) → `PackEnrollment` (status, `paused_at`/`paused_days` pour la pause de facturation) → `PackPayment` → `PackPaymentReminder`

Même patron dupliqué pour `Training` → `TrainingEnrollment` → `TrainingPayment` → `TrainingPaymentReminder`

### Bibliothèque de ressources (indépendante du système Cours)
`AcademicResource` — rattachée directement à `Subject`, classée par `type` (cours/td/examen/resume) et `professor_name` (texte libre, pas de FK vers un compte). Accès étudiant strictement conditionné à un pack de type `semestre` actif (`User::hasSemesterAccessToSubject()`), plus strict que `User::hasAccessToSubject()` (utilisée pour les leçons, qui accepte aussi un pack module seul).

### Ressources par cours (CourseResource)
Système distinct de `AcademicResource`, rattaché à `Course` (pas encore exploité en usage réel — construit en prévision d'un usage futur).

### CV / ATS / Portfolio
`CvProfile` (1-1 avec `User`) → `CvEducation`, `CvExperience`, `CvSkill` (avec `category`, alimentée par `SkillSuggestion`), `CvLanguage`, `CvCertification`, `PortfolioProject`

- `CvProfile::effective_summary` — résumé saisi par l'étudiant, ou généré automatiquement via `CvSummaryGeneratorService` si vide (jamais persisté, recalculé à l'affichage)
- `AtsScoreService` — score /100 basé sur des règles de complétude (pas d'IA externe)
- `CvProfile::show_in_navigation` + `is_public` — affiche une icône photo dans le menu étudiant menant au portfolio public
- Modèles de CV imprimables : `classique` (Times New Roman, noir, colonne unique — voir contraintes typographiques dans `DECISIONS.md`) et `moderne` (coloré, 2 colonnes)
- Admin peut éditer le CV d'un étudiant à sa place via `Admin\CvBuilderController`, qui réutilise **les mêmes vues Blade** que le contrôleur étudiant (routes et layout injectés dynamiquement via `$routePrefix`/`$targetUser`/`$layout`, pas de duplication de code)

### Profil utilisateur général (distinct du CV)
Table `profiles` (1-1 avec `User`, préexistante au projet mais sous-exploitée) : `first_name`, `last_name`, `phone`, `birth_date`, `gender`, `address`, `city`, `country`, `bio`, `avatar_path`. Gérée via la page Breeze standard `/profile`.
**Important** : `bio` (biographie générale du compte) est différent de `CvProfile::summary`/`effective_summary` (résumé professionnel du CV) — ne jamais les confondre.

## Patron d'impression/export (établi et à réutiliser)

Pas de librairie PDF serveur (dompdf, etc.) — tout export PDF passe par le bouton natif du navigateur ("Imprimer / Enregistrer en PDF") sur des vues Blade avec CSS `@media print`. Patron :
- `@push('styles')` avec `@page { size: A4|A5; margin: ...; }`
- Classe `print:hidden` sur tout élément à masquer à l'impression (bouton, sidebar, header)
- Les layouts principaux (`admin.blade.php`, `student.blade.php`) ont déjà `print:hidden` sur `<aside>`/`<header>` et `print:p-0` sur `<main>`

## Reçus & rapports financiers
`resources/views/admin/centre/pack-enrollments/receipt.blade.php` — reçu A5 portrait, logo + titre + n°/date en en-tête, pied de page avec "encaissé par"/"imprimé par". Même patron dupliqué côté Training.
`resources/views/admin/centre/reports/index.blade.php` — état financier détaillé par filière/semestre, format A4.

## Portfolio public
`routes/cv.php` → `GET /portfolio/{slug}` sans middleware auth. Vue standalone (`resources/views/portfolio/show.blade.php`, pas de `@extends` d'un layout applicatif — HTML complet autonome avec son propre `@vite`).

## Logo du site
`public/images/smarteco-logo.png` — référencé directement par chemin fixe (`asset('images/smarteco-logo.png')`) dans tous les layouts, reçus, et le portfolio public. Remplacer ce fichier (même nom) suffit à changer le logo partout.

'@
$dir2 = Split-Path $path2 -Parent
if (-not (Test-Path $dir2)) { New-Item -ItemType Directory -Path $dir2 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path2, $content2, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: docs/ARCHITECTURE.md" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: docs/ARCHITECTURE.md -- $($_.Exception.Message)" -ForegroundColor Red
}

$path3 = "C:\laragon\www\SEA\docs\CHANGELOG_AI.md"
$content3 = @'
# CHANGELOG_AI.md

Journal chronologique des changements effectués par les assistants IA (Claude, ChatGPT). Chaque entrée : date, assistant, résumé, fichiers principaux.

## 02/08/2026 — Claude
Système de coordination multi-assistants créé (AGENTS.md, CLAUDE.md, docs/*, prompts/MASTER_PROMPT.md). Aucun changement métier.

## 02/08/2026 — Claude
Nettoyage navigation : liens morts du tableau de bord admin remplacés par de vrais liens ; tableau de bord étudiant — statistiques réelles au lieu de zéros figés, cartes de modules cliquables selon disponibilité réelle.

## 01-02/08/2026 — Claude
Module CV/ATS/Portfolio construit de A à Z puis affiné sur de nombreux cycles : structure de données (CvProfile + sections), CV builder avec cases à cocher pour compétences/langues, résumé auto-généré, score ATS, modèles Classique (Times New Roman strict) et Moderne, portfolio public partageable, catalogue de compétences par catégorie géré par l'admin, édition déléguée par l'admin, photo de profil générale distincte du CV, icône de navigation vers le portfolio.

## Session antérieure — Claude
Modules Inscription/Admission, Centre (structure académique, packs, paiements avec facturation mensuelle et pause, reçus A5, relances email, état financier A4, bibliothèque de ressources académiques indépendante), rôles Professeur/Superviseur, Cours/leçons, Formations courtes.

## Note sur la traçabilité
Ce journal est tenu manuellement par chaque assistant en fin de session — il ne remplace pas `git log`, qui reste la trace technique définitive. En cas de doute sur ce qui a été réellement livré, toujours vérifier `git log` et l'état réel des fichiers plutôt que ce journal seul.

'@
$dir3 = Split-Path $path3 -Parent
if (-not (Test-Path $dir3)) { New-Item -ItemType Directory -Path $dir3 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path3, $content3, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: docs/CHANGELOG_AI.md" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: docs/CHANGELOG_AI.md -- $($_.Exception.Message)" -ForegroundColor Red
}

$path4 = "C:\laragon\www\SEA\docs\CURRENT_STATE.md"
$content4 = @'
# CURRENT_STATE.md

Dernière mise à jour : 02/08/2026, par Claude.

## Branche stable
`develop` — c'est **la seule branche de travail réelle**. `main` existe mais est quasiment vide, ne jamais y travailler.

## Branche de travail actuelle
`develop` (pas de branche séparée par tâche — voir `AGENTS.md` pour la raison).

## Fonctionnalités terminées
Voir `docs/TASKS.md` pour le détail : Inscription/Admission, Centre (packs/paiements/reçus/bibliothèque), rôles Prof/Superviseur, Cours/leçons, Formations courtes, CV/ATS/Portfolio, photo de profil, nettoyage navigation.

## Fonctionnalités en cours
Aucune au moment de la rédaction — dernière tâche livrée : nettoyage des liens morts du tableau de bord étudiant/admin.

## Fonctionnalités prévues (non commencées)
Community/Divertissement, Projets/Services académiques, Examens, synchronisation multi-PC (reportée par Ali).

## Fichiers récemment modifiés (dernière session connue)
- `resources/views/admin/dashboard.blade.php` — liens morts remplacés
- `resources/views/student/dashboard.blade.php` — statistiques réelles + cartes cliquables
- `routes/web.php` — calcul des vraies statistiques dans la route dashboard étudiant
- `resources/views/student/cv/templates/classique.blade.php` — typographie stricte (Times New Roman, 3 tailles, colonne unique)
- `resources/views/portfolio/show.blade.php` — fond du bandeau sécurisé en style inline (bug texte invisible corrigé), sections skills/langues en liste

## Tests exécutés
Aucun test automatisé (`tests/Feature/*`) n'a été exécuté durant les sessions de développement — les vérifications se sont faites manuellement par Ali via capture d'écran après chaque installation. **Point d'amélioration identifié** : faire tourner `php artisan test` avant chaque livraison importante.

## Problèmes connus / non résolus
1. **Incident Git résolu mais cause racine non confirmée** : Ali s'est retrouvé sur `main` au lieu de `develop`. Résolu, mais surveiller une récidive.
2. **Aucun test automatisé n'a jamais été exécuté sur ce projet.** À mettre en place.
3. Le module `CourseResource` (ressources par cours) est codé mais non exploité en usage réel — `AcademicResource` (bibliothèque indépendante) est le système réellement utilisé.
4. Modules "Bientôt disponible" affichés sur le tableau de bord étudiant (Examens, Projets, Community) — cartes visibles mais non cliquables, honnête sur ce qui n'existe pas encore.

## Prochaine action recommandée
Demander à Ali quelle est sa priorité : (a) commencer un nouveau module (Community, Projets, ou Examens), (b) mettre en place la synchronisation multi-PC reportée, (c) mettre en place une suite de tests automatisés, ou (d) autre chose. Ne pas décider à sa place.

'@
$dir4 = Split-Path $path4 -Parent
if (-not (Test-Path $dir4)) { New-Item -ItemType Directory -Path $dir4 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path4, $content4, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: docs/CURRENT_STATE.md" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: docs/CURRENT_STATE.md -- $($_.Exception.Message)" -ForegroundColor Red
}

$path5 = "C:\laragon\www\SEA\docs\DATABASE_SCHEMA.md"
$content5 = @'
# DATABASE_SCHEMA.md

Référence des tables par domaine. Pour le détail exact des colonnes, toujours se référer au fichier de migration correspondant dans `database/migrations/` — ce document donne la vue d'ensemble, pas l'exhaustivité.

## Fondations (scaffold + rôles)
- `users` — comptes (name, email, is_active pour validation admin des inscriptions publiques)
- `profiles` — 1-1 avec users : first_name, last_name, phone, birth_date, gender, address, city, country, bio, avatar_path
- `roles`, `permissions`, `role_user`, `permission_role` — RBAC classique
- `modules`, `module_settings` — activation/désactivation de modules entiers
- `media_files`, `notifications`, `activity_logs`, `system_settings` — utilitaires génériques

## Centre académique
- `academic_levels` → `academic_programs` → `semesters` → `subjects` (modules) — hiérarchie complète (migration `2026_07_13_145015_create_centre_module_tables.php`)
- `courses`, `course_sections`, `lessons`, `lesson_files` — structure de cours et leçons
- `course_resources` — ressources rattachées à un `course` (peu exploité en usage réel)
- `academic_resources` — LE vrai réservoir de documents utilisé, rattaché directement à `subject`, avec `type` (cours/td/examen/resume) et `professor_name` en texte libre

## Packs & paiements (Centre)
- `packs` — type (semestre/module), billing_type (unique/mensuel), prix
- `pack_enrollments` — status, amount_due, paused_at, paused_days
- `pack_payments`, `pack_payment_reminders`

## Formations courtes (Training)
- `training_sessions`, `training_sections`, `training_lessons`, `training_progress`
- `training_enrollments`, `training_payments`, `training_payment_reminders`
(même patron que les packs, dupliqué indépendamment)

## Inscription/Admission
- `registrations` — dossier d'admission (status : draft/submitted/under_review/accepted/rejected/etc.)
- `registration_documents`, `registration_status_histories`

## CV / ATS / Portfolio (migration `2026_08_01_150000_create_cv_portfolio_tables.php` + ajouts ultérieurs)
- `cv_profiles` — 1-1 avec users : full_name, headline, summary, photo_path, liens sociaux, cv_template, portfolio_template, is_public, show_in_navigation, public_slug
- `cv_educations` (⚠️ table nommée `cv_educations` mais modèle `CvEducation` — `$table` forcé explicitement, sinon Eloquent devine `cv_education` à tort, "education" étant traité comme indénombrable)
- `cv_experiences`, `cv_skills` (avec `category`, alimentée depuis `skill_suggestions`), `cv_languages`, `cv_certifications`
- `portfolio_projects` — projets affichés sur le portfolio public
- `skill_suggestions` — catalogue de compétences suggérées, gérable depuis l'admin (name, category, is_active, sort_order)

## Points d'attention Eloquent
- `CvEducation::$table = 'cv_educations'` doit rester forcé explicitement (voir ci-dessus)
- `PackEnrollment` et `TrainingEnrollment` suivent des logiques de facturation quasi-identiques mais sont des modèles totalement séparés (pas d'héritage/trait partagé) — toute correction de bug sur l'un doit être vérifiée sur l'autre

'@
$dir5 = Split-Path $path5 -Parent
if (-not (Test-Path $dir5)) { New-Item -ItemType Directory -Path $dir5 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path5, $content5, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: docs/DATABASE_SCHEMA.md" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: docs/DATABASE_SCHEMA.md -- $($_.Exception.Message)" -ForegroundColor Red
}

$path6 = "C:\laragon\www\SEA\docs\DECISIONS.md"
$content6 = @'
# DECISIONS.md — Décisions déjà tranchées (ne pas reproposer)

Format : date approximative, décision, raison.

## Architecture générale
- **Laravel + MySQL**, pas de couche Python/FastAPI séparée malgré le plan initial (dossier technique ChatGPT) — simplification décidée pour un MVP plus rapide à livrer.
- **Une seule branche `develop`**, pas de branches multiples par fonctionnalité — Ali gère seul Git manuellement, la complexité multi-branches a causé un incident de confusion majeur (voir plus bas).

## Export PDF / impression
- **Pas de librairie PDF serveur** (dompdf etc.) — export via bouton d'impression navigateur + CSS `@media print`. Choix validé après plusieurs itérations sur les reçus.
- Reçus de paiement : format **A5 portrait** (148×210mm), logo en haut à gauche, titre au centre, numéro/date à droite, pied de page avec mentions d'encaissement/impression.
- État financier (rapport) : format **A4**.

## CV / ATS / Portfolio
- Le CV, sa version ATS, et le portfolio public **partagent la même donnée sous-jacente** (`CvProfile` et relations) — aucune duplication de saisie.
- **Résumé professionnel auto-généré** si l'étudiant le laisse vide (3-10 lignes, basé sur formations/expériences/compétences), jamais persisté en base, ne remplace jamais un résumé saisi manuellement.
- **Compétences classées par catégorie**, catalogue géré depuis l'admin (`SkillSuggestion`), catégories définies par Ali : Intelligence artificielle et analyse, Systèmes d'information et cloud, Développement et outils numériques, Logiciels de gestion, Infrastructure et maintenance, Compétences professionnelles.
- **Langues en liste fixe** (menu déroulant, pas texte libre) : Arabe, Français, Anglais, Espagnol, Allemand, + option "Autre".
- **Format du CV "Classique" strictement défini par Ali** : police Times New Roman forcée en CSS pur (`!important`, indépendant de Tailwind pour éviter tout risque de classes non compilées), noir garanti, titre 20pt, sous-titres 16pt, paragraphes 12pt, interligne 1.5, **colonne unique obligatoire** (le nombre de pages n'est pas une contrainte).
- Séparateur langue/niveau : **deux-points** (`Arabe : Langue maternelle`), jamais de tiret.
- Compétences affichées **une par ligne** (pas en pastilles horizontales), notamment pour la catégorie "Compétences professionnelles" qui contient des phrases longues.
- **Accès à la bibliothèque de ressources académiques** : règle **stricte**, un pack de type `semestre` actif est **obligatoire** — un pack limité à un seul module ne suffit pas (contrairement à l'accès aux leçons de cours, plus permissif).
- Admin peut éditer le CV d'un étudiant à sa place (`Admin\CvBuilderController`), réutilisant les mêmes vues que le builder étudiant.
- Icône photo dans le menu étudiant (`CvProfile::show_in_navigation`) : fonctionnalité générique réutilisable pour n'importe quel compte, pas codée en dur pour Ali spécifiquement.

## Garde-fou refusé
- Une bannière d'avertissement admin sur la cohérence "niveau du pack vs niveau du dossier d'admission accepté" a été construite puis **explicitement retirée à la demande d'Ali** — il ne voulait aucun changement côté admin sur ce point, seulement la restriction d'accès côté étudiant (déjà satisfaite par ailleurs).

## Incident du 02/08/2026 — confusion de branche Git
Ali s'est retrouvé sur la branche `main` (quasiment vide) au lieu de `develop` (tout le travail réel), après avoir cru pousser normalement. Cause exacte non identifiée avec certitude, possiblement un `git checkout main` antérieur oublié. Résolu par `git checkout develop` (après suppression d'un fichier non suivi qui bloquait le switch). Décision : **toujours vérifier `git status` en tout début de diagnostic** si Ali rapporte qu'un fichier "de base" semble manquant.

## Modules explicitement reportés (ne pas construire sans demande explicite)
- Community / Divertissement
- Projets / Services académiques
- Examens

'@
$dir6 = Split-Path $path6 -Parent
if (-not (Test-Path $dir6)) { New-Item -ItemType Directory -Path $dir6 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path6, $content6, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: docs/DECISIONS.md" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: docs/DECISIONS.md -- $($_.Exception.Message)" -ForegroundColor Red
}

$path7 = "C:\laragon\www\SEA\docs\HANDOFF.md"
$content7 = @'
# HANDOFF.md

## Assistant ayant travaillé
Claude (Anthropic), session longue du 01-02/08/2026.

## Date
02/08/2026

## Tâche
Mise en place du système de coordination multi-assistants (TASK-000), suite à une longue session de développement couvrant les modules Inscription, Centre, Formations, CV/ATS/Portfolio, et un nettoyage de navigation.

## Branche
`develop`

## Commit
Le hash exact doit être vérifié par Ali sur sa machine via :
```
git log -1 --format="%H %s"
```
(le hash tel que vu depuis l'environnement de travail de Claude peut différer légèrement de celui réellement présent sur GitHub, car Claude ne pousse jamais directement — voir `AGENTS.md`)

## Travail effectué (cette session de mise en place de la coordination)
Création des fichiers `AGENTS.md`, `CLAUDE.md`, `docs/PROJECT_CONTEXT.md`, `docs/ARCHITECTURE.md`, `docs/DECISIONS.md`, `docs/TASKS.md`, `docs/CURRENT_STATE.md`, `docs/HANDOFF.md` (ce fichier), `docs/CHANGELOG_AI.md`, `docs/DATABASE_SCHEMA.md`, `prompts/MASTER_PROMPT.md`. Aucun fichier métier modifié.

## Fichiers modifiés
Uniquement les fichiers de documentation listés ci-dessus (tous nouveaux, aucune modification de fichier existant).

## Tests exécutés
Aucun (documentation pure, pas de changement de code).

## Travail restant
- Ali doit installer ces fichiers sur sa machine et les committer/pousser sur `develop`
- Aucune tâche métier en cours au moment de la rédaction

## Prochaine instruction exacte pour le prochain assistant
1. Lire `docs/CURRENT_STATE.md` et ce fichier en entier avant toute action
2. Demander à Ali sa priorité du jour (ne pas supposer)
3. Si Ali mentionne avoir utilisé ChatGPT entre-temps, lui demander un résumé de ce qui a été fait, et vérifier que `docs/CURRENT_STATE.md` reflète bien ces changements — sinon, le mettre à jour avant de continuer
4. À la fin de la session, mettre à jour ce fichier (`HANDOFF.md`) et `docs/CURRENT_STATE.md` avant de clore

'@
$dir7 = Split-Path $path7 -Parent
if (-not (Test-Path $dir7)) { New-Item -ItemType Directory -Path $dir7 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path7, $content7, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: docs/HANDOFF.md" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: docs/HANDOFF.md -- $($_.Exception.Message)" -ForegroundColor Red
}

$path8 = "C:\laragon\www\SEA\docs\PROJECT_CONTEXT.md"
$content8 = @'
# PROJECT_CONTEXT.md — SmartEco Academy

## Qu'est-ce que c'est

Plateforme web (startup réelle, pas un projet scolaire) de gestion académique pour un centre de formation marocain (Tétouan), couvrant : inscriptions/admissions, gestion de cursus, packs payants avec facturation, formations courtes, bibliothèque de ressources pédagogiques, et un module CV/ATS/Portfolio pour les étudiants.

## Porteur du projet

Ali Bahtit — candidat au doctorat en Management/SI/IA, formateur expérimenté (20+ ans en informatique, maintenance, enseignement). Fondateur et administrateur principal de la plateforme.

## Objectif métier

- Digitaliser la gestion d'un centre de formation (niveaux DEUG/Licence, filières Économie/Gestion et spécialisations)
- Monétiser via des "packs" (semestre complet ou module isolé) avec facturation mensuelle ou unique
- Offrir aux étudiants un outil de CV/portfolio professionnel généré à partir de leurs données académiques

## Stack technique

- Laravel 11+ (PHP 8.3), MySQL, Blade, Tailwind CSS, Alpine.js
- Environnement local : Laragon sur Windows (`C:\laragon\www\SEA`)
- Dépôt : https://github.com/sircompus/smarteco-academy.git
- Branche de travail unique : `develop`

## Rôles utilisateurs

| Rôle | Accès |
|---|---|
| `admin` | Tout, y compris édition à la place des étudiants (CV, etc.) |
| `superviseur` | Inscriptions, paiements, dossiers d'admission, CV & Portfolios (lecture + édition partagée avec admin) |
| `professeur` | Ses propres cours uniquement |
| `etudiant` / `stagiaire` | Espace étudiant standard |

## Modules fonctionnels (voir `docs/CURRENT_STATE.md` pour le détail de ce qui est terminé)

1. **Inscription/Admission** — dossiers d'admission, validation par l'admin
2. **Centre** — structure académique (niveaux → filières → semestres → modules), packs payants, paiements, reçus, relances, bibliothèque de ressources, cours/leçons
3. **Formations courtes (Training)** — formations indépendantes du cursus semestriel
4. **CV / ATS / Portfolio** — CV builder, score de compatibilité ATS, portfolio public partageable
5. Modules prévus mais non commencés : Community/Divertissement, Projets/Services académiques, Examens

## Contraintes de collaboration IA

Voir `AGENTS.md` à la racine — règles obligatoires pour tout assistant (Claude ou ChatGPT) travaillant sur ce dépôt.

'@
$dir8 = Split-Path $path8 -Parent
if (-not (Test-Path $dir8)) { New-Item -ItemType Directory -Path $dir8 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path8, $content8, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: docs/PROJECT_CONTEXT.md" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: docs/PROJECT_CONTEXT.md -- $($_.Exception.Message)" -ForegroundColor Red
}

$path9 = "C:\laragon\www\SEA\docs\TASKS.md"
$content9 = @'
# TASKS.md

Format par tâche : identifiant, statut (TERMINÉ / EN COURS / PRÉVU), objectif, fichiers concernés.

## TASK-000 — Système de coordination multi-assistants
Statut : TERMINÉ
Responsable : Claude
Objectif : Créer AGENTS.md, CLAUDE.md, docs/*, prompts/MASTER_PROMPT.md pour permettre à Claude et ChatGPT de se relayer sans perdre le contexte.
Fichiers autorisés : AGENTS.md, CLAUDE.md, docs/*, prompts/*
Critères de validation : aucun fichier métier modifié ; contenu reflète l'état réel du dépôt (pas d'invention) ; Ali peut installer et pousser sans conflit.

## TASK-001 — Module Inscription/Admission
Statut : TERMINÉ
Objectif : Dossiers d'admission étudiant + validation admin.
Fichiers : `routes/registration.php`, `app/Http/Controllers/{Admin,Student}/RegistrationController.php`, `resources/views/{admin,student}/registrations/*`

## TASK-002 — Module Centre (structure académique + packs + paiements)
Statut : TERMINÉ
Objectif : Niveaux/filières/semestres/modules, packs payants (semestre ou module, facturation unique ou mensuelle avec pause), paiements, relances email, reçus imprimables A5, état financier A4.
Fichiers : `routes/centre.php`, `app/Http/Controllers/Admin/{Centre,Curriculum,Pack,PackEnrollment,PaymentReport}Controller.php`, `app/Models/{AcademicLevel,AcademicProgram,Semester,Subject,Pack,PackEnrollment,PackPayment,PackPaymentReminder}.php`

## TASK-003 — Bibliothèque de ressources académiques
Statut : TERMINÉ
Objectif : Réservoir de documents (Cours/TD/Examens/Résumés) classé par Niveau→Filière→Semestre→Module, indépendant du système Cours, prof en texte libre, accès étudiant strict (pack semestre obligatoire).
Fichiers : `app/Models/AcademicResource.php`, `app/Http/Controllers/Admin/AcademicResourceController.php`, `app/Http/Controllers/Student/LibraryController.php`, `resources/views/{admin,student}/.../library/*`

## TASK-004 — Rôles Professeur & Superviseur
Statut : TERMINÉ (mais fragile — voir HANDOFF.md)
Objectif : Interfaces dédiées par rôle, permissions différenciées.
Fichiers : `routes/professor.php`, `resources/views/layouts/{professor,supervisor}.blade.php`
⚠️ Le fichier `layouts/professor.blade.php` a été découvert manquant en base (jamais committé) lors d'une session ultérieure et recréé — vérifier qu'il est bien présent avant de construire dessus.

## TASK-005 — Cours & leçons (upload/téléchargement)
Statut : TERMINÉ mais non exploité en usage réel
Objectif : Upload de contenu de cours par le professeur propriétaire, téléchargement étudiant selon accès.
Fichiers : `app/Models/{Course,CourseSection,Lesson,CourseResource}.php`, `app/Http/Controllers/Professor/CourseController.php`

## TASK-006 — Module Formations courtes (Training)
Statut : TERMINÉ (paiements ajoutés sur une base déjà codée)
Fichiers : `routes/training.php`, `app/Models/Training*.php`

## TASK-007 — Module CV / ATS / Portfolio
Statut : TERMINÉ (première version complète, itérée en profondeur)
Objectif : CV builder, score ATS, modèles imprimables (Classique Times New Roman / Moderne coloré), portfolio public partageable, édition déléguée par l'admin, catalogue de compétences par catégorie.
Fichiers : `routes/cv.php`, `app/Models/Cv*.php`, `app/Models/PortfolioProject.php`, `app/Models/SkillSuggestion.php`, `app/Services/{AtsScoreService,CvSummaryGeneratorService}.php`, `app/Http/Controllers/{Student,Admin}/Cv*.php`, `resources/views/{student/cv,admin/cv,portfolio}/*`

## TASK-008 — Photo de profil générale (compte)
Statut : TERMINÉ
Objectif : Upload d'avatar sur `/profile` (distinct du CV), affiché dans les 4 menus latéraux.
Fichiers : `app/Http/Controllers/ProfileController.php`, `resources/views/profile/partials/update-avatar-form.blade.php`

## TASK-009 — Nettoyage navigation (liens morts)
Statut : TERMINÉ
Objectif : Tableau de bord admin et étudiant avaient des liens `href="#"` ou des statistiques figées en dur — corrigés.
Fichiers : `resources/views/admin/dashboard.blade.php`, `resources/views/student/dashboard.blade.php`, `routes/web.php`

## TASK-010 — CV réel d'Ali Bahtit (données de démonstration/réelles)
Statut : TERMINÉ
Objectif : Seeder remplissant le profil CV réel d'Ali à partir de son CV docx.
Fichiers : `database/seeders/AliBahtitCvSeeder.php`

## TASK-011 — Module Community / Divertissement
Statut : PRÉVU (non commencé)

## TASK-012 — Module Projets / Services académiques
Statut : PRÉVU (non commencé)

## TASK-013 — Module Examens
Statut : PRÉVU (non commencé)

## TASK-014 — Synchronisation multi-PC (2 postes de travail)
Statut : REPORTÉ à la demande d'Ali ("on garde ça pour plus tard")
Contexte : Ali veut travailler depuis 2 PC sur le même réseau Wifi, partageant la même base MySQL. Plan déjà donné (bind-address MySQL, utilisateur réseau, .env pointant vers l'IP du PC serveur) mais non mis en œuvre.

'@
$dir9 = Split-Path $path9 -Parent
if (-not (Test-Path $dir9)) { New-Item -ItemType Directory -Path $dir9 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path9, $content9, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: docs/TASKS.md" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: docs/TASKS.md -- $($_.Exception.Message)" -ForegroundColor Red
}

$path10 = "C:\laragon\www\SEA\prompts\MASTER_PROMPT.md"
$content10 = @'
# MASTER_PROMPT.md

Copie-colle ce texte tel quel au tout début de toute nouvelle session (Claude ou ChatGPT) pour reprendre le projet SmartEco Academy.

---

Tu reprends le développement du projet **SmartEco Academy**.

Dépôt GitHub (source unique de vérité) :
https://github.com/sircompus/smarteco-academy.git
Branche de travail : `develop` (ne jamais utiliser `main`)

**Contrainte technique importante** : tu n'as pas d'accès direct pour pousser sur ce dépôt. Le porteur du projet (Ali) installe lui-même chaque fichier que tu lui fournis, puis fait `git commit` / `git push` de son côté. Ne prétends jamais avoir poussé quelque chose toi-même.

Avant toute action, lis dans cet ordre :
1. `AGENTS.md` — règles obligatoires pour tout assistant IA sur ce projet
2. `CLAUDE.md` (si tu es Claude) — contexte et erreurs déjà rencontrées à éviter
3. `docs/PROJECT_CONTEXT.md` — ce qu'est le projet
4. `docs/ARCHITECTURE.md` — comment il est structuré techniquement
5. `docs/DATABASE_SCHEMA.md` — schéma de base de données
6. `docs/DECISIONS.md` — décisions déjà tranchées, ne pas les reproposer
7. `docs/CURRENT_STATE.md` — état exact actuel
8. `docs/HANDOFF.md` — ce que le dernier assistant a fait, prochaine action recommandée
9. `docs/TASKS.md` — liste des tâches et leurs statuts

Une fois ces fichiers lus, indique-moi clairement, avant de coder quoi que ce soit :
1. La tâche active recommandée (d'après `HANDOFF.md`)
2. Son statut
3. Ce qui a déjà été fait
4. Ce qu'il reste à faire
5. Ta première question de clarification si besoin, ou ta proposition de première action

Ne modifie aucun fichier avant cette étape de vérification. N'invente jamais qu'une fonctionnalité existe ou est terminée si ce n'est pas confirmé dans `docs/CURRENT_STATE.md` ou dans le code lui-même.

---

## Note pour Ali : comment utiliser ce fichier

- Colle le bloc ci-dessus (entre les `---`) en tout début de conversation avec Claude ou ChatGPT
- Si tu as travaillé avec l'autre assistant entre-temps, dis-le explicitement dans ton premier message ("j'ai avancé avec ChatGPT depuis la dernière fois, voici ce qu'on a fait : ...") pour que l'assistant actuel mette à jour son contexte
- En fin de session, demande à l'assistant de mettre à jour `docs/CURRENT_STATE.md`, `docs/HANDOFF.md` et `docs/CHANGELOG_AI.md` avant de terminer, puis installe/committe/pousse ces fichiers comme n'importe quel autre livrable

'@
$dir10 = Split-Path $path10 -Parent
if (-not (Test-Path $dir10)) { New-Item -ItemType Directory -Path $dir10 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path10, $content10, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: prompts/MASTER_PROMPT.md" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: prompts/MASTER_PROMPT.md -- $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "Termine. Verifie qu il n y a AUCUNE ligne ECHEC rouge ci-dessus." -ForegroundColor Cyan
