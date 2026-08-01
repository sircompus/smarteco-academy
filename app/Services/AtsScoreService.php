<?php

namespace App\Services;

use App\Models\CvProfile;

class AtsScoreService
{
    /**
     * Calcule un score /100 et une liste de conseils d'amélioration,
     * basé sur des règles simples de compatibilité ATS (pas d'IA,
     * juste des vérifications de complétude et de bonnes pratiques).
     *
     * @return array{score: int, max: int, checks: array<int, array{label: string, passed: bool, advice: string}>}
     */
    public function evaluate(CvProfile $profile): array
    {
        $checks = [];

        // --- Informations de contact ---
        $checks[] = $this->check(
            'Nom complet renseigné',
            filled($profile->full_name),
            'Ajoute ton nom complet — sans lui, les logiciels ATS rejettent souvent le CV.'
        );

        $checks[] = $this->check(
            'Adresse e-mail renseignée',
            filled($profile->email),
            'Ajoute une adresse e-mail professionnelle et facilement identifiable.'
        );

        $checks[] = $this->check(
            'Téléphone renseigné',
            filled($profile->phone),
            'Ajoute un numéro de téléphone joignable.'
        );

        $checks[] = $this->check(
            'Titre / accroche professionnelle',
            filled($profile->headline),
            'Ajoute une accroche claire (ex: "Étudiant en Gestion — Comptabilité et Finance").'
        );

        // --- Résumé ---
        $summaryLength = $profile->summary ? str_word_count(strip_tags($profile->summary)) : 0;

        $checks[] = $this->check(
            'Résumé professionnel présent et suffisant (30-150 mots)',
            $summaryLength >= 30 && $summaryLength <= 150,
            $summaryLength === 0
                ? 'Ajoute un résumé de 2-3 phrases présentant ton profil.'
                : ($summaryLength < 30
                    ? 'Ton résumé est trop court — développe un peu plus (30 mots minimum).'
                    : 'Ton résumé est trop long — les ATS préfèrent des résumés concis (150 mots max).')
        );

        // --- Formation ---
        $checks[] = $this->check(
            'Au moins une formation renseignée',
            $profile->educations->isNotEmpty(),
            'Ajoute au moins une formation (diplôme, établissement, dates).'
        );

        $checks[] = $this->check(
            'Formations avec dates complètes',
            $profile->educations->isEmpty() || $profile->educations->every(
                fn ($e) => $e->start_date && ($e->end_date || $e->is_current)
            ),
            'Complète les dates de début/fin de chaque formation — les ATS analysent la chronologie.'
        );

        // --- Expérience ---
        $checks[] = $this->check(
            'Au moins une expérience ou un stage renseigné',
            $profile->experiences->isNotEmpty(),
            'Ajoute au moins une expérience professionnelle ou un stage, même court.'
        );

        $checks[] = $this->check(
            'Descriptions d\'expérience détaillées (10 mots min. chacune)',
            $profile->experiences->isEmpty() || $profile->experiences->every(
                fn ($e) => $e->description && str_word_count(strip_tags($e->description)) >= 10
            ),
            'Détaille chaque expérience avec des tâches concrètes et des résultats mesurables.'
        );

        // --- Compétences ---
        $checks[] = $this->check(
            'Au moins 5 compétences renseignées',
            $profile->skills->count() >= 5,
            'Liste au moins 5 compétences — les ATS scannent les mots-clés de compétences en priorité.'
        );

        // --- Langues ---
        $checks[] = $this->check(
            'Au moins une langue renseignée',
            $profile->languages->isNotEmpty(),
            'Ajoute au moins une langue avec ton niveau.'
        );

        // --- Photo ---
        $checks[] = $this->check(
            'Pas de photo dans la version ATS',
            true, // toujours vrai : notre version ATS n'inclut jamais de photo
            ''
        );

        $passed = collect($checks)->where('passed', true)->count();
        $total = count($checks);
        $score = $total > 0 ? (int) round(($passed / $total) * 100) : 0;

        return [
            'score' => $score,
            'max' => 100,
            'checks' => $checks,
        ];
    }

    private function check(string $label, bool $passed, string $advice): array
    {
        return [
            'label' => $label,
            'passed' => $passed,
            'advice' => $passed ? null : $advice,
        ];
    }
}
