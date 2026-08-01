<?php

namespace Database\Seeders;

use App\Models\CvProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AliBahtitCvSeeder extends Seeder
{
    /**
     * Se lance à la demande :
     *   php artisan db:seed --class=AliBahtitCvSeeder
     *
     * Remplit le profil CV/Portfolio du compte sircompus@gmail.com
     * avec les vraies données extraites de son CV, rend le portfolio
     * public et active l'icône dans le menu étudiant.
     */
    public function run(): void
    {
        $user = User::whereRaw('LOWER(email) = ?', ['sircompus@gmail.com'])->first();

        if (! $user) {
            $this->command?->error('Compte sircompus@gmail.com introuvable.');

            return;
        }

        $existingSlug = CvProfile::where('user_id', $user->id)->value('public_slug');

        $profile = CvProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'uuid' => (string) Str::uuid(),
                'full_name' => 'Ali Bahtit',
                'headline' => "Candidat au doctorat en Management, Systèmes d'Information et Intelligence Artificielle",
                'email' => 'sircompus@gmail.com',
                'phone' => '06 63 62 83 76',
                'address' => 'Tétouan, Maroc',
                'summary' => "Candidat au doctorat disposant d'un parcours pluridisciplinaire en économie-gestion, management des systèmes d'information, leadership managérial augmenté par l'intelligence artificielle et transformation numérique. Les travaux de recherche menés sur l'industrie 4.0, la migration des systèmes vers le cloud via Google Cloud Platform et la veille stratégique augmentée traduisent une trajectoire scientifique cohérente centrée sur l'innovation, l'aide à la décision et la performance organisationnelle. Plus de vingt ans d'expérience en informatique, maintenance, logiciels de gestion, formation et accompagnement des utilisateurs.",
                'cv_template' => 'moderne',
                'portfolio_template' => 'elegant',
                'is_public' => true,
                'show_in_navigation' => true,
                'public_slug' => $existingSlug ?? 'ali-bahtit-'.Str::lower(Str::random(6)),
            ]
        );

        // On repart propre pour éviter les doublons si on relance le seeder.
        $profile->educations()->delete();
        $profile->experiences()->delete();
        $profile->skills()->delete();
        $profile->languages()->delete();

        // --- Formation (de la plus récente à la plus ancienne) ---
        $educations = [
            [
                'institution' => "Master en Leadership managérial augmenté par les outils d'intelligence artificielle",
                'degree' => 'Master',
                'field_of_study' => "Leadership augmenté, aide à la décision, pilotage de la performance, innovation et conduite du changement",
                'start_date' => '2024-09-01',
                'end_date' => '2026-06-30',
                'is_current' => true,
            ],
            [
                'institution' => "Master spécialisé en Management des systèmes d'information",
                'degree' => 'Master spécialisé',
                'field_of_study' => 'Gouvernance des SI, alignement stratégique, transformation numérique et management de la performance',
                'start_date' => '2022-09-01',
                'end_date' => '2024-06-30',
                'is_current' => false,
            ],
            [
                'institution' => 'Licence en économie, parcours Gestion',
                'degree' => 'Licence',
                'field_of_study' => 'Économie, parcours Gestion',
                'start_date' => '2019-09-01',
                'end_date' => '2020-06-30',
                'is_current' => false,
            ],
            [
                'institution' => 'EPSIEL, Fès',
                'degree' => 'DCESS',
                'field_of_study' => 'Électronique et informatique industrielle',
                'start_date' => '2001-09-01',
                'end_date' => '2003-06-30',
                'is_current' => false,
            ],
            [
                'institution' => 'Licence en informatique électronique',
                'degree' => 'Licence',
                'field_of_study' => 'Système de réseaux',
                'start_date' => '1997-09-01',
                'end_date' => '2001-06-30',
                'is_current' => false,
            ],
            [
                'institution' => 'Baccalauréat en sciences expérimentales',
                'degree' => 'Baccalauréat',
                'field_of_study' => 'Sciences expérimentales',
                'start_date' => '1996-09-01',
                'end_date' => '1997-06-30',
                'is_current' => false,
            ],
        ];

        foreach ($educations as $i => $education) {
            $profile->educations()->create($education + ['sort_order' => $i]);
        }

        // --- Expérience (académique, pédagogique et professionnelle) ---
        $experiences = [
            [
                'company' => 'Centres partenaires et accompagnement universitaire',
                'position' => 'Formateur et intervenant en support académique',
                'location' => 'Tétouan',
                'start_date' => '2019-01-01',
                'end_date' => null,
                'is_current' => true,
                'description' => "Conception et animation de cours de support du semestre S1 au semestre S6 ainsi que pour des parcours de master en management des systèmes d'information et en ressources humaines. Enseignement et accompagnement en économie, gestion financière, contrôle de gestion, finance et outils numériques. Préparation de supports structurés, d'exercices pratiques et de dispositifs d'accompagnement individualisé. Mobilisation raisonnée des outils d'intelligence artificielle pour la recherche, la synthèse et la personnalisation des apprentissages.",
            ],
            [
                'company' => 'Divers centres de formation, Région Nord',
                'position' => 'Formateur en informatique, programmation et logiciels de gestion',
                'location' => 'Région Nord',
                'start_date' => '2005-01-01',
                'end_date' => '2023-12-31',
                'is_current' => false,
                'description' => 'Animation de formations en R, Python, PHP, HTML, JEE, bases de données, bureautique avancée et logiciels de gestion. Formation continue auprès de centres de formation, associations, structures professionnelles et établissements partenaires. Adaptation des contenus aux besoins opérationnels et accompagnement des utilisateurs dans l\'adoption des outils numériques.',
            ],
            [
                'company' => 'Cabinet fiduciaire & Visitec SARL',
                'position' => 'Expériences complémentaires en gestion et environnement professionnel',
                'location' => 'Tétouan',
                'start_date' => '2015-01-01',
                'end_date' => '2019-12-31',
                'is_current' => false,
                'description' => 'Immersion en cabinet fiduciaire : comptabilité, fiscalité et gestion administrative. Stage au sein de Visitec SARL : compréhension de l\'environnement opérationnel des centres de visite technique.',
            ],
            [
                'company' => 'BEGIN TO INFORMATIQUE',
                'position' => 'Technicien et responsable maintenance informatique, électronique industrielle et réseaux',
                'location' => 'Région Nord',
                'start_date' => '1999-01-01',
                'end_date' => '2017-12-31',
                'is_current' => false,
                'description' => 'Responsabilité du service maintenance et gestion d\'interventions préventives et correctives. Maintenance de systèmes informatiques, électroniques et industriels, réseaux et équipements automatisés. Gestion de contrats de maintenance, coordination des interventions et formation des utilisateurs. Interventions auprès d\'établissements publics, de centres techniques, d\'instituts de formation et d\'organisations de la région Nord.',
            ],
        ];

        foreach ($experiences as $i => $experience) {
            $profile->experiences()->create($experience + ['sort_order' => $i]);
        }

        // --- Compétences ---
        $skills = [
            // IA et analyse
            'IA générative appliquée au management' => ['Intelligence artificielle et analyse', 'expert'],
            "Analyse et synthèse de l'information" => ['Intelligence artificielle et analyse', 'expert'],
            'Veille stratégique' => ['Intelligence artificielle et analyse', 'expert'],
            'Machine learning' => ['Intelligence artificielle et analyse', 'avance'],
            'Business intelligence' => ['Intelligence artificielle et analyse', 'avance'],
            'Power BI' => ['Intelligence artificielle et analyse', 'avance'],
            'R (langage statistique)' => ['Intelligence artificielle et analyse', 'avance'],
            'Python' => ['Intelligence artificielle et analyse', 'avance'],
            // SI et cloud
            'Gouvernance des SI' => ["Systèmes d'information et cloud", 'expert'],
            'Transformation numérique' => ["Systèmes d'information et cloud", 'expert'],
            'Google Cloud Platform' => ["Systèmes d'information et cloud", 'avance'],
            'Docker' => ["Systèmes d'information et cloud", 'intermediaire'],
            'Kubernetes' => ["Systèmes d'information et cloud", 'intermediaire'],
            'SQL Server' => ["Systèmes d'information et cloud", 'avance'],
            'Bases de données' => ["Systèmes d'information et cloud", 'expert'],
            // Développement
            'PHP' => ['Développement et outils numériques', 'avance'],
            'HTML' => ['Développement et outils numériques', 'avance'],
            'JEE' => ['Développement et outils numériques', 'intermediaire'],
            'Excel' => ['Développement et outils numériques', 'expert'],
            'Word' => ['Développement et outils numériques', 'expert'],
            'PowerPoint' => ['Développement et outils numériques', 'expert'],
            'MS Project' => ['Développement et outils numériques', 'avance'],
            // Logiciels de gestion
            'SAGE SAARI' => ['Logiciels de gestion', 'avance'],
            'SAGE i7' => ['Logiciels de gestion', 'avance'],
            'CIEL' => ['Logiciels de gestion', 'intermediaire'],
            'EBP' => ['Logiciels de gestion', 'intermediaire'],
            // Infrastructure
            'Réseaux' => ['Infrastructure et maintenance', 'expert'],
            'Électromécanique' => ['Infrastructure et maintenance', 'avance'],
            'Électronique industrielle' => ['Infrastructure et maintenance', 'avance'],
            // Professionnelles
            'Rigueur, autonomie, persévérance et sens des responsabilités' => ['Compétences professionnelles', 'expert'],
            "Capacité d'analyse, de synthèse, d'organisation et de résolution de problèmes" => ['Compétences professionnelles', 'expert'],
            'Pédagogie, communication, vulgarisation et accompagnement du changement' => ['Compétences professionnelles', 'expert'],
            'Travail en équipe, relation avec les parties prenantes et orientation résultats' => ['Compétences professionnelles', 'expert'],
        ];

        $i = 0;
        foreach ($skills as $name => [$category, $level]) {
            $profile->skills()->create([
                'name' => $name,
                'category' => $category,
                'level' => $level,
                'sort_order' => $i,
            ]);
            $i++;
        }

        // --- Langues ---
        $languages = [
            'Arabe' => 'natif',
            'Français' => 'courant',
            'Espagnol' => 'intermediaire',
            'Anglais' => 'intermediaire',
        ];

        $i = 0;
        foreach ($languages as $name => $level) {
            $profile->languages()->create([
                'name' => $name,
                'level' => $level,
                'sort_order' => $i,
            ]);
            $i++;
        }

        $this->command?->info('Profil CV de Ali Bahtit rempli avec succès.');
        $this->command?->info('Lien public : '.$profile->public_url);
    }
}
