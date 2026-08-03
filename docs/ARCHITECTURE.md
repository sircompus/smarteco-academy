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
