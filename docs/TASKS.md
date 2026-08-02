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
