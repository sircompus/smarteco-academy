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



## 2026-08-02 — Fondation du module de veille d’emploi

Assistant : ChatGPT

Branche : `feature/job-watch-foundation`

Ajouts :

- six migrations MySQL ;
- six modèles Eloquent ;
- six factories ;
- `JobWatchPolicy` ;
- relations avec `User` et `CvProfile` ;
- tests des relations, contraintes et autorisations.

Tests :

- `JobWatchFoundationTest` : 7 réussis, 21 assertions.


## 2026-08-02 — Fondations de la veille d’emploi

Branche : `feature/job-watch-foundation`

Ajouts :

- migrations `job_watches`, `job_watch_keywords`, `job_sources`,
  `job_offers`, `job_offer_skills` et `job_matches` ;
- modèles et relations Eloquent ;
- factories ;
- `JobWatchPolicy` ;
- tests des relations, contraintes et autorisations.

Tests :

- MySQL `laravel_testing` ;
- 7 tests réussis ;
- 21 assertions.