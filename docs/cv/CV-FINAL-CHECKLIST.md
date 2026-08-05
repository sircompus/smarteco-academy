# Module CV — Checklist finale

## Fonctionnalités

- [ ] téléversement, remplacement et suppression de la photo ;
- [ ] modèle classique avec photo facultative ;
- [ ] modèle moderne avec photo facultative ;
- [ ] modèle ATS sans photo ;
- [ ] police Times New Roman ;
- [ ] texte courant 12 pt ;
- [ ] sous-titres 14 pt ;
- [ ] titres 16 pt ;
- [ ] nom 20 pt ;
- [ ] interligne 1,5 ;
- [ ] retrait de première ligne 1,25 cm ;
- [ ] nettoyage des caractères parasites au début des paragraphes ;
- [ ] aperçu intégré ;
- [ ] sélection visuelle des modèles ;
- [ ] sauvegarde avec détection des modifications ;
- [ ] progression du CV ;
- [ ] aide à la rédaction ;
- [ ] contrôle avant export ;
- [ ] règles de pagination A4.

## Vérification visuelle

Ouvrir :

```text
http://127.0.0.1:8000/cv
```

Contrôler sur ordinateur et mobile :

1. la photo ;
2. les trois modèles ;
3. les textes longs ;
4. les coupures de page ;
5. l’impression A4 à 100 % ;
6. la version ATS ;
7. l’enregistrement du modèle choisi.

## Validation automatisée

Depuis la racine du projet :

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\cv-final-check.ps1
```

Le script doit terminer par :

```text
VALIDATION CV RÉUSSIE
```

## Git

Ne pas utiliser `git add .`.

Vérifier d’abord :

```powershell
git status --short
git diff --check
```

Ajouter ensuite uniquement les fichiers CV validés.
