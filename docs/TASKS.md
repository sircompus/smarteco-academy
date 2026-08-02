# TASKS — Tableau de travail SmartEco Academy

> Ce fichier est le tableau de travail partagé entre les développeurs, ChatGPT et Claude.
> Toute tâche doit être enregistrée ici avant de modifier le code.

## 1. Règles d’utilisation

1. Lire ce fichier avant de commencer une nouvelle tâche.
2. Ne travailler que sur une tâche portant le statut `PRÊTE` ou `EN COURS`.
3. Une seule personne ou un seul assistant IA doit être responsable d’une tâche à la fois.
4. Indiquer précisément les fichiers autorisés avant toute modification.
5. Ne pas modifier un fichier qui ne figure pas dans la liste des fichiers autorisés.
6. Ne jamais travailler directement sur la branche `main`.
7. Créer une branche dédiée à partir de `develop`.
8. Ajouter ou adapter les tests nécessaires.
9. Mettre à jour cette tâche après chaque session.
10. Mettre à jour `CURRENT_STATE.md`, `HANDOFF.md` et `CHANGELOG_AI.md` avant de terminer.
11. Une tâche ne passe à `TERMINÉE` qu’après validation des tests et vérification du résultat.
12. Une tâche bloquée doit contenir la cause du blocage et la prochaine action concrète.

---

## 2. Statuts autorisés

| Statut | Signification |
|---|---|
| `À ÉTUDIER` | Besoin identifié, mais pas encore analysé |
| `PRÊTE` | Tâche suffisamment définie pour être commencée |
| `EN COURS` | Travail actuellement en cours |
| `BLOQUÉE` | Impossible d’avancer sans correction, information ou décision |
| `À TESTER` | Développement terminé, vérifications encore nécessaires |
| `À VALIDER` | Tests réussis, validation finale requise |
| `TERMINÉE` | Travail testé, validé, documenté et intégré |
| `ANNULÉE` | Tâche abandonnée avec justification |

---

## 3. Priorités autorisées

| Priorité | Signification |
|---|---|
| `P0` | Urgence critique : sécurité, perte de données ou application indisponible |
| `P1` | Priorité élevée : fonctionnalité essentielle ou blocage majeur |
| `P2` | Priorité normale : développement planifié |
| `P3` | Amélioration non urgente |
| `P4` | Idée ou évolution future |

---

## 4. Tableau synthétique

| ID | Tâche | Priorité | Statut | Responsable | Branche | Dépendance |
|---|---|---:|---|---|---|---|
| DOC-001 | Mettre en place la documentation de coordination IA | P1 | EN COURS | À compléter | `docs/coordination-ia` | Aucune |
| DOC-002 | Compléter le contexte réel du projet | P1 | PRÊTE | Non attribué | À créer | DOC-001 |
| DOC-003 | Documenter l’architecture existante | P1 | À ÉTUDIER | Non attribué | À créer | DOC-002 |
| DB-001 | Documenter le schéma MySQL actuel | P1 | À ÉTUDIER | Non attribué | À créer | DOC-002 |
| APP-001 | À compléter avec la prochaine tâche fonctionnelle | P2 | À ÉTUDIER | Non attribué | À créer | À compléter |

> Ce tableau sert uniquement de résumé. Les détails complets de chaque tâche se trouvent dans les sections suivantes.

---

# 5. Tâches actives

## DOC-001 — Mettre en place la documentation de coordination IA

### Informations générales

- **Priorité :** P1
- **Statut :** EN COURS
- **Responsable :** À compléter
- **Branche :** `docs/coordination-ia`
- **Date de création :** 2026-08-02
- **Dernière mise à jour :** 2026-08-02
- **Dépendances :** Aucune

### Objectif

Créer la structure documentaire permettant à plusieurs comptes ChatGPT, à Claude et aux développeurs de travailler sur le même dépôt sans divergence.

### Résultat attendu

Les fichiers suivants existent, contiennent des informations cohérentes et sont disponibles dans la branche `develop` :

```text
AGENTS.md
CLAUDE.md
docs/PROJECT_CONTEXT.md
docs/ARCHITECTURE.md
docs/DECISIONS.md
docs/TASKS.md
docs/CURRENT_STATE.md
docs/HANDOFF.md
docs/CHANGELOG_AI.md
docs/DATABASE_SCHEMA.md
prompts/MASTER_PROMPT.md
```

### Fichiers autorisés

```text
AGENTS.md
CLAUDE.md
docs/PROJECT_CONTEXT.md
docs/ARCHITECTURE.md
docs/DECISIONS.md
docs/TASKS.md
docs/CURRENT_STATE.md
docs/HANDOFF.md
docs/CHANGELOG_AI.md
docs/DATABASE_SCHEMA.md
prompts/MASTER_PROMPT.md
README.md
```

