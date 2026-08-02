# HANDOFF — SmartEco Academy

> Document de transmission entre les sessions de travail, les développeurs et les assistants IA.

## 1. Informations générales

- **Projet :** SmartEco Academy
- **Dépôt :** `smarteco-academy`
- **Branche de travail principale :** `develop`
- **Environnement local :** `C:\laragon\www\SEA`
- **Dernière mise à jour :** 2 août 2026
- **Responsable de la dernière transmission :** À compléter
- **Dernier commit concerné :** À compléter

---

## 2. Objectif de ce fichier

Ce fichier permet à une nouvelle session ChatGPT, Claude ou à un nouveau développeur de reprendre le projet sans perdre le contexte.

Il doit être mis à jour :

- à la fin de chaque session de travail ;
- avant de changer d’assistant IA ;
- avant de transmettre le projet à une autre personne ;
- lorsqu’une tâche importante est terminée ou bloquée ;
- lorsqu’une décision technique modifie la suite du projet.

---

## 3. Documents à lire avant toute modification

Lire les fichiers suivants dans cet ordre :

1. `AGENTS.md`
2. `CLAUDE.md`
3. `docs/PROJECT_CONTEXT.md`
4. `docs/CURRENT_STATE.md`
5. `docs/TASKS.md`
6. `docs/ARCHITECTURE.md`
7. `docs/DECISIONS.md`
8. `docs/DATABASE_SCHEMA.md`
9. `docs/CHANGELOG_AI.md`
10. `docs/HANDOFF.md`

Ne pas commencer une modification importante avant d’avoir lu ces documents.

---

## 4. État actuel du projet

### Fonctionnalités opérationnelles

- À compléter.
- Exemple : authentification des utilisateurs fonctionnelle.
- Exemple : connexion MySQL configurée.
- Exemple : espace administrateur accessible.

### Fonctionnalités en cours

- À compléter.
- Exemple : gestion des cours.
- Exemple : tableau de bord étudiant.
- Exemple : gestion des paiements.

### Fonctionnalités non commencées

- À compléter.
- Exemple : notifications.
- Exemple : génération des certificats.
- Exemple : système de suivi des candidatures.

---

## 5. Travail réalisé pendant la dernière session

### Résumé

À compléter avec une description courte et précise du travail effectué.

### Fichiers créés

```text
À compléter
```

### Fichiers modifiés

```text
À compléter
```

### Fichiers supprimés

```text
Aucun, sauf indication contraire.
```

### Base de données

- Migration ajoutée : À compléter.
- Table modifiée : À compléter.
- Colonne ajoutée ou supprimée : À compléter.
- Données de test ajoutées : À compléter.
- Sauvegarde réalisée : Oui / Non.

---

## 6. Tâche actuellement en cours

### Titre

À compléter.

### Objectif

À compléter.

### Travail déjà effectué

À compléter.

### Travail restant

À compléter.

### Critères de validation

- [ ] La fonctionnalité répond au besoin.
- [ ] Les erreurs sont gérées.
- [ ] Les validations sont présentes.
- [ ] Les tests ont été exécutés.
- [ ] L’interface a été vérifiée.
- [ ] La documentation a été mise à jour.
- [ ] Aucun fichier existant n’a été supprimé sans justification.

---

## 7. Prochaines tâches prioritaires

### Priorité 1 — Urgente

- [ ] À compléter.

### Priorité 2 — Importante

- [ ] À compléter.

### Priorité 3 — Amélioration

- [ ] À compléter.

La liste détaillée et permanente des tâches doit être maintenue dans `docs/TASKS.md`.

---

## 8. Problèmes connus et blocages

| Problème | Impact | Cause probable | Solution envisagée | Statut |
|---|---|---|---|---|
| À compléter | À compléter | À compléter | À compléter | Ouvert |

Ne pas masquer un problème non résolu. Indiquer clairement les erreurs, les fichiers concernés et les commandes permettant de reproduire le problème.

---

## 9. Tests et vérifications effectués

