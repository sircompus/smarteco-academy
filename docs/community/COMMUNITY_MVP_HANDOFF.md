# HANDOFF — Community MVP

## Projet

SmartEco Academy — Laravel + MySQL  
Racine locale : `C:\laragon\www\SEA`

## Bases

- application : `smarteco_academy`
- tests : `laravel_testing`

Ne jamais utiliser `migrate:fresh` sur la base application.

## Tables

- `community_posts`
- `community_comments`
- `community_likes`

## Modèles

- `App\Models\CommunityPost`
- `App\Models\CommunityComment`
- `App\Models\CommunityLike`

## Fonctionnement

### Publications

Un utilisateur vérifié peut publier, modifier et supprimer logiquement
ses propres publications.

### Commentaires

Un utilisateur vérifié peut commenter une publication publiée et supprimer
son propre commentaire.

### Likes

Un utilisateur peut aimer une publication une seule fois. Le même bouton
permet de retirer le like.

### Modération

Un utilisateur ayant le rôle `admin` peut :

- masquer ou restaurer une publication ;
- masquer ou restaurer un commentaire ;
- saisir un motif facultatif lors du masquage d’une publication ;
- voir dans le fil les contenus masqués.

Les étudiants ne voient que les contenus `published`.

## Routes

Préfixe URL : `/community`  
Préfixe des noms : `student.community.`

Routes principales :

- `student.community.index`
- `student.community.posts.store`
- `student.community.posts.edit`
- `student.community.posts.update`
- `student.community.posts.destroy`
- `student.community.comments.store`
- `student.community.comments.destroy`
- `student.community.likes.toggle`
- `student.community.moderation.posts`
- `student.community.moderation.comments`

## Navigation

- lien `Community` dans le menu étudiant ;
- module `community` actif dans la table `modules` ;
- carte du module reliée à `student.community.index`.

## Tests

- `CommunityFoundationTest`
- `CommunityPostCrudTest`
- `CommunityInteractionsTest`
- `CommunityFinalTest`

Total attendu : 28 tests.

## Commandes de validation

```powershell
php artisan optimize:clear
php artisan route:list --name=student.community
php artisan test tests\Feature\Community
npm.cmd run build
git diff --check
```

## Sauvegardes créées par les installateurs

- `routes/web.php.before-community-phase2.bak`
- `resources/views/layouts/student.blade.php.before-community-phase4.bak`
- `routes/web.php.before-community-phase4.bak`

## Évolutions V2

- signalements utilisateurs ;
- pièces jointes ;
- notifications ;
- groupes ;
- recherche ;
- hashtags ;
- temps réel ;
- tableau de modération dédié.