### Fichiers interdits

Tous les fichiers applicatifs, les migrations, les contrôleurs, les modèles, les vues, les routes et les fichiers de configuration qui ne figurent pas dans la liste précédente.

### Sous-tâches

- [ ] Créer `AGENTS.md`.
- [ ] Créer `CLAUDE.md`.
- [ ] Créer `docs/PROJECT_CONTEXT.md`.
- [ ] Créer `docs/ARCHITECTURE.md`.
- [ ] Créer `docs/DECISIONS.md`.
- [x] Créer `docs/TASKS.md`.
- [ ] Créer `docs/CURRENT_STATE.md`.
- [x] Créer `docs/HANDOFF.md`.
- [ ] Créer `docs/CHANGELOG_AI.md`.
- [ ] Créer `docs/DATABASE_SCHEMA.md`.
- [x] Créer `prompts/MASTER_PROMPT.md`.
- [ ] Vérifier la cohérence entre tous les documents.
- [ ] Ajouter une section dans `README.md` expliquant la procédure de reprise.
- [ ] Envoyer les fichiers vers `origin/develop`.

### Tests et vérifications

```powershell
git status
git branch --show-current
git ls-tree -r --name-only HEAD
git diff --check
```

### Critères d’acceptation

- [ ] Les 11 fichiers demandés sont présents.
- [ ] Les chemins et les noms respectent exactement la structure prévue.
- [ ] Aucun fichier applicatif n’a été modifié.
- [ ] Aucun secret n’est présent dans les fichiers.
- [ ] Les documents ne se contredisent pas.
- [ ] `CURRENT_STATE.md` reflète l’état réel du dépôt.
- [ ] `HANDOFF.md` indique la prochaine action exacte.
- [ ] Le commit est disponible sur `origin/develop`.

### Notes de progression

- `HANDOFF.md` a été créé.
- `MASTER_PROMPT.md` a été créé.
- `TASKS.md` a été créé.
- Les autres documents doivent encore être rédigés et adaptés à l’état réel du projet.

### Blocages

Aucun blocage signalé actuellement.

### Prochaine action exacte

Créer et compléter `docs/PROJECT_CONTEXT.md` à partir de l’état réel du dépôt.

---

## DOC-002 — Compléter le contexte réel du projet

### Informations générales

- **Priorité :** P1
- **Statut :** PRÊTE
- **Responsable :** Non attribué
- **Branche recommandée :** `docs/project-context`
- **Dépendance :** DOC-001

### Objectif

Décrire clairement le but de SmartEco Academy, ses utilisateurs, ses fonctionnalités, son environnement technique et ses contraintes.

### Fichiers autorisés

```text
README.md
composer.json
package.json
routes/
app/
resources/
config/
database/
docs/PROJECT_CONTEXT.md
docs/CURRENT_STATE.md
docs/HANDOFF.md
docs/CHANGELOG_AI.md
docs/TASKS.md
```

> Les fichiers applicatifs peuvent être consultés, mais seuls les fichiers de documentation peuvent être modifiés dans cette tâche.

### Sous-tâches

- [ ] Lire le `README.md`.
- [ ] Identifier la version de Laravel.
- [ ] Identifier les principaux modules fonctionnels.
- [ ] Identifier les rôles utilisateurs.
- [ ] Identifier les dépendances importantes.
- [ ] Identifier les services externes.
- [ ] Identifier les fonctionnalités terminées et incomplètes.
- [ ] Rédiger `docs/PROJECT_CONTEXT.md`.
- [ ] Mettre à jour `docs/CURRENT_STATE.md`.
- [ ] Mettre à jour `docs/HANDOFF.md`.
- [ ] Mettre à jour `docs/CHANGELOG_AI.md`.

### Critères d’acceptation

- [ ] Le contenu repose sur le code réel du dépôt.
- [ ] Aucune fonctionnalité inexistante n’est présentée comme terminée.
- [ ] Les hypothèses sont clairement signalées.
- [ ] Les informations techniques sont vérifiables dans le dépôt.

---

# 6. Modèle pour créer une nouvelle tâche

Copier ce bloc à la fin de la section **Tâches actives** :

