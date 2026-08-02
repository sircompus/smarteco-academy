$path0 = "C:\laragon\www\SEA\AGENTS.md"
$content0 = @'
# AGENTS.md — Instructions pour tout assistant IA (Claude, ChatGPT, etc.)

## ⚠️ OBLIGATION DE CLÔTURE DE SESSION (non négociable)

**Dès qu'un fichier métier (code, vue, migration, route) a été modifié pendant la session**, l'assistant DOIT, avant de considérer la session terminée — même si Ali ne le demande pas explicitement :

1. Mettre à jour `docs/CURRENT_STATE.md` (nouvel état, nouveaux fichiers modifiés, nouveaux problèmes connus)
2. Mettre à jour `docs/HANDOFF.md` (remplacer entièrement son contenu par la passation de LA session en cours)
3. Ajouter une entrée datée dans `docs/CHANGELOG_AI.md`
4. Si une tâche a changé de statut, mettre à jour `docs/TASKS.md`
5. Fournir ces fichiers mis à jour à Ali **dans le même lot** que les fichiers métier livrés (même script d'installation, même message) — jamais dans un message séparé qu'Ali pourrait ignorer

**Ne jamais attendre qu'Ali demande "mets à jour la doc"** — c'est une étape automatique de fin de tâche, au même titre que fournir le fichier lui-même. Si la session ne modifie **aucun** fichier métier (question, discussion, lecture seule), cette obligation ne s'applique pas.

**Auto-vérification avant de clore toute réponse contenant un livrable de code** : "Ai-je mis à jour CURRENT_STATE.md et HANDOFF.md dans ce même message ?" Si non, le faire avant de répondre, pas après.

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

## Rappel critique (déjà dans AGENTS.md, répété ici car Claude a tendance à l'oublier en fin de longue session)

Toute réponse qui livre un fichier métier modifié DOIT inclure, dans le **même** message, la mise à jour de `docs/CURRENT_STATE.md` et `docs/HANDOFF.md`. Ne pas attendre la fin de la conversation ni une demande explicite d'Ali. Voir la section "OBLIGATION DE CLÔTURE DE SESSION" dans `AGENTS.md` pour le détail complet.

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
try {
    [System.IO.File]::WriteAllText($path1, $content1, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: CLAUDE.md" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: CLAUDE.md -- $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "Termine." -ForegroundColor Cyan
