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

## TASK-015 — Fondations du système de veille d’emploi

Statut : EN COURS

Responsable : Ali / ChatGPT

Priorité : P1

Branche : `feature/job-watch-foundation`

Objectif : Créer les fondations MySQL et Eloquent du système de
recommandation d’offres d’emploi selon le CV, les compétences,
les expériences et le portfolio de l’utilisateur.

Contexte : Le module CV / ATS / Portfolio existe déjà dans TASK-007.
Cette tâche doit réutiliser les modèles et les données existants sans
recréer les tables CV, portfolio, utilisateurs ou notifications.

Fichiers autorisés :

- `app/Models/JobWatch.php`
- `app/Models/JobWatchKeyword.php`
- `app/Models/JobSource.php`
- `app/Models/JobOffer.php`
- `app/Models/JobOfferSkill.php`
- `app/Models/JobMatch.php`
- `app/Policies/JobWatchPolicy.php`
- `app/Models/User.php`
- `app/Models/CvProfile.php`
- `database/migrations/*job_watch*`
- `database/migrations/*job_source*`
- `database/migrations/*job_offer*`
- `database/migrations/*job_match*`
- `database/factories/*`
- `database/seeders/*Job*`
- `tests/Feature/JobWatch/*`
- `tests/Unit/JobWatch/*`
- `docs/TASKS.md`
- `docs/DATABASE_SCHEMA.md`
- `docs/CURRENT_STATE.md`
- `docs/HANDOFF.md`
- `docs/CHANGELOG_AI.md`
- `docs/DECISIONS.md`

Tables à créer dans cette première phase :

- `job_watches`
- `job_watch_keywords`
- `job_sources`
- `job_offers`
- `job_offer_skills`
- `job_matches`

Relations prévues :

- Un utilisateur possède plusieurs veilles.
- Une veille appartient à un utilisateur.
- Une veille peut utiliser un profil CV.
- Une veille peut utiliser un portfolio.
- Une veille possède plusieurs mots-clés.
- Une veille possède plusieurs correspondances.
- Une source possède plusieurs offres.
- Une offre possède plusieurs compétences.
- Une correspondance appartient à une veille et à une offre.

Sous-tâches :

- [ ] Vérifier les modèles CV et portfolio existants.
- [ ] Vérifier les migrations existantes.
- [ ] Vérifier la table Laravel `notifications`.
- [ ] Définir précisément les colonnes des nouvelles tables.
- [ ] Mettre à jour `DATABASE_SCHEMA.md`.
- [ ] Mettre à jour `DECISIONS.md`.
- [ ] Créer les migrations MySQL.
- [ ] Créer les modèles Eloquent.
- [ ] Ajouter les relations Eloquent.
- [ ] Ajouter les clés étrangères et les index.
- [ ] Ajouter les contraintes contre les doublons.
- [ ] Créer `JobWatchPolicy`.
- [ ] Ajouter les factories et données fictives.
- [ ] Tester les migrations et les rollbacks.
- [ ] Tester les relations Eloquent.
- [ ] Tester que chaque utilisateur accède uniquement à ses veilles.
- [ ] Mettre à jour `CURRENT_STATE.md`.
- [ ] Mettre à jour `HANDOFF.md`.
- [ ] Mettre à jour `CHANGELOG_AI.md`.

Hors périmètre de cette première phase :

- API externe d’offres d’emploi ;
- scraping ;
- Laravel Scheduler ;
- Laravel Queue ;
- notifications par email ;
- interface utilisateur ;
- moteur d’intelligence artificielle ;
- scoring avancé ;
- récupération automatique des offres.

Tests obligatoires :

- `php artisan migrate:status`
- `php artisan migrate`
- `php artisan migrate:rollback`
- `php artisan test --filter=JobWatch`
- `vendor\bin\pint --test`
- `npm.cmd run build`

État initial des tests avant cette tâche :

- 24 tests réussis ;
- 1 test échoué ;
- échec préexistant dans `RegistrationTest` ;
- cet échec ne provient pas du module de veille.

Critères de validation :

- Les six nouvelles tables sont créées dans MySQL.
- Les migrations et rollbacks fonctionnent.
- Aucune table existante n’est recréée.
- Les relations Eloquent sont testées.
- Les doublons d’offres sont bloqués par MySQL.
- Un utilisateur ne peut consulter que ses propres veilles.
- Aucun secret ou fichier `.env` n’est ajouté à Git.
- La documentation est mise à jour.

Prochaine action exacte : Inventorier les modèles et migrations CV,
portfolio, notifications et jobs avant de créer les nouvelles migrations.