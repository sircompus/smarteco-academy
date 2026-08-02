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
