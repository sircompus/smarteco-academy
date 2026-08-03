$path0 = "C:\laragon\www\SEA\database\seeders\RealCurriculumSeeder.php"
$content0 = @'
<?php

namespace Database\Seeders;

use App\Models\AcademicProgram;
use App\Models\Semester;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RealCurriculumSeeder extends Seeder
{
    /**
     * Reproduit exactement le vrai programme d'Ali (retranscrit depuis ses
     * captures d'écran d'origine), de façon permanente cette fois — plus
     * besoin de repasser par la saisie manuelle du Générateur de cursus
     * si la base doit un jour être réinitialisée.
     *
     * Structure : [Nom exact de la filière (AcademicProgram.name)] => [
     *     numéro_semestre => [liste des modules],
     * ]
     */
    public function run(): void
    {
        $troncCommunS1 = [
            'Introduction aux sciences de gestion',
            'Comptabilité générale 1',
            "Introduction à l'étude de droit",
            'Microéconomie 1',
            'Mathématiques',
            'Macroéconomie 1',
            'Méthodologie du travail universitaire',
        ];

        $troncCommunS2Gestion = [
            'Management fondamental',
            'Comptabilité générale 2',
            'Statistique descriptive',
            'Macroéconomie 2',
            'Mathématiques financières',
            'Microéconomie 2',
            'Compétence numérique et IA',
        ];

        $troncCommunS2Economie = [
            'Management',
            'Comptabilité générale 2',
            'Statistique descriptive',
            'Macroéconomie 2',
            'Mathématiques financières',
            'Microéconomie 2',
            'Compétence numérique et IA',
        ];

        $troncCommunS3 = [
            'Comptabilité analytique de gestion',
            'Economie du Maroc',
            'Management des organisations',
            'Probabilité',
            "Droit de l'entreprise",
            'Sociologie des organisations',
            'Langue étrangère',
        ];

        $troncCommunS4 = [
            'Analyse financière',
            'Marketing de base',
            'Comptabilité des sociétés',
            'Introduction à la GRH',
            'Informatique de gestion',
            'Analyse et traitement des données',
            'Langue étrangère',
        ];

        $structure = [
            'Tronc commun en gestion' => [
                1 => $troncCommunS1,
                2 => $troncCommunS2Gestion,
                3 => $troncCommunS3,
                4 => $troncCommunS4,
            ],
            'Tronc commun en économie' => [
                1 => $troncCommunS1,
                2 => $troncCommunS2Economie,
                3 => $troncCommunS3,
                4 => $troncCommunS4,
            ],
            'Management Ressources Humaines' => [
                5 => [
                    'Contrôle de gestion social',
                    'Gestion de la paie',
                    'Outils GRH',
                    'Gestion prévisionnelle des emplois et compétences',
                    'Droit social',
                    'Langues étrangères 2',
                    'Communication professionnelle et outils digitaux',
                ],
                6 => [
                    'Management stratégique',
                    'Gestion des relations sociales',
                    'Intelligence artificielle et SIRH',
                    'Entrepreneuriat et gestion de projet',
                    'Audit social',
                    'Langues étrangères 2',
                    'Culture entrepreneuriale',
                ],
            ],
            'Comptabilité Finance et Fiscalité' => [
                5 => [
                    'Contrôle de gestion',
                    "Choix d'investissement et modes de financement",
                    'Fiscalité des entreprises 1',
                    'Comptabilité approfondie',
                    'Droit des affaires',
                    'Langues étrangères 2',
                    'Communication professionnelle et outils digitaux',
                ],
                6 => [
                    'Gestion de la trésorerie',
                    'Fiscalité des entreprises 2',
                    'Intelligence artificielle et finance',
                    'Entrepreneuriat et gestion de projet',
                    'Audit comptable et financier',
                    'Langues étrangères 2',
                    'Culture entrepreneuriale',
                ],
            ],
            'Commerce et Marketing' => [
                5 => [
                    'Contrôle de gestion',
                    "Choix d'investissement et modes de financement",
                    'Fiscalité des entreprises 1',
                    'Comptabilité approfondie',
                    'Droit des affaires',
                    'Langues étrangères 2',
                    'Communication professionnelle et outils digitaux',
                ],
                6 => [
                    'Management stratégique',
                    'Entrepreneuriat et gestion de projet',
                    'Intelligence artificielle et commerce',
                    'Technique du marketing international',
                    'Marketing stratégique',
                    'Langues étrangères 2',
                    'Culture entrepreneuriale',
                ],
            ],
            'Économétrie' => [
                5 => [
                    'Microéconomie approfondie',
                    'Intelligence artificielle et recherche opérationnelle',
                    "Tests d'hypothèses",
                    'Economie internationale',
                    'Entrepreneuriat et gestion de projet',
                    'Langues étrangères 2',
                    'Initiation aux logiciels économétriques',
                ],
                6 => [
                    'Management stratégique',
                    'Entrepreneuriat et gestion de projet',
                    'Intelligence artificielle et commerce',
                    'Technique du marketing international',
                    'Marketing stratégique',
                    'Langues étrangères 2',
                    'Culture entrepreneuriale',
                ],
            ],
        ];

        $programCount = 0;
        $semesterCount = 0;
        $moduleCount = 0;

        foreach ($structure as $programName => $semesters) {
            $program = AcademicProgram::where('name', $programName)->first();

            if (! $program) {
                $this->command?->warn("Filière introuvable, ignorée : {$programName}");

                continue;
            }

            $programCount++;

            foreach ($semesters as $number => $moduleNames) {
                $semester = Semester::query()->updateOrCreate(
                    [
                        'academic_program_id' => $program->id,
                        'number' => $number,
                    ],
                    [
                        'uuid' => (string) Str::uuid(),
                        'name' => "Semestre {$number}",
                        'code' => "S{$number}",
                        'is_active' => true,
                        'sort_order' => $number,
                    ]
                );

                $semesterCount++;

                $keptNames = [];

                foreach ($moduleNames as $position => $moduleName) {
                    $keptNames[] = Str::lower(trim($moduleName));

                    Subject::query()->updateOrCreate(
                        [
                            'semester_id' => $semester->id,
                            'name' => trim($moduleName),
                        ],
                        [
                            'uuid' => (string) Str::uuid(),
                            'slug' => $this->uniqueSlug($programName.'-s'.$number.'-'.$moduleName),
                            'code' => 'S'.$number.'-M'.($position + 1),
                            'credits' => 0,
                            'coefficient' => 1,
                            'is_active' => true,
                            'sort_order' => $position,
                        ]
                    );

                    $moduleCount++;
                }

                // Retire les anciens modules génériques ("Module 1", "Module 2"...)
                // qui ne font pas partie du vrai programme, pour éviter les doublons.
                $semester->subjects()
                    ->get()
                    ->reject(fn (Subject $subject) => in_array(Str::lower(trim($subject->name)), $keptNames, true))
                    ->each(fn (Subject $subject) => $subject->delete());
            }
        }

        $this->command?->info("{$programCount} filière(s), {$semesterCount} semestre(s), {$moduleCount} module(s) synchronisés avec le vrai programme.");
    }

    private function uniqueSlug(string $value): string
    {
        $baseSlug = Str::slug($value);
        $slug = $baseSlug;
        $number = 2;

        while (Subject::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$number;
            $number++;
        }

        return $slug;
    }
}

