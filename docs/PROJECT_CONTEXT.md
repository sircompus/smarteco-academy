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
