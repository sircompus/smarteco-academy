# MASTER_PROMPT.md

Copie-colle ce texte tel quel au tout début de toute nouvelle session (Claude ou ChatGPT) pour reprendre le projet SmartEco Academy.

---

Tu reprends le développement du projet **SmartEco Academy**.

Dépôt GitHub (source unique de vérité) :
https://github.com/sircompus/smarteco-academy.git
Branche de travail : `develop` (ne jamais utiliser `main`)

**Contrainte technique importante** : tu n'as pas d'accès direct pour pousser sur ce dépôt. Le porteur du projet (Ali) installe lui-même chaque fichier que tu lui fournis, puis fait `git commit` / `git push` de son côté. Ne prétends jamais avoir poussé quelque chose toi-même.

Avant toute action, lis dans cet ordre :
1. `AGENTS.md` — règles obligatoires pour tout assistant IA sur ce projet
2. `CLAUDE.md` (si tu es Claude) — contexte et erreurs déjà rencontrées à éviter
3. `docs/PROJECT_CONTEXT.md` — ce qu'est le projet
4. `docs/ARCHITECTURE.md` — comment il est structuré techniquement
5. `docs/DATABASE_SCHEMA.md` — schéma de base de données
6. `docs/DECISIONS.md` — décisions déjà tranchées, ne pas les reproposer
7. `docs/CURRENT_STATE.md` — état exact actuel
8. `docs/HANDOFF.md` — ce que le dernier assistant a fait, prochaine action recommandée
9. `docs/TASKS.md` — liste des tâches et leurs statuts

Une fois ces fichiers lus, indique-moi clairement, avant de coder quoi que ce soit :
1. La tâche active recommandée (d'après `HANDOFF.md`)
2. Son statut
3. Ce qui a déjà été fait
4. Ce qu'il reste à faire
5. Ta première question de clarification si besoin, ou ta proposition de première action

Ne modifie aucun fichier avant cette étape de vérification. N'invente jamais qu'une fonctionnalité existe ou est terminée si ce n'est pas confirmé dans `docs/CURRENT_STATE.md` ou dans le code lui-même.

---

## Note pour Ali : comment utiliser ce fichier

- Colle le bloc ci-dessus (entre les `---`) en tout début de conversation avec Claude ou ChatGPT
- Si tu as travaillé avec l'autre assistant entre-temps, dis-le explicitement dans ton premier message ("j'ai avancé avec ChatGPT depuis la dernière fois, voici ce qu'on a fait : ...") pour que l'assistant actuel mette à jour son contexte
- En fin de session, demande à l'assistant de mettre à jour `docs/CURRENT_STATE.md`, `docs/HANDOFF.md` et `docs/CHANGELOG_AI.md` avant de terminer, puis installe/committe/pousse ces fichiers comme n'importe quel autre livrable
