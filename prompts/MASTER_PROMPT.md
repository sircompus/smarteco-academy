# Prompt maître SmartEco Academy

Tu travailles sur le projet **SmartEco Academy** :

https://github.com/sircompus/smarteco-academy

Avant toute modification, lis obligatoirement :

1. `README.md`
2. `AGENTS.md`
3. `CLAUDE.md`
4. `docs/PROJECT_CONTEXT.md`
5. `docs/ARCHITECTURE.md`
6. `docs/DECISIONS.md`
7. `docs/TASKS.md`
8. `docs/CURRENT_STATE.md`
9. `docs/HANDOFF.md`
10. `docs/DATABASE_SCHEMA.md`

## Règles obligatoires

- GitHub est la source unique de vérité.
- Ne te base pas sur la mémoire d’une ancienne conversation.
- Utilise Laravel et MySQL.
- Ne pas introduire FastAPI, PostgreSQL ou Celery.
- Utiliser Eloquent, Laravel Queue et Laravel Scheduler.
- Ne jamais modifier directement la branche `main`.
- Travailler uniquement sur une branche dédiée issue de `develop`.
- Ne modifier que les fichiers autorisés dans `docs/TASKS.md`.
- Ne pas supprimer une fonctionnalité existante.
- Ne pas changer l’architecture sans mettre à jour `docs/DECISIONS.md`.
- Ajouter ou adapter les tests.
- Ne jamais mettre de secrets dans GitHub.
- Ne jamais modifier `.env` dans un commit.
- Vérifier l’état Git avant et après chaque tâche.
- Mettre à jour `docs/CURRENT_STATE.md` et `docs/HANDOFF.md` avant de terminer.
- Ajouter les changements réalisés par une IA dans `docs/CHANGELOG_AI.md`.
- Ne jamais déclarer une tâche terminée sans avoir exécuté les vérifications nécessaires.

## Procédure de démarrage obligatoire

Avant de commencer le travail, exécuter :

```bash
git fetch origin
git switch develop
git pull --ff-only origin develop
git status
```

Ensuite :

1. Lire tous les fichiers de référence.
2. Identifier la tâche active dans `docs/TASKS.md`.
3. Vérifier les fichiers autorisés.
4. Créer une branche dédiée.
5. Réaliser uniquement les modifications demandées.
6. Tester les changements.
7. Mettre à jour la documentation de suivi.
8. Présenter le rapport final obligatoire.

## Création d’une branche de travail

Utiliser un nom explicite, par exemple :

```bash
git switch -c feature/nom-de-la-fonctionnalite
```

ou :

```bash
git switch -c fix/nom-du-correctif
```

Ne jamais travailler directement sur `main`.

## Format obligatoire du rapport final

```text
TÂCHE :
BRANCHE :
FICHIERS CONSULTÉS :
FICHIERS MODIFIÉS :
MODIFICATIONS :
TESTS :
RÉSULTATS DES TESTS :
RISQUES :
TRAVAIL RESTANT :
PROCHAINE ACTION :
COMMIT RECOMMANDÉ :
```

## Transmission vers une nouvelle conversation

Lorsqu’un compte ou une conversation devient indisponible :

1. Vérifier que les modifications utiles sont enregistrées dans Git.
2. Mettre à jour `docs/CURRENT_STATE.md`.
3. Mettre à jour `docs/HANDOFF.md`.
4. Mettre à jour `docs/TASKS.md`.
5. Copier le contenu de ce fichier dans la nouvelle conversation.
6. Demander au nouvel assistant de lire les fichiers du dépôt avant toute modification.

La nouvelle conversation ne doit jamais continuer uniquement à partir d’un résumé de mémoire. Elle doit reprendre depuis l’état réel du dépôt GitHub et la documentation présente dans le projet.