'@
try {
    [System.IO.File]::WriteAllText($path0, $content0, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: database/seeders/RealCurriculumSeeder.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: database/seeders/RealCurriculumSeeder.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path1 = "C:\laragon\www\SEA\database\seeders\DatabaseSeeder.php"
$content1 = @'
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            ModuleSeeder::class,
            AcademicSeeder::class,
            SemesterSubjectSeeder::class,
            RealCurriculumSeeder::class,
            SkillSuggestionSeeder::class,
        ]);
    }
}
'@
try {
    [System.IO.File]::WriteAllText($path1, $content1, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: database/seeders/DatabaseSeeder.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: database/seeders/DatabaseSeeder.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path2 = "C:\laragon\www\SEA\docs\DECISIONS.md"
$content2 = @'
# DECISIONS.md — Décisions déjà tranchées (ne pas reproposer)

Format : date approximative, décision, raison.

## Architecture générale
- **Laravel + MySQL**, pas de couche Python/FastAPI séparée malgré le plan initial (dossier technique ChatGPT) — simplification décidée pour un MVP plus rapide à livrer.
- **Une seule branche `develop`**, pas de branches multiples par fonctionnalité — Ali gère seul Git manuellement, la complexité multi-branches a causé un incident de confusion majeur (voir plus bas).

## Export PDF / impression
- **Pas de librairie PDF serveur** (dompdf etc.) — export via bouton d'impression navigateur + CSS `@media print`. Choix validé après plusieurs itérations sur les reçus.
- Reçus de paiement : format **A5 portrait** (148×210mm), logo en haut à gauche, titre au centre, numéro/date à droite, pied de page avec mentions d'encaissement/impression.
- État financier (rapport) : format **A4**.

## CV / ATS / Portfolio
- Le CV, sa version ATS, et le portfolio public **partagent la même donnée sous-jacente** (`CvProfile` et relations) — aucune duplication de saisie.
- **Résumé professionnel auto-généré** si l'étudiant le laisse vide (3-10 lignes, basé sur formations/expériences/compétences), jamais persisté en base, ne remplace jamais un résumé saisi manuellement.
- **Compétences classées par catégorie**, catalogue géré depuis l'admin (`SkillSuggestion`), catégories définies par Ali : Intelligence artificielle et analyse, Systèmes d'information et cloud, Développement et outils numériques, Logiciels de gestion, Infrastructure et maintenance, Compétences professionnelles.
- **Langues en liste fixe** (menu déroulant, pas texte libre) : Arabe, Français, Anglais, Espagnol, Allemand, + option "Autre".
- **Format du CV "Classique" strictement défini par Ali** : police Times New Roman forcée en CSS pur (`!important`, indépendant de Tailwind pour éviter tout risque de classes non compilées), noir garanti, titre 20pt, sous-titres 16pt, paragraphes 12pt, interligne 1.5, **colonne unique obligatoire** (le nombre de pages n'est pas une contrainte).
- Séparateur langue/niveau : **deux-points** (`Arabe : Langue maternelle`), jamais de tiret.
- Compétences affichées **une par ligne** (pas en pastilles horizontales), notamment pour la catégorie "Compétences professionnelles" qui contient des phrases longues.
- **Accès à la bibliothèque de ressources académiques** : règle **stricte**, un pack de type `semestre` actif est **obligatoire** — un pack limité à un seul module ne suffit pas (contrairement à l'accès aux leçons de cours, plus permissif).
- Admin peut éditer le CV d'un étudiant à sa place (`Admin\CvBuilderController`), réutilisant les mêmes vues que le builder étudiant.
- Icône photo dans le menu étudiant (`CvProfile::show_in_navigation`) : fonctionnalité générique réutilisable pour n'importe quel compte, pas codée en dur pour Ali spécifiquement.

## Garde-fou refusé
- Une bannière d'avertissement admin sur la cohérence "niveau du pack vs niveau du dossier d'admission accepté" a été construite puis **explicitement retirée à la demande d'Ali** — il ne voulait aucun changement côté admin sur ce point, seulement la restriction d'accès côté étudiant (déjà satisfaite par ailleurs).

## Incident du 02/08/2026 — confusion de branche Git
Ali s'est retrouvé sur la branche `main` (quasiment vide) au lieu de `develop` (tout le travail réel), après avoir cru pousser normalement. Cause exacte non identifiée avec certitude, possiblement un `git checkout main` antérieur oublié. Résolu par `git checkout develop` (après suppression d'un fichier non suivi qui bloquait le switch). Décision : **toujours vérifier `git status` en tout début de diagnostic** si Ali rapporte qu'un fichier "de base" semble manquant.

## Modules explicitement reportés (ne pas construire sans demande explicite)
- Community / Divertissement
- Projets / Services académiques
- Examens

## Incident du 03/08/2026 — base de données vide, curriculum réel perdu
La base `smarteco_academy` s'est retrouvée sans aucune table (migrations jamais appliquées), causant une cascade de pannes (connexion impossible, pages vides, packs manquants). Résolu par `php artisan migrate` + `php artisan db:seed`. **Découverte importante** : le vrai programme académique d'Ali (modules réels par filière/semestre) n'existait QUE dans la saisie manuelle faite via `admin/centre/curriculum`, jamais dans un seeder — il a donc été perdu avec la réinitialisation. **Retrouvé** en fouillant la transcription complète de la conversation (les 6 blocs de texte donnés initialement), et transformé en seeder permanent `RealCurriculumSeeder`, désormais dans `DatabaseSeeder`. Décision : **toute donnée métier réelle et non triviale doit être capturée dans un seeder, jamais laissée uniquement en saisie manuelle via l'interface.**

'@
try {
    [System.IO.File]::WriteAllText($path2, $content2, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: docs/DECISIONS.md" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: docs/DECISIONS.md -- $($_.Exception.Message)" -ForegroundColor Red
}

$path3 = "C:\laragon\www\SEA\docs\CURRENT_STATE.md"
$content3 = @'
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
5. **RÉSOLU le 03/08/2026** : la base `smarteco_academy` s'était retrouvée entièrement vide (migrations jamais appliquées). Résolu par migrate + db:seed. Le vrai programme académique (curriculum) a été retrouvé et rendu permanent via `RealCurriculumSeeder` — voir `docs/DECISIONS.md`.
6. Les **packs** créés manuellement par Ali (prix, activation) ne sont pas capturés dans un seeder au-delà des valeurs par défaut de `PackSeeder` (250 DH semestre) — si la base est de nouveau réinitialisée, les prix réels devront être réajustés manuellement.

## Prochaine action recommandée
Demander à Ali quelle est sa priorité : (a) commencer un nouveau module (Community, Projets, ou Examens), (b) mettre en place la synchronisation multi-PC reportée, (c) mettre en place une suite de tests automatisés, ou (d) autre chose. Ne pas décider à sa place.

'@
try {
    [System.IO.File]::WriteAllText($path3, $content3, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: docs/CURRENT_STATE.md" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: docs/CURRENT_STATE.md -- $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "Termine." -ForegroundColor Cyan
