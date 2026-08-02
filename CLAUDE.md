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
