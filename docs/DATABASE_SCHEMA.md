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



## Module de veille d’emploi

### job_watches

Veilles créées par les utilisateurs.

Relations :

- `user_id` → `users.id`
- `cv_profile_id` → `cv_profiles.id`, nullable

### job_watch_keywords

Mots-clés associés à une veille.

Relation :

- `job_watch_id` → `job_watches.id`

### job_sources

Sources autorisées pour les offres.

Les secrets et clés API ne sont jamais enregistrés dans cette table.

### job_offers

Offres récupérées et normalisées.

Relation :

- `job_source_id` → `job_sources.id`

Contraintes :

- `fingerprint` unique ;
- couple `job_source_id` + `external_id` unique.

### job_offer_skills

Compétences demandées dans une offre.

Relation :

- `job_offer_id` → `job_offers.id`

### job_matches

Correspondances entre une veille et une offre.

Relations :

- `job_watch_id` → `job_watches.id`
- `job_offer_id` → `job_offers.id`

Contrainte :

- couple `job_watch_id` + `job_offer_id` unique.

## Module de veille d’emploi

### `job_watches`

Veilles créées par les utilisateurs.

- `user_id` référence `users.id`.
- `cv_profile_id` référence éventuellement `cv_profiles.id`.
- `source_mode` accepte `cv`, `portfolio` ou `both`.
- Le portfolio est actuellement récupéré via les projets rattachés aux profils CV.

### `job_watch_keywords`

Mots-clés associés aux veilles.

- `job_watch_id` référence `job_watches.id`.
- Le couple veille, mot-clé normalisé et type est unique.

### `job_sources`

Sources autorisées pour l’importation des offres.

- Les clés API ne sont pas enregistrées dans cette table.
- Les secrets restent dans les variables d’environnement.

### `job_offers`

Offres récupérées et normalisées.

- `job_source_id` référence `job_sources.id`.
- `fingerprint` est unique.
- Le couple `job_source_id` et `external_id` est unique.

### `job_offer_skills`

Compétences demandées par les offres.

- `job_offer_id` référence `job_offers.id`.
- Une compétence normalisée ne peut apparaître qu’une fois par offre.

### `job_matches`

Correspondances entre les veilles et les offres.

- `job_watch_id` référence `job_watches.id`.
- `job_offer_id` référence `job_offers.id`.
- Le couple veille et offre est unique.