# CURRENT_STATE.md

Dernière mise à jour : 02/08/2026, par Claude.

## Branche stable
`develop` — c'est **la seule branche de travail réelle**. `main` existe mais est quasiment vide, ne jamais y travailler.

## Branche de travail actuelle
`develop` (pas de branche séparée par tâche — voir `AGENTS.md` pour la raison).

## Fonctionnalités terminées
Voir `docs/TASKS.md` pour le détail : Inscription/Admission, Centre (packs/paiements/reçus/bibliothèque), rôles Prof/Superviseur, Cours/leçons, Formations courtes, CV/ATS/Portfolio, photo de profil, nettoyage navigation.

## Fonctionnalités en cours
Aucune au moment de la rédaction — dernière tâche livrée : nettoyage des liens morts du tableau de bord étudiant/admin.

## Fonctionnalités prévues (non commencées)
Community/Divertissement, Projets/Services académiques, Examens, synchronisation multi-PC (reportée par Ali).

## Fichiers récemment modifiés (dernière session connue)
- `resources/views/admin/dashboard.blade.php` — liens morts remplacés
- `resources/views/student/dashboard.blade.php` — statistiques réelles + cartes cliquables
- `routes/web.php` — calcul des vraies statistiques dans la route dashboard étudiant
- `resources/views/student/cv/templates/classique.blade.php` — typographie stricte (Times New Roman, 3 tailles, colonne unique)
- `resources/views/portfolio/show.blade.php` — fond du bandeau sécurisé en style inline (bug texte invisible corrigé), sections skills/langues en liste

## Tests exécutés
Aucun test automatisé (`tests/Feature/*`) n'a été exécuté durant les sessions de développement — les vérifications se sont faites manuellement par Ali via capture d'écran après chaque installation. **Point d'amélioration identifié** : faire tourner `php artisan test` avant chaque livraison importante.

## Problèmes connus / non résolus
1. **Incident Git résolu mais cause racine non confirmée** : Ali s'est retrouvé sur `main` au lieu de `develop`. Résolu, mais surveiller une récidive.
2. **Aucun test automatisé n'a jamais été exécuté sur ce projet.** À mettre en place.
3. Le module `CourseResource` (ressources par cours) est codé mais non exploité en usage réel — `AcademicResource` (bibliothèque indépendante) est le système réellement utilisé.
4. Modules "Bientôt disponible" affichés sur le tableau de bord étudiant (Examens, Projets, Community) — cartes visibles mais non cliquables, honnête sur ce qui n'existe pas encore.

## Prochaine action recommandée
Demander à Ali quelle est sa priorité : (a) commencer un nouveau module (Community, Projets, ou Examens), (b) mettre en place la synchronisation multi-PC reportée, (c) mettre en place une suite de tests automatisés, ou (d) autre chose. Ne pas décider à sa place.



## Veille d’emploi

Les fondations MySQL et Eloquent sont opérationnelles.

Éléments disponibles :

- six tables MySQL ;
- six modèles Eloquent ;
- factories ;
- relations ;
- Policy de propriété ;
- contraintes anti-doublons ;
- tests MySQL réussis.

Non encore développé :

- interface utilisateur ;
- récupération externe des offres ;
- moteur de scoring ;
- Queue et Scheduler ;
- notifications ;
- résumé quotidien.