```markdown
## [ID] — [Titre précis de la tâche]

### Informations générales

- **Priorité :** P0 / P1 / P2 / P3 / P4
- **Statut :** À ÉTUDIER / PRÊTE / EN COURS / BLOQUÉE / À TESTER / À VALIDER
- **Responsable :** Nom, ChatGPT, Claude ou développeur
- **Branche :** `feature/...`, `fix/...`, `docs/...`
- **Date de création :** AAAA-MM-JJ
- **Dernière mise à jour :** AAAA-MM-JJ
- **Dépendances :** Aucune ou identifiants des tâches

### Contexte

Décrire pourquoi cette tâche existe.

### Objectif

Décrire le résultat attendu en une ou deux phrases.

### Hors périmètre

- Élément qui ne doit pas être traité.
- Fonctionnalité qui ne doit pas être modifiée.

### Fichiers autorisés

```text
chemin/du/fichier
chemin/du/dossier/
```

### Fichiers interdits

```text
chemin/du/fichier
```

### Sous-tâches

- [ ] Première action.
- [ ] Deuxième action.
- [ ] Ajouter ou adapter les tests.
- [ ] Mettre à jour la documentation.
- [ ] Mettre à jour `CURRENT_STATE.md`.
- [ ] Mettre à jour `HANDOFF.md`.
- [ ] Mettre à jour `CHANGELOG_AI.md`.

### Tests obligatoires

```bash
commande de test
```

### Critères d’acceptation

- [ ] Critère vérifiable 1.
- [ ] Critère vérifiable 2.
- [ ] Aucun test existant n’est cassé.
- [ ] Aucun secret n’est ajouté au dépôt.
- [ ] La documentation est mise à jour.

### Risques

- Risque identifié.
- Mesure de protection.

### Notes de progression

- AAAA-MM-JJ : travail réalisé.

### Blocages

Aucun, ou description précise du blocage.

### Prochaine action exacte

Décrire une seule action concrète permettant de reprendre immédiatement.
```

---

# 7. Procédure de prise en charge d’une tâche

## Avant de commencer

1. Mettre à jour le dépôt :

```powershell
git fetch origin
git switch develop
git pull --ff-only origin develop
git status
```

2. Choisir une tâche ayant le statut `PRÊTE`.
3. Ajouter le responsable.
4. Passer le statut à `EN COURS`.
5. Créer une branche dédiée :

```powershell
git switch -c feature/nom-de-la-tache
```

Pour une correction :

```powershell
git switch -c fix/nom-du-correctif
```

Pour une documentation :

```powershell
git switch -c docs/nom-du-document
```

6. Enregistrer la prise en charge :

```powershell
git add docs/TASKS.md
git commit -m "docs: prise en charge de la tâche ID"
git push -u origin nom-de-la-branche
```

---

# 8. Procédure pendant le travail

Après chaque étape importante :

1. Cocher les sous-tâches terminées.
2. Ajouter une note datée dans **Notes de progression**.
3. Signaler immédiatement tout blocage.
4. Indiquer les nouveaux risques.
5. Ne pas élargir le périmètre sans créer une nouvelle tâche.
6. Ne pas ajouter un fichier à la liste des fichiers autorisés sans justification.

Exemple :

```markdown
### Notes de progression

- 2026-08-02 : analyse des routes terminée.
- 2026-08-02 : modèle `Course` vérifié.
- 2026-08-02 : blocage identifié dans la migration `courses`.
```

---

# 9. Procédure lorsqu’une tâche est bloquée

Modifier immédiatement :

```markdown
- **Statut :** BLOQUÉE
```

Puis compléter :

```markdown
### Blocages

- Erreur exacte :
- Fichier concerné :
- Commande permettant de reproduire :
- Cause probable :
- Information ou décision nécessaire :
- Prochaine action possible :
```

Ne jamais masquer une erreur et ne jamais déclarer la tâche terminée pour contourner un blocage.

---

# 10. Procédure de clôture d’une tâche

Avant de passer la tâche à `TERMINÉE` :

1. Exécuter tous les tests obligatoires.
2. Vérifier le résultat manuellement.
3. Vérifier que seuls les fichiers autorisés ont été modifiés.
4. Mettre à jour les cases à cocher.
5. Mettre à jour `CURRENT_STATE.md`.
6. Mettre à jour `HANDOFF.md`.
7. Mettre à jour `CHANGELOG_AI.md`.
8. Ajouter les résultats des tests.
9. Passer d’abord le statut à `À VALIDER`.
10. Après validation finale, passer le statut à `TERMINÉE`.

Commandes de contrôle :

```powershell
git status
git diff --name-only origin/develop...HEAD
git diff --check
git log --oneline -5
```

Exemple de clôture :

```markdown
- **Statut :** TERMINÉE

### Résultats des tests

- `php artisan test` : réussi.
- `npm run build` : réussi.
- Vérification manuelle : réussie.
- Régression connue : aucune.
```

---

# 11. Tâches terminées

Déplacer ici uniquement les tâches réellement validées.

Aucune tâche terminée pour le moment.

---

# 12. Tâches annulées

Déplacer ici les tâches abandonnées en indiquant obligatoirement la raison.

Aucune tâche annulée pour le moment.