### Commandes exécutées

```powershell
# Exemples à adapter
php artisan test
php artisan migrate:status
php artisan route:list
npm run build
git status
```

### Résultats

- Tests automatiques : Réussis / Échoués / Non exécutés.
- Migrations : Validées / Non validées.
- Compilation front-end : Réussie / Échouée / Non exécutée.
- Vérification manuelle : Réalisée / Non réalisée.

### Erreurs restantes

```text
À compléter ou écrire : Aucune erreur connue.
```

---

## 10. Commandes utiles pour reprendre le travail

```powershell
cd C:\laragon\www\SEA

git fetch origin
git switch develop
git pull --ff-only origin develop

git status
composer install
npm install

php artisan migrate:status
php artisan serve
npm run dev
```

Exécuter uniquement les commandes nécessaires selon l’état réel du projet.

---

## 11. État Git au moment de la transmission

- **Branche actuelle :** `develop`
- **Dépôt distant :** `origin`
- **Dernier commit local :** À compléter
- **Dernier commit distant :** À compléter
- **Modifications non validées :** Oui / Non
- **Fichiers non suivis :** Oui / Non
- **Conflits Git :** Oui / Non

### Commandes de vérification

```powershell
git branch --show-current
git status
git log -1 --oneline
git diff
git diff --staged
```

---

## 12. Consignes pour la prochaine session ou le prochain assistant IA

1. Travailler uniquement sur la branche indiquée.
2. Exécuter `git pull` avant toute modification.
3. Ne pas remplacer l’ensemble du projet pour corriger un seul problème.
4. Ne pas supprimer un fichier existant sans expliquer la raison.
5. Ne pas modifier la structure de la base de données sans mettre à jour `docs/DATABASE_SCHEMA.md`.
6. Enregistrer toute décision importante dans `docs/DECISIONS.md`.
7. Mettre à jour `docs/CURRENT_STATE.md` après une évolution fonctionnelle importante.
8. Mettre à jour `docs/TASKS.md` après chaque tâche terminée ou ajoutée.
9. Ajouter les changements réalisés par une IA dans `docs/CHANGELOG_AI.md`.
10. Mettre à jour ce fichier avant de terminer la session.
11. Exécuter les tests disponibles avant le commit.
12. Faire des commits petits, descriptifs et cohérents.
13. Ne jamais prétendre qu’une correction est terminée sans l’avoir vérifiée.

---

## 13. Format de transmission obligatoire en fin de session

Copier et remplir ce bloc à chaque nouvelle transmission :

```markdown
## Transmission du AAAA-MM-JJ — HH:MM

- Assistant ou développeur :
- Branche :
- Commit :
- Objectif de la session :
- Travail terminé :
- Travail non terminé :
- Fichiers modifiés :
- Migrations ou changements SQL :
- Tests exécutés :
- Résultat des tests :
- Problèmes connus :
- Prochaine action exacte :
- Commande recommandée pour reprendre :
```

---

## 14. Dernière transmission

## Transmission du 2026-08-02 — À compléter

- **Assistant ou développeur :** À compléter
- **Branche :** `develop`
- **Commit :** À compléter
- **Objectif de la session :** Mise en place de la documentation de coordination du projet.
- **Travail terminé :** Création de la structure documentaire destinée à coordonner ChatGPT, Claude et les développeurs.
- **Travail non terminé :** Compléter les documents avec les informations réelles du projet.
- **Fichiers modifiés :** `docs/HANDOFF.md`
- **Migrations ou changements SQL :** Aucun.
- **Tests exécutés :** Aucun test applicatif requis pour ce document.
- **Problèmes connus :** Les autres fichiers de documentation doivent encore être complétés.
- **Prochaine action exacte :** Créer et remplir `docs/PROJECT_CONTEXT.md`.
- **Commande recommandée pour reprendre :**

```powershell
cd C:\laragon\www\SEA
git switch develop
git pull --ff-only origin develop
code docs\PROJECT_CONTEXT.md
```
