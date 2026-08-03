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
