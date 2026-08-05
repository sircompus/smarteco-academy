# Registration Phase 1 — Audit et couverture

## Périmètre analysé

- contrôleur étudiant des inscriptions ;
- contrôleur étudiant des documents ;
- contrôleur administrateur ;
- modèles `Registration`, `RegistrationDocument` et
  `RegistrationStatusHistory` ;
- routes étudiantes et administrateur ;
- migration du module ;
- vues du module.

## Couverture ajoutée

### Fondation

- génération de l’UUID et de la référence ;
- relations utilisateur, niveau et filière ;
- règles `canBeEdited()` et `canBeSubmitted()` ;
- isolation des inscriptions par utilisateur.

### Parcours étudiant

- création d’un brouillon ;
- création de l’historique initial ;
- cohérence entre niveau et filière ;
- interdiction d’accès aux demandes d’un autre utilisateur ;
- documents obligatoires ;
- soumission du dossier ;
- verrouillage du dossier après soumission.

### Documents

- téléversement ;
- remplacement sans doublon ;
- suppression de l’ancien fichier ;
- téléchargement ;
- suppression ;
- contrôle du propriétaire ;
- verrouillage après soumission.

### Administration

- contrôle du rôle administrateur ;
- passage en examen ;
- historique des changements ;
- motif obligatoire pour les statuts négatifs ;
- blocage de l’acceptation si un document n’est pas validé ;
- validation des documents ;
- acceptation finale ;
- rejet des transitions interdites.

## Phase suivante

Après exécution, toute erreur doit être corrigée dans la cause
fonctionnelle réelle. Les tests ne doivent pas être assouplis pour
masquer un défaut du module.
