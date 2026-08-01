<?php

namespace App\Services;

use App\Models\CvProfile;

class CvSummaryGeneratorService
{
    /**
     * Génère un résumé professionnel de 3 à 10 lignes à partir des
     * formations, expériences, compétences et langues du profil.
     * N'est jamais enregistré en base — recalculé à chaque affichage
     * tant que l'étudiant n'a pas rempli son propre résumé.
     */
    public function generate(CvProfile $profile): string
    {
        $lines = [];

        // --- Ligne 1 : accroche générale ---
        $latestEducation = $profile->educations->first();

        if ($latestEducation) {
            $degree = $latestEducation->degree ?: 'formation';
            $field = $latestEducation->field_of_study ? " en {$latestEducation->field_of_study}" : '';
            $institution = $latestEducation->institution ? " à {$latestEducation->institution}" : '';

            $lines[] = trim("{$degree}{$field}{$institution}.");
        } else {
            $lines[] = 'Étudiant(e) motivé(e) à la recherche de nouvelles opportunités.';
        }

        // --- Ligne 2 : autres formations, si plusieurs ---
        if ($profile->educations->count() > 1) {
            $others = $profile->educations->skip(1)->pluck('degree')->filter()->implode(', ');
            if ($others) {
                $lines[] = "Parcours complété par : {$others}.";
            }
        }

        // --- Lignes expérience ---
        if ($profile->experiences->isNotEmpty()) {
            $latestExperience = $profile->experiences->first();
            $lines[] = "Expérience en tant que {$latestExperience->position} chez {$latestExperience->company}"
                .($latestExperience->is_current ? ' (poste actuel)' : '').'.';

            if ($profile->experiences->count() > 1) {
                $lines[] = "Totalise {$profile->experiences->count()} expérience(s) professionnelle(s) au total.";
            }
        } else {
            $lines[] = 'Ouvert(e) à une première expérience professionnelle ou un stage.';
        }

        // --- Compétences ---
        if ($profile->skills->isNotEmpty()) {
            $skillNames = $profile->skills->pluck('name')->take(6)->implode(', ');
            $lines[] = "Compétences clés : {$skillNames}.";
        }

        // --- Langues ---
        if ($profile->languages->isNotEmpty()) {
            $languageNames = $profile->languages->pluck('name')->implode(', ');
            $lines[] = "Langues parlées : {$languageNames}.";
        }

        // --- Certifications ---
        if ($profile->certifications->isNotEmpty()) {
            $lines[] = "Titulaire de {$profile->certifications->count()} certification(s) complémentaire(s).";
        }

        // --- Ligne de clôture ---
        $lines[] = 'Rigoureux(se), motivé(e) et désireux(se) de mettre ses compétences au service d\'une équipe.';

        // On garde entre 3 et 10 lignes.
        $lines = array_slice($lines, 0, 10);

        if (count($lines) < 3) {
            $lines[] = 'Profil sérieux, dynamique et adaptable, prêt(e) à relever de nouveaux défis.';
        }

        return implode(' ', $lines);
    }
}
