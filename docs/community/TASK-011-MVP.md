# TASK-011 — Module Community MVP

Statut : TERMINÉ sous réserve du passage de tous les tests finaux.

## Périmètre livré

- fil de publications ;
- création, modification et suppression par l’auteur ;
- commentaires ;
- suppression du commentaire par son auteur ;
- likes avec bascule J’aime / Je n’aime plus ;
- pagination ;
- modération administrateur ;
- masquage et restauration des publications ;
- masquage et restauration des commentaires ;
- contenus masqués invisibles aux étudiants ;
- contenus masqués visibles aux administrateurs ;
- menu étudiant ;
- carte du module sur le tableau de bord ;
- policies, validations, factories et tests.

## Hors périmètre MVP

- messagerie privée ;
- groupes ;
- images et pièces jointes ;
- temps réel ;
- abonnements ;
- hashtags et mentions ;
- notifications avancées ;
- signalement par les utilisateurs.

## Validation finale

```powershell
php artisan test tests\Feature\Community
npm.cmd run build
git diff --check
```

Résultat attendu : 28 tests Community réussis.